<?php

namespace Api\Controller\Business;

use Api\Entity\Host;
use Api\Entity\Lodging;
use Api\Exception\BusinessException;
use Api\Service\Business\LodgingObjectHandler;
use Api\Object\Business\CreateLodgingRequestObject;
use Api\Object\Business\PatchRequestObject;
use Api\Service\Technical\ResponseBuffer;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;

final class LodgingController extends AbstractController
{
    /**
     * Return a list of lodging
     */
    #[Route('/lodging', name: 'api_lodging_list', methods: ['GET'])]
    public function index(): JsonResponse
    {

        return $this->responseBuffer->buildResponse($this->handler->loadList());
    }

    /**
     * Return one lodging
     */
    #[Route('/lodging/{guid}', name: 'api_lodging')]
    public function lodging(string $guid): JsonResponse
    {
        $errorMessage = 'Lodging (' . $guid . ') not found';

        if (!ctype_alnum($guid))
            throw new BusinessException(404,  $errorMessage);

        $lodging = $this->handler->loadOne($guid);

        if ($lodging === null)
            throw new BusinessException(404,  $errorMessage);

        return $this->responseBuffer->buildResponse($lodging);
    }

    /**
     * "Auth Create lodging" endpoint
     */
    #[Route('/auth/lodging', name: 'api_create_lodging', methods: ['POST'])]
    public function create(): JsonResponse
    {

        try {

            $createLodgingRequestObject = $this->serializer->deserialize(
                $this->requestStack->getCurrentRequest()->getContent(),
                CreateLodgingRequestObject::class,
                'json'
            );

            // Get the Host associated with the current user
            $hostEntity = $this->entityManager->getRepository(Host::class)->findOneByUserId($this->getUser()->getId());
            if ($hostEntity === null)
                throw new BusinessException(500, 'Host not found');

            $createdLodging = $this->handler->createOne($createLodgingRequestObject, $hostEntity);

            $this->responseBuffer->addHeader('Location', '/lodging/' . $createdLodging->id);
            $this->responseBuffer->setStatusCode(201);
            return $this->responseBuffer->buildResponse($createdLodging);
        } catch (Exception $e) {
            $exceptionPath = explode('\\', $e::class);
            $exceptioName = count($exceptionPath) > 0 ? array_pop($exceptionPath) : null;
            if (in_array($exceptioName, ['NotEncodableValueException', 'MissingConstructorArgumentsException'])) {
                throw new BusinessException(400, $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Update one lodging using the PATCH method and return the full object
     */
    #[Route('/auth/lodging/{guid}/{property}', name: 'api_update_lodging', methods: ['PATCH'])]
    public function update(string $guid, string $property): JsonResponse
    {
        if (!ctype_alnum($guid))
            throw new BusinessException(404,  'Lodging (' . $guid . ') not found');

        // Get the Host associated with the current user
        $hostEntity = $this->entityManager->getRepository(Host::class)->findOneByUserId($this->getUser()->getId());
        if ($hostEntity === null)
            throw new BusinessException(500, 'Host not found');

        // Check if the lodging is associated with the current user
        $currentLodging = $this->entityManager->getRepository(Lodging::class)->findOneBy(['guid' => $guid]);
        if ($currentLodging === null)
            throw new BusinessException(404,  'Lodging (' . $guid . ') not found');

        if ($currentLodging->getHost()->getId() !== $hostEntity->getId())
            throw new BusinessException(403,  'Incorrect lodging host');

        try {

            $patchRequestObject = $this->serializer->deserialize(
                $this->requestStack->getCurrentRequest()->getContent(),
                PatchRequestObject::class,
                'json'
            );

            $patchedLodging = $this->handler->patchOne($guid, $property, $patchRequestObject);

            return $this->responseBuffer->buildResponse($patchedLodging);
        } catch (Exception $e) {
            $exceptionPath = explode('\\', $e::class);
            $exceptioName = count($exceptionPath) > 0 ? array_pop($exceptionPath) : null;
            if (in_array($exceptioName, ['NotEncodableValueException', 'MissingConstructorArgumentsException'])) {
                throw new BusinessException(400, $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    public function __construct(
        private readonly ResponseBuffer $responseBuffer,
        private readonly LodgingObjectHandler $handler,
        private readonly EntityManagerInterface $entityManager,
        protected readonly RequestStack $requestStack,
        private readonly SerializerInterface $serializer
    ) {}
}
