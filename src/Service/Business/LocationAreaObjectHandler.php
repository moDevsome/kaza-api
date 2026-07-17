<?php

namespace Api\Service\Business;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Api\Entity\LocationArea;
use Api\Exception\BusinessException;
use Api\Service\Business\ContentTranslationStore;
use Api\Interface\ObjectHandlerInterface;
use Api\Object\Business\CreateLocationAreaRequestObject;
use Api\Object\Business\LocationAreaObject;
use Api\Object\Business\PatchRequestObject;

final class LocationAreaObjectHandler implements ObjectHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentTranslationStore $contentTranslationStore,
    ) {}

    public function convertToLocationAreaObject(LocationArea $input): LocationAreaObject
    {
        // TODO:handle translation
        return new LocationAreaObject(
            $input->getId(),
            $this->contentTranslationStore->getValue('locationarea.name', $input->getId(), $input->getName()),
        );
    }

    public function loadList(array $criterias = []): array
    {
        return array_map(
            fn($locationAreaEntity) => $this->convertToLocationAreaObject($locationAreaEntity),
            $this->entityManager->getRepository(LocationArea::class)->findBy($criterias)
        );
    }

    public function loadOne(string $id): LocationAreaObject|null
    {

        $locationArea = $this->entityManager->getRepository(LocationArea::class)->findOneById($id);
        if (!$locationArea)
            return null;

        return $this->convertToLocationAreaObject($locationArea);
    }

    /**
     * Create one location area then return the object
     * @param CreateLocationAreaRequestObject $createRequest
     * @throws BusinessException
     * @return LocationAreaObject
     */
    public function createOne(CreateLocationAreaRequestObject $createRequest): LocationAreaObject
    {

        //TODO:check if the name already exist

        try {

            $newEntity = new LocationArea();
            $newEntity->setName($createRequest->name);
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();

            if ($newEntity === null)
                throw new BusinessException(500, 'Error occured while creating location area');

            return $this->convertToLocationAreaObject($newEntity);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Update a location area object
     *
     * @param string $id
     * @param CreateLocationAreaRequestObject $requestObject
     * @throws BusinessException
     * @return LocationAreaObject
     */
    public function updateOne(string $id, CreateLocationAreaRequestObject $requestObject): LocationAreaObject
    {

        try {

            $locationAreaEntity = $this->entityManager->getRepository(LocationArea::class)->findOneById($id);
            if ($locationAreaEntity === null)
                throw new BusinessException(404, 'LocationArea not found');

            $translateProperty = null;

            $locationAreaEntity->setName($requestObject->name);
            $this->entityManager->persist($locationAreaEntity);
            $this->entityManager->flush();

            /*
            if ($translateProperty !== null and $requestObject->autoTranslate === true) {
                //TODO:handle translation
            }
                */

            return $this->convertToLocationAreaObject($locationAreaEntity);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete a location area object
     *
     * @param string $id
     * @throws BusinessException
     * @return void
     */
    public function deleteOne(string $id): void
    {

        $locationAreaEntity = $this->entityManager->getRepository(LocationArea::class)->findOneById($id);
        if ($locationAreaEntity !== null) {
            $this->entityManager->remove($locationAreaEntity);
            $this->entityManager->flush();

            //TODO:delete translation
        }
    }

    public function patchOne(string $id, string $property, PatchRequestObject $requestObject): LocationAreaObject
    {
        return new LocationAreaObject('', '');
    }
}
