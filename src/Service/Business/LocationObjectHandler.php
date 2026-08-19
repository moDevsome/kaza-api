<?php

namespace Api\Service\Business;

use Exception;
use BadFunctionCallException;
use Doctrine\ORM\EntityManagerInterface;
use Api\Entity\Location;
use Api\Entity\LocationArea;
use Api\Exception\BusinessException;
use Api\Service\Business\ContentTranslationStore;
use Api\Interface\ObjectHandlerInterface;
use Api\Object\Business\CreateLocationRequestObject;
use Api\Object\Business\LocationObject;
use Api\Object\Business\PatchRequestObject;
use Api\Enum\Business\ContentTranslationType;
use Api\Enum\Business\ContentTranslationLocationProperty;
use Api\Object\Business\ContentTranslationRequestValueObject;

final class LocationObjectHandler implements ObjectHandlerInterface
{
    public function __construct(
        private readonly LocationAreaObjectHandler $locatioAreaObjectHandler,
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentTranslationStore $contentTranslationStore,
        private readonly LookupService $lookupService
    ) {}

    /**
     * Mapping function wich convert the DTO found in the database to the expected web output object format
     * @param Location $input
     * @return LocationObject
     */
    private function convertToLocationObject(Location $input): LocationObject
    {
        return new LocationObject(
            $input->getId(),
            $this->contentTranslationStore->getValue('location.name', $input->getId(), $input->getName()),
            $this->locatioAreaObjectHandler->convertToLocationAreaObject($input->getArea())
        );
    }

    public function loadList(array $criterias = array(), array $orderBy = array(), int $limitCount = 40, int $limitOffset = 0): array
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
     * @param bool $applyTranslation
     * @throws BusinessException
     * @return LocationObject
     */
    public function createOne(CreateLocationRequestObject $createRequest, bool $applyTranslation): LocationObject
    {

        try {

            $locationAreaEntity = $this->entityManager->getRepository(LocationArea::class)->findOneById($createRequest->locationAreaId);
            if ($locationAreaEntity === null)
                throw new BusinessException(400, 'Location area not found (' . $createRequest->locationAreaId . ')');

            $alreadyExistCount = count($this->lookupService->find('LOCATION', $createRequest->name));
            if ($alreadyExistCount > 0)
                throw new BusinessException(400, $alreadyExistCount . ' Location already exist with this name');

            $newEntity = new Location();
            $newEntity->setName($createRequest->name);
            $newEntity->setArea($locationAreaEntity);
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();

            if ($newEntity === null)
                throw new BusinessException(500, 'Error occured while creating location');

            if ($applyTranslation === true) {

                $this->contentTranslationStore->setValues(
                    $newEntity->getId(),
                    ContentTranslationType::Location,
                    ContentTranslationLocationProperty::Name,
                    [
                        new ContentTranslationRequestValueObject($this->contentTranslationStore->getCurrentTag(), $createRequest->name)
                    ]
                );
            }

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
     * @param bool $applyTranslation
     * @throws BusinessException
     * @return LocationObject
     */
    public function updateOne(string $id, CreateLocationRequestObject $requestObject, bool $applyTranslation): LocationObject
    {

        try {

            $alreadyExistCount = count($this->lookupService->find('LOCATION', $requestObject->name));
            if ($alreadyExistCount > 0)
                throw new BusinessException(400, $alreadyExistCount . ' Location already exist with this name');

            $locationAreaEntity = $this->entityManager->getRepository(LocationArea::class)->findOneById($requestObject->locationAreaId);
            if ($locationAreaEntity === null)
                throw new BusinessException(400, 'Location area not found (' . $requestObject->locationAreaId . ')');

            $locationEntity = $this->entityManager->getRepository(Location::class)->findOneById($id);
            if ($locationEntity === null)
                throw new BusinessException(404, 'Location not found');

            $locationEntity->setName($requestObject->name);
            $locationEntity->setArea($locationAreaEntity);
            $this->entityManager->persist($locationEntity);
            $this->entityManager->flush();

            if ($applyTranslation === true) {

                $this->contentTranslationStore->setValues(
                    $locationEntity->getId(),
                    ContentTranslationType::Location,
                    ContentTranslationLocationProperty::Name,
                    [
                        new ContentTranslationRequestValueObject($this->contentTranslationStore->getCurrentTag(), $requestObject->name)
                    ]
                );
            }

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

            $this->contentTranslationStore->deleteValues(
                $locationEntity->getId(),
                ContentTranslationType::Location,
                ContentTranslationLocationProperty::Name
            );
        }
    }

    public function patchOne(string $id, string $property, PatchRequestObject $requestObject, bool $applyTranslation): LocationObject
    {
        throw new BadFunctionCallException('Patch Location not implemented');
    }
}
