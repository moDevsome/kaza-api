<?php

namespace Api\Service\Business;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Api\Entity\Equipment;
use Api\Exception\BusinessException;
use Api\Service\Business\ContentTranslationStore;
use Api\Interface\ObjectHandlerInterface;
use Api\Object\Business\CreateEquipmentRequestObject;
use Api\Object\Business\EquipmentObject;
use Api\Object\Business\PatchRequestObject;

final class EquipmentObjectHandler implements ObjectHandlerInterface
{
    private function convertToEquipmentObject(Equipment $input): EquipmentObject
    {
        return new EquipmentObject(
            $input->getId(),
            $this->contentTranslationStore->getValue('equipment.name', $input->getId(), $input->getName()),
        );
    }

    public function loadList(array $criterias = []): array
    {
        return array_map(
            fn($equipmentEntity) => $this->convertToEquipmentObject($equipmentEntity),
            $this->entityManager->getRepository(Equipment::class)->findBy($criterias)
        );
    }

    public function loadOne(string $id): EquipmentObject|null
    {

        $equipment = $this->entityManager->getRepository(Equipment::class)->findOneById($id);
        if (!$equipment)
            return null;

        return $this->convertToEquipmentObject($equipment);
    }

    /**
     * Create one equipment then return the object
     * @param CreateEquipmentRequestObject $createRequest
     * @throws BusinessException
     * @return EquipmentObject
     */
    public function createOne(CreateEquipmentRequestObject $createRequest): EquipmentObject
    {

        //TODO:check if the name already exist

        try {

            $newEntity = new Equipment();
            $newEntity->setName($createRequest->name);
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();

            if ($newEntity === null)
                throw new BusinessException(500, 'Error occured while creating equipment');

            return $this->convertToEquipmentObject($newEntity);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Update a equipment object
     *
     * @param string $id
     * @param CreateEquipmentRequestObject $requestObject
     * @throws BusinessException
     * @return EquipmentObject
     */
    public function updateOne(string $id, CreateEquipmentRequestObject $requestObject): EquipmentObject
    {

        try {

            $equipmentEntity = $this->entityManager->getRepository(Equipment::class)->findOneById($id);
            if ($equipmentEntity === null)
                throw new BusinessException(404, 'Equipment not found');

            $translateProperty = null;

            $equipmentEntity->setName($requestObject->name);
            $this->entityManager->persist($equipmentEntity);
            $this->entityManager->flush();

            if ($translateProperty !== null and $requestObject->autoTranslate === true) {
                //TODO:handle translation
            }

            return $this->convertToEquipmentObject($equipmentEntity);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete a equipment object
     *
     * @param string $id
     * @throws BusinessException
     * @return void
     */
    public function deleteOne(string $id): void
    {

        $equipmentEntity = $this->entityManager->getRepository(Equipment::class)->findOneById($id);
        if ($equipmentEntity !== null) {
            $this->entityManager->remove($equipmentEntity);
            $this->entityManager->flush();

            //TODO:delete translation
        }
    }

    public function patchOne(string $id, string $property, PatchRequestObject $requestObject): EquipmentObject
    {
        return new EquipmentObject('', '');
    }

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentTranslationStore $contentTranslationStore,
    ) {}
}
