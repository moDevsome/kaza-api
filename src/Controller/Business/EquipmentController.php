<?php

namespace Api\Controller\Business;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Exception;
use Api\Entity\Equipment;
use Api\Exception\BusinessException;
use Api\Object\Business\CreateEquipmentRequestObject;
use Api\Service\Technical\ResponseBuffer;
use Api\Service\Business\EquipmentObjectHandler;
use Doctrine\ORM\EntityManagerInterface;

final class EquipmentController extends AbstractController
{

    public function __construct(
        private readonly ResponseBuffer $responseBuffer,
        private readonly EquipmentObjectHandler $handler,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        protected readonly RequestStack $requestStack,
    ) {}

    /**
     * Return a list of equipment
     */
    #[Route('/equipment', name: 'api_equipment_list', methods: ['GET'])]
    public function index(): JsonResponse
    {

        return $this->responseBuffer->buildResponse($this->handler->loadList());
    }

    /**
     * Return one equipment
     * @param string $id
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/equipment/{id}', name: 'api_equipment')]
    public function equipment(string $id): JsonResponse
    {
        $errorMessage = 'Equipment (' . $id . ') not found';

        $equipment = $this->handler->loadOne($id);

        if ($equipment === null)
            throw new BusinessException(404,  $errorMessage);

        return $this->responseBuffer->buildResponse($equipment);
    }

    /**
     * "Create Equipment" endpoint for authenticated user
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/auth/equipment', name: 'api_create_equipment', methods: ['POST'])]
    public function create(): JsonResponse
    {

        $createEquipmentRequestObject = $this->serializer->deserialize(
            $this->requestStack->getCurrentRequest()->getContent(),
            CreateEquipmentRequestObject::class,
            'json'
        );

        $createdEquipment = $this->handler->createOne($createEquipmentRequestObject);

        $this->responseBuffer->addHeader('Location', '/equipment/' . $createdEquipment->id);
        $this->responseBuffer->setStatusCode(201);
        return $this->responseBuffer->buildResponse($createdEquipment);
    }

    /**
     * "Update Equipment" endpoint for authenticated user
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/auth/equipment/{id}', name: 'api_update_equipment', methods: ['PUT'])]
    public function update(string $id): JsonResponse
    {

        $createEquipmentRequestObject = $this->serializer->deserialize(
            $this->requestStack->getCurrentRequest()->getContent(),
            CreateEquipmentRequestObject::class,
            'json'
        );

        // Check if the equipment is used by a lodging which not belong to the current user
        try {
            $count = $this->entityManager->getRepository(Equipment::class)->countEquipmentByUserId($id, $this->getUser()->getId(), true);
        } catch (Exception $e) {
            throw new BusinessException(500, $e->getMessage());
        }

        if ($count !== 0)
            throw new BusinessException(400, 'This equipment could not be updated by the current user because it is associated with lodging which not belong to the current user');

        $updatedEquipment = $this->handler->updateOne($id, $createEquipmentRequestObject);
        return $this->responseBuffer->buildResponse($updatedEquipment);
    }

    /**
     * "Delete Equipment" endpoint for authenticated user
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/auth/equipment/{id}', name: 'api_delete_equipment', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {

        // Check if the equipment is used by a lodging which not belong to the current user
        try {
            $count = $this->entityManager->getRepository(Equipment::class)->countEquipmentByUserId($id, $this->getUser()->getId(), true);
        } catch (Exception $e) {
            throw new BusinessException(500, $e->getMessage());
        }

        if ($count !== 0)
            throw new BusinessException(400, 'This equipment could not be deleted by the current user because it is associated with lodging which not belong to the current user');

        $this->handler->deleteOne($id);
        return $this->responseBuffer->buildResponse(['OK']);
    }
}
