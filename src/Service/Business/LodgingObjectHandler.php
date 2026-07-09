<?php

namespace Api\Service\Business;

use Api\Entity\Equipment;
use Api\Entity\Host;
use Api\Entity\Location;
use Api\Entity\Lodging;
use Api\Entity\Picture;
use Api\Entity\Tag;
use Api\Exception\BusinessException;
use Api\Service\Business\ContentTranslationStore;
use Api\Interface\ObjectHandlerInterface;
use Api\Object\Business\CreateLodgingRequestObject;
use Api\Object\Business\HostObject;
use Api\Object\Business\LodgingObject;
use Api\Object\Business\PatchRequestObject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Exception;

/**
 * This class is made to load one or more LoadgingObject from the database
 */
final class LodgingObjectHandler implements ObjectHandlerInterface
{
    private array $allowedPictureFileMimeTypes = [
        'image/bmp',
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    private function getPictureFileMimeType(string $picturePath): ?string
    {
        try {
            $fileContent = file_get_contents($picturePath);

            $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
            return $fileInfo->buffer($fileContent);
        } catch (Exception $e) {
            return null;
        }
    }

    private function convertToLodgingObject(Lodging $input): LodgingObject
    {

        $hostEntity = $input->getHost();
        $locationEntity = $input->getLocation();

        return new LodgingObject(
            $input->getGuid(),
            $this->contentTranslationStore->getValue('lodging.title', $input->getId(), $input->getTitle()),
            $input->getCover(),
            array_values(array_map(fn($pictureEntity) => $pictureEntity->getPath(), $input->getPictures()->toArray())),
            $this->contentTranslationStore->getValue('lodging.description', $input->getId(), $input->getDescription()),
            new HostObject($hostEntity->getFirstname() . ' ' . $hostEntity->getLastname(), $hostEntity->getPicture()),
            $input->getRating(),
            implode(' - ', [
                $this->contentTranslationStore->getValue('location-area.name', $locationEntity->getArea()->getId(), $locationEntity->getArea()->getName()),
                $this->contentTranslationStore->getValue('location.name', $locationEntity->getId(), $locationEntity->getName())
            ]),
            array_values(array_map(fn($equipmentEntity) => $this->contentTranslationStore->getValue('equipment.name', $equipmentEntity->getId(), $equipmentEntity->getName()), $input->getEquipments()->toArray())),
            array_values(array_map(fn($tagEntity) => $this->contentTranslationStore->getValue('tag.name', $tagEntity->getId(), $tagEntity->getName()), $input->getTags()->toArray()))
        );
    }

    public function loadList(array $criterias = []): array
    {
        return array_map(
            fn($lodgingEntity) => $this->convertToLodgingObject($lodgingEntity),
            $this->entityManager->getRepository(Lodging::class)->findBy($criterias)
        );
    }

    public function loadOne(string|int $guid): LodgingObject|null
    {

        $lodging = $this->entityManager->getRepository(Lodging::class)->findOneBy(['guid' => $guid]);
        if (!$lodging)
            return null;

        return $this->convertToLodgingObject($lodging);
    }

    /**
     * Create one lodging then return the object
     * @param CreateLodgingRequestObject $createRequest
     * @return LodgingObject
     */
    public function createOne(CreateLodgingRequestObject $createRequest, Host $host): LodgingObject
    {

        try {

            // Get the Location entity associated with the given location id if not null
            if ($createRequest->locationId !== null) {
                $locationEntity = $this->entityManager->getRepository(Location::class)->findOneBy(['id' => $createRequest->locationId]);
                if ($locationEntity === null)
                    throw new BusinessException(400, 'The given locationId (' . $createRequest->locationId . ') is not associated with any location');
            }

            // Check the cover picture
            $coverPicture = '';
            if ($createRequest->cover !== null) {
                if (in_array($this->getPictureFileMimeType($createRequest->cover), $this->allowedPictureFileMimeTypes)) {
                    $coverPicture = $createRequest->cover;
                } else {
                    throw new BusinessException(400, 'The type of the file "' . $createRequest->cover . '" is not allowed. Allowed types: ' . implode(', ', $this->allowedPictureFileMimeTypes));
                }
            }

            // Check each given picture
            $pictureEntities = array();
            if (is_array($createRequest->pictures) and count($createRequest->pictures) > 0) {

                foreach ($createRequest->pictures as $picturePath) {
                    if (in_array($this->getPictureFileMimeType($picturePath), $this->allowedPictureFileMimeTypes)) {
                        $pictureEntity = new Picture();
                        $pictureEntity->setPath($picturePath);
                        $pictureEntities[] = $pictureEntity;
                    } else {
                        throw new BusinessException(400, 'The type of the file "' . $picturePath . '" is not allowed. Allowed types: ' . implode(', ', $this->allowedPictureFileMimeTypes));
                    }
                }
            }

            $newEntity = new Lodging();
            $newEntity->setTitle($createRequest->title);
            $newEntity->setCover($coverPicture);
            $newEntity->setDescription($createRequest->description);
            $newEntity->setHost($host);

            if (isset($locationEntity))
                $newEntity->setLocation($locationEntity);

            $newEntity->setGuid(Uuid::v7());

            // Add each given tag
            if (is_array($createRequest->tagIds) and count($createRequest->tagIds) > 0) {
                $tagEntities = $this->entityManager->getRepository(Tag::class)->findByIds($createRequest->tagIds);
                foreach ($createRequest->tagIds as $requestTagId) {

                    $tagEntity = array_find($tagEntities, fn($tagEntity) => $tagEntity->getId() === $requestTagId);
                    if ($tagEntity === null)
                        throw new BusinessException(400, 'Unknown tag (' . $requestTagId . ')');

                    $newEntity->addTag($tagEntity);
                }
            }

            // Add each given equipment
            if (is_array($createRequest->equipmentIds) and count($createRequest->equipmentIds) > 0) {
                $equipmentEntities = $this->entityManager->getRepository(Equipment::class)->findByIds($createRequest->equipmentIds);
                foreach ($createRequest->equipmentIds as $requestEquipmentId) {

                    $equipmentEntity = array_find($equipmentEntities, fn($equipmentEntity) => $equipmentEntity->getId() === $requestEquipmentId);
                    if ($equipmentEntity === null)
                        throw new BusinessException(400, 'Unknown equipment (' . $requestEquipmentId . ')');

                    $newEntity->addEquipment($equipmentEntity);
                }
            }

            $this->entityManager->persist($newEntity);

            // Insert each given picture
            foreach ($pictureEntities as $pictureEntity) {
                $pictureEntity->setLodging($newEntity);
                $newEntity->addPicture($pictureEntity);
            }

            $this->entityManager->flush();

            if ($newEntity === null)
                throw new BusinessException(500, 'Error occured while creating lodging');

            return $this->convertToLodgingObject($newEntity);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function patchOne(string $guid, string $property, PatchRequestObject $requestObject): LodgingObject
    {

        try {

            $lodgingEntity = $this->entityManager->getRepository(Lodging::class)->findOneBy(['guid' => $guid]);
            if ($lodgingEntity === null)
                throw new BusinessException(404, 'Lodging not found');

            switch ($property) {

                case 'title':
                    $lodgingEntity->setTitle($requestObject->value);
                    break;

                case 'description':
                    $lodgingEntity->setDescription($requestObject->value);
                    break;

                case 'cover':
                    if (!in_array($this->getPictureFileMimeType($requestObject->value), $this->allowedPictureFileMimeTypes))
                        throw new BusinessException(400, 'The type of the file "' . $requestObject->value . '" is not allowed. Allowed types: ' . implode(', ', $this->allowedPictureFileMimeTypes));

                    $lodgingEntity->setCover($requestObject->value);
                    break;

                case 'pictures':
                    if (!is_array($requestObject->value))
                        throw new BusinessException(400, 'The given value must be an array of picture path (string)');

                    // Remove all current picture entities
                    foreach ($lodgingEntity->getPictures() as $pictureEntity) {
                        $lodgingEntity->removePicture($pictureEntity);
                    }

                    foreach ($requestObject->value as $picturePath) {
                        if (!in_array($this->getPictureFileMimeType($picturePath), $this->allowedPictureFileMimeTypes))
                            throw new BusinessException(400, 'The type of the file "' . $picturePath . '" is not allowed. Allowed types: ' . implode(', ', $this->allowedPictureFileMimeTypes));

                        $pictureEntity = new Picture();
                        $pictureEntity->setPath($picturePath);
                        $lodgingEntity->addPicture($pictureEntity);
                    }
                    break;

                case 'location':
                    $locationEntity = $this->entityManager->getRepository(Location::class)->findOneBy(['id' => $requestObject->value]);
                    if ($locationEntity === null)
                        throw new BusinessException(400, 'The given locationId (' . $requestObject->value . ') is not associated with any location');

                    $lodgingEntity->setLocation($locationEntity);
                    break;

                case 'tags':
                    if (!is_array($requestObject->value))
                        throw new BusinessException(400, 'The given value must be an array of Tag id (int)');

                    // Remove all current tag entities
                    foreach ($lodgingEntity->getTags() as $tagEntity) {
                        $lodgingEntity->removeTag($tagEntity);
                    }

                    $tagEntities = $this->entityManager->getRepository(Tag::class)->findByIds($requestObject->value);
                    foreach ($requestObject->value as $requestTagId) {

                        $tagEntity = array_find($tagEntities, fn($tagEntity) => $tagEntity->getId() === $requestTagId);
                        if ($tagEntity === null)
                            throw new BusinessException(400, 'Unknown tag (' . $requestTagId . ')');

                        $lodgingEntity->addTag($tagEntity);
                    }
                    break;

                case 'equipments':
                    if (!is_array($requestObject->value))
                        throw new BusinessException(400, 'The given value must be an array of Equipment id (int)');

                    // Remove all current equipment entities
                    foreach ($lodgingEntity->getEquipments() as $equipmentEntity) {
                        $lodgingEntity->removeEquipment($equipmentEntity);
                    }

                    $equipmentEntities = $this->entityManager->getRepository(Equipment::class)->findByIds($requestObject->value);
                    foreach ($requestObject->value as $requestEquipmentId) {

                        $equipmentEntity = array_find($equipmentEntities, fn($equipmentEntity) => $equipmentEntity->getId() === $requestEquipmentId);
                        if ($equipmentEntity === null)
                            throw new BusinessException(400, 'Unknown equipment (' . $requestEquipmentId . ')');

                        $lodgingEntity->addEquipment($equipmentEntity);
                    }
                    break;

                default:
                    throw new BusinessException(400, 'The given property is not allowed for PATCH, allowed properties: title, description, cover, pictures, location (locationId), tags, equipments');
            }
            $this->entityManager->flush();

            return $this->convertToLodgingObject($lodgingEntity);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentTranslationStore $contentTranslationStore,
    ) {}
}
