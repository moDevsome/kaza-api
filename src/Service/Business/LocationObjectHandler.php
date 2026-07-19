<?php

namespace Api\Service\Business;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Api\Entity\Location;
use Api\Entity\LocationArea;
use Api\Exception\BusinessException;
use Api\Service\Business\ContentTranslationStore;
use Api\Interface\ObjectHandlerInterface;
use Api\Object\Business\CreateLocationRequestObject;
use Api\Object\Business\LocationAreaObject;
use Api\Object\Business\LocationObject;
use Api\Object\Business\PatchRequestObject;

final class LocationObjectHandler implements ObjectHandlerInterface
{
    public function __construct(
        private readonly LocationAreaObjectHandler $locatioAreaObjectHandler,
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentTranslationStore $contentTranslationStore,
    ) {}

    private function convertToLocationObject(Location $input): LocationObject
    {
        // TODO:handle translation
        return new LocationObject(
            $input->getId(),
            $this->contentTranslationStore->getValue('location.name', $input->getId(), $input->getName()),
            $this->locatioAreaObjectHandler->convertToLocationAreaObject($input->getArea())
        );
    }

    public function loadList(array $criterias = [], int $limitCount = 40, int $limitOffset = 0): array
    {
        return array_map(
            fn($locationEntity) => $this->convertToLocationObject($locationEntity),
            $this->entityManager->getRepository(Location::class)->findBy($criterias)
        );
    }

    public function loadOne(string $id): LocationObject|null
    {

        $location = $this->entityManager->getRepository(Location::class)->findOneById($id);
        if (!$location)
            return null;

        return $this->convertToLocationObject($location);
    }

    /**
     * Create one location then return the object
     * @param CreateLocationRequestObject $createRequest
     * @throws BusinessException
     * @return LocationObject
     */
    public function createOne(CreateLocationRequestObject $createRequest): LocationObject
    {

        //TODO:check if the name already exist

        $locationAreaEntity = $this->entityManager->getRepository(LocationArea::class)->findOneById($createRequest->locationAreaId);
        if ($locationAreaEntity === null)
            throw new BusinessException(400, 'Location area not found (' . $createRequest->locationAreaId . ')');

        try {

            $newEntity = new Location();
            $newEntity->setName($createRequest->name);
            $newEntity->setArea($locationAreaEntity);
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();

            if ($newEntity === null)
                throw new BusinessException(500, 'Error occured while creating location');

            return $this->convertToLocationObject($newEntity);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Update a location object
     *
     * @param string $id
     * @param CreateLocationRequestObject $requestObject
     * @throws BusinessException
     * @return LocationObject
     */
    public function updateOne(string $id, CreateLocationRequestObject $requestObject): LocationObject
    {

        try {

            $locationAreaEntity = $this->entityManager->getRepository(LocationArea::class)->findOneById($requestObject->locationAreaId);
            if ($locationAreaEntity === null)
                throw new BusinessException(400, 'Location area not found (' . $requestObject->locationAreaId . ')');

            $locationEntity = $this->entityManager->getRepository(Location::class)->findOneById($id);
            if ($locationEntity === null)
                throw new BusinessException(404, 'Location not found');

            $translateProperty = null;

            $locationEntity->setName($requestObject->name);
            $locationEntity->setArea($locationAreaEntity);
            $this->entityManager->persist($locationEntity);
            $this->entityManager->flush();

            /*
            if ($translateProperty !== null and $requestObject->autoTranslate === true) {
                //TODO:handle translation
            }
            */

            return $this->convertToLocationObject($locationEntity);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete a location object
     *
     * @param string $id
     * @throws BusinessException
     * @return void
     */
    public function deleteOne(string $id): void
    {

        $locationEntity = $this->entityManager->getRepository(Location::class)->findOneById($id);
        if ($locationEntity !== null) {
            $this->entityManager->remove($locationEntity);
            $this->entityManager->flush();

            //TODO:delete translation
        }
    }

    public function patchOne(string $id, string $property, PatchRequestObject $requestObject): LocationObject
    {
        return new LocationObject('', '', new LocationAreaObject('', ''));
    }
}
