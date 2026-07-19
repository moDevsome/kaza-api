<?php

namespace Api\Controller\Business;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Api\Entity\Location;
use Api\Exception\BusinessException;
use Api\Object\Business\CreateLocationRequestObject;
use Api\Service\Technical\ResponseBuffer;
use Api\Service\Business\LocationObjectHandler;
use Doctrine\ORM\EntityManagerInterface;

final class LocationController extends AbstractController
{
    private array $queryParams;

    public function __construct(
        private readonly ResponseBuffer $responseBuffer,
        private readonly LocationObjectHandler $handler,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        protected readonly RequestStack $requestStack,
    ) {
        $this->queryParams = $this->requestStack->getCurrentRequest()->query->all();
    }

    /**
     * Return a list of location
     */
    #[Route('/location', name: 'api_location_list', methods: ['GET'])]
    public function index(): JsonResponse
    {

        return $this->responseBuffer->buildResponse($this->handler->loadList());
    }

    /**
     * Return one location
     * @param string $id
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/location/{id}', name: 'api_location', methods: ['GET'])]
    public function location(string $id): JsonResponse
    {
        $errorMessage = 'Location (' . $id . ') not found';

        $location = $this->handler->loadOne($id);

        if ($location === null)
            throw new BusinessException(404,  $errorMessage);

        return $this->responseBuffer->buildResponse($location);
    }

    /**
     * "Create Location" endpoint for authenticated user
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/auth/location', name: 'api_create_location', methods: ['POST'])]
    public function create(): JsonResponse
    {

        $createLocationRequestObject = $this->serializer->deserialize(
            $this->requestStack->getCurrentRequest()->getContent(),
            CreateLocationRequestObject::class,
            'json'
        );

        $createdLocation = $this->handler->createOne(
            $createLocationRequestObject,
            isset($this->queryParams['applyTranslation']) ? $this->queryParams['applyTranslation'] === "true" : true
        );

        $this->responseBuffer->addHeader('Location', '/location/' . $createdLocation->id);
        $this->responseBuffer->setStatusCode(201);
        return $this->responseBuffer->buildResponse($createdLocation);
    }

    /**
     * "Update Location" endpoint for authenticated user
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/auth/location/{id}', name: 'api_update_location', methods: ['PUT'])]
    public function update(string $id): JsonResponse
    {

        $createLocationRequestObject = $this->serializer->deserialize(
            $this->requestStack->getCurrentRequest()->getContent(),
            CreateLocationRequestObject::class,
            'json'
        );

        // Check if the location is used by a lodging which not belong to the current user
        try {
            $count = $this->entityManager->getRepository(Location::class)->countLocationByUserId($id, $this->getUser()->getId(), true);
        } catch (Exception $e) {
            throw new BusinessException(500, $e->getMessage());
        }

        if ($count !== 0)
            throw new BusinessException(400, 'This location could not be updated by the current user because it is associated with lodging which not belong to the current user');

        $updatedLocation = $this->handler->updateOne(
            $id,
            $createLocationRequestObject,
            isset($this->queryParams['applyTranslation']) ? $this->queryParams['applyTranslation'] === "true" : true
        );
        return $this->responseBuffer->buildResponse($updatedLocation);
    }

    /**
     * "Delete Location" endpoint for authenticated user
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/auth/location/{id}', name: 'api_delete_location', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {

        // Check if the location is used by a lodging which not belong to the current user
        try {
            $count = $this->entityManager->getRepository(Location::class)->countLocationByUserId($id, $this->getUser()->getId(), true);
        } catch (Exception $e) {
            throw new BusinessException(500, $e->getMessage());
        }

        if ($count !== 0)
            throw new BusinessException(400, 'This location could not be deleted by the current user because it is associated with lodging which not belong to the current user');

        $this->handler->deleteOne($id);
        return $this->responseBuffer->buildResponse(['OK']);
    }
}
