<?php

namespace Api\Service\Business;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Api\Entity\LocationArea;
use Api\Enum\Business\ContentTranslationLocationAreaProperty;
use Api\Enum\Business\ContentTranslationType;
use Api\Exception\BusinessException;
use Api\Service\Business\ContentTranslationStore;
use Api\Interface\ObjectHandlerInterface;
use Api\Object\Business\ContentTranslationRequestValueObject;
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
        return new LocationAreaObject(
            $input->getId(),
            $this->contentTranslationStore->getValue('locationarea.name', $input->getId(), $input->getName()),
        );
    }

    public function loadList(array $criterias = [], int $limitCount = 40, int $limitOffset = 0): array
    {
        return array_map(
            fn($locationAreaEntity) => $this->convertToLocationAreaObject($locationAreaEntity),
            $this->entityManager->getRepository(LocationArea::class)->findBy($criterias, ['name' => 'asc'])
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
     * @param bool $applyTranslation
     * @throws BusinessException
     * @return LocationAreaObject
     */
    public function createOne(CreateLocationAreaRequestObject $createRequest, bool $applyTranslation): LocationAreaObject
    {

        try {

            $newEntity = new LocationArea();
            $newEntity->setName($createRequest->name);
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();

            if ($newEntity === null)
                throw new BusinessException(500, 'Error occured while creating location area');

            if ($applyTranslation === true) {

                $this->contentTranslationStore->setValues(
                    $newEntity->getId(),
                    ContentTranslationType::LocationArea,
                    ContentTranslationLocationAreaProperty::Name,
                    [
                        new ContentTranslationRequestValueObject($this->contentTranslationStore->getCurrentTag(), $createRequest->name)
                    ]
                );
            }

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
     * @param bool $applyTranslation
     * @throws BusinessException
     * @return LocationAreaObject
     */
    public function updateOne(string $id, CreateLocationAreaRequestObject $requestObject, bool $applyTranslation): LocationAreaObject
    {

        try {

            $locationAreaEntity = $this->entityManager->getRepository(LocationArea::class)->findOneById($id);
            if ($locationAreaEntity === null)
                throw new BusinessException(404, 'LocationArea not found');

            $translateProperty = null;

            $locationAreaEntity->setName($requestObject->name);
            $this->entityManager->persist($locationAreaEntity);
            $this->entityManager->flush();

            if ($translateProperty !== null and $applyTranslation === true) {
                //TODO:handle translation
            }

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

    public function patchOne(string $id, string $property, PatchRequestObject $requestObject, bool $applyTranslation): LocationAreaObject
    {
        return new LocationAreaObject('', '');
    }
}
