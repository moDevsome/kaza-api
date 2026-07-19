<?php

namespace Api\Controller\Business;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Api\Entity\Host;
use Api\Entity\Lodging;
use Api\Exception\BusinessException;
use Api\Object\Business\AddElementRequestObject;
use Api\Service\Business\LodgingObjectHandler;
use Api\Object\Business\CreateLodgingRequestObject;
use Api\Object\Business\PatchRequestObject;
use Api\Object\Business\RemoveElementRequestObject;
use Api\Service\Technical\ResponseBuffer;

final class LodgingController extends AbstractController
{
    private array $queryParams;

    public function __construct(
        private readonly ResponseBuffer $responseBuffer,
        private readonly LodgingObjectHandler $handler,
        private readonly EntityManagerInterface $entityManager,
        protected readonly RequestStack $requestStack,
        private readonly SerializerInterface $serializer
    ) {
        $this->queryParams = $this->requestStack->getCurrentRequest()->query->all();
    }

    /**
     * Check if the current user is the updated lodging owner
     * @param Lodging $updatedLodging
     * @throws BusinessException
     * @return void
     */
    private function checkOwner(Lodging $updatedLodging): void
    {
        // Get the Host associated with the current user
        $hostEntity = $this->entityManager->getRepository(Host::class)->findOneByUserId($this->getUser()->getId());
        if ($hostEntity === null)
            throw new BusinessException(500, 'Host not found');

        // Check if the lodging is associated with the current user
        if ($updatedLodging->getHost()->getId() !== $hostEntity->getId())
            throw new BusinessException(403,  'Incorrect lodging host');
    }

    /**
     * Return a list of lodging
     */
    #[Route('/lodging', name: 'api_lodging_list', methods: ['GET'])]
    public function index(): JsonResponse
    {

        $criterias = array_filter($this->queryParams, fn($queryParamKey) => in_array($queryParamKey, ['hostId', 'title']), 2);
        $limitCount = $this->queryParams['limitCount'] ?? 40;
        $limitOffset = $this->queryParams['limitOffset'] ?? 0;

        return $this->responseBuffer->buildResponse($this->handler->loadList($criterias, $limitCount, $limitOffset));
    }

    /**
     * Return one lodging
     * @param string $id
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/lodging/{id}', name: 'api_lodging')]
    public function lodging(string $id): JsonResponse
    {
        $errorMessage = 'Lodging (' . $id . ') not found';

        $lodging = $this->handler->loadOne($id);

        if ($lodging === null)
            throw new BusinessException(404,  $errorMessage);

        return $this->responseBuffer->buildResponse($lodging);
    }

    /**
     * "Create lodging" endpoint for authenticated user
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/auth/lodging', name: 'api_create_lodging', methods: ['POST'])]
    public function create(): JsonResponse
    {

        $createLodgingRequestObject = $this->serializer->deserialize(
            $this->requestStack->getCurrentRequest()->getContent(),
            CreateLodgingRequestObject::class,
            'json'
        );

        // Get the Host associated with the current user
        $hostEntity = $this->entityManager->getRepository(Host::class)->findOneByUserId($this->getUser()->getId());
        if ($hostEntity === null)
            throw new BusinessException(500, 'Host not found');

        $createdLodging = $this->handler->createOne(
            $createLodgingRequestObject,
            $hostEntity,
            isset($this->queryParams['applyTranslation']) ? $this->queryParams['applyTranslation'] === "true" : true
        );

        $this->responseBuffer->addHeader('Location', '/lodging/' . $createdLodging->id);
        $this->responseBuffer->setStatusCode(201);
        return $this->responseBuffer->buildResponse($createdLodging);
    }

    /**
     * Add a Picture, a Tag or Equipement to a Lodging
     * Fallback to "removeElement" method if the route does not match
     * @param string $id - Id of the lodging
     * @param string $element - "picture", "tag" or "equipment"
     * @throws BusinessException
     * @return JsonResponse
     */
    #[
        Route(
            '/auth/lodging/{id}/add-{element}',
            name: 'api_lodging_add_element',
            methods: ['POST'],
            condition: 'params["element"] in ["picture", "tag", "equipment"]',
            priority: 8
        )
    ]
    public function addElement(string $id, string $element): JsonResponse
    {
        $currentLodging = $this->entityManager->getRepository(Lodging::class)->findOneBy(['id' => $id]);
        if ($currentLodging === null)
            throw new BusinessException(404,  'Lodging (' . $id . ') not found');

        $this->checkOwner($currentLodging);

        $addElementRequestObject = $this->serializer->deserialize(
            $this->requestStack->getCurrentRequest()->getContent(),
            AddElementRequestObject::class,
            'json'
        );

        $updatedLodging = $this->handler->addElement($id, $element, $addElementRequestObject);

        return $this->responseBuffer->buildResponse($updatedLodging);
    }

    /**
     * Remove a Picture, a Tag or Equipement from a Lodging
     * Fallback to "patch" method if the route does not match
     * @param string $id - Id of the lodging
     * @param string $element - "picture", "tag" or "equipment"
     * @throws BusinessException
     * @return JsonResponse
     */
    #[
        Route(
            '/auth/lodging/{id}/remove-{element}',
            name: 'api_lodging_remove_element',
            methods: ['DELETE'],
            condition: 'params["element"] in ["picture", "tag", "equipment"]',
            priority: 9
        )
    ]
    public function removeElement(string $id, string $element): JsonResponse
    {

        $currentLodging = $this->entityManager->getRepository(Lodging::class)->findOneById($id);
        if ($currentLodging === null)
            throw new BusinessException(404,  'Lodging (' . $id . ') not found');

        $this->checkOwner($currentLodging);

        $removeElementRequestObject = $this->serializer->deserialize(
            $this->requestStack->getCurrentRequest()->getContent(),
            RemoveElementRequestObject::class,
            'json'
        );

        $updatedLodging = $this->handler->removeElement($id, $element, $removeElementRequestObject);

        return $this->responseBuffer->buildResponse($updatedLodging);
    }

    /**
     * Update one lodging using the PATCH method and return the full object
     * @param string $id
     * @param string $property - The property name
     * @throws BusinessException
     * @return JsonResponse
     */
    #[
        Route(
            '/auth/lodging/{id}/{property}',
            name: 'api_patch_lodging',
            methods: ['PATCH'],
            priority: 10
        )
    ]
    public function patch(string $id, string $property): JsonResponse
    {

        $currentLodging = $this->entityManager->getRepository(Lodging::class)->findOneById($id);
        if ($currentLodging === null)
            throw new BusinessException(404,  'Lodging (' . $id . ') not found');

        $this->checkOwner($currentLodging);

        $patchRequestObject = $this->serializer->deserialize(
            $this->requestStack->getCurrentRequest()->getContent(),
            PatchRequestObject::class,
            'json'
        );

        $applyTranslation = isset($this->queryParams['applyTranslation']) ? $this->queryParams['applyTranslation'] === "true" : true;
        $patchedLodging = $this->handler->patchOne($id, $property, $patchRequestObject, $applyTranslation);

        if (!in_array($property, ['title', 'description']) and $applyTranslation === true) {
            $this->responseBuffer->addWarning('applyTranslation has effect only for "title" and "description" properties');
        }

        return $this->responseBuffer->buildResponse($patchedLodging);
    }
}
