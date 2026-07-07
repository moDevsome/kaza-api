<?php

namespace Api\Service\Business;

use Api\Entity\Equipment;
use Api\Entity\Host;
use Api\Entity\Location;
use Api\Entity\Lodging;
use Api\Entity\Tag;
use Api\Exception\BusinessException;
use Api\Service\Business\ContentTranslationStore;
use Api\Interface\ObjectLoaderInterface;
use Api\Object\Business\CreateLodgingRequestObject;
use Api\Object\Business\HostObject;
use Api\Object\Business\LodgingObject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Exception;

/**
 * This class is made to load one or more LoadgingObject from the database
 */
final class LodgingLoader implements ObjectLoaderInterface
{

    private function convertToLodgingObject(Lodging $input): LodgingObject
    {

        $hostEntity = $input->getHost();
        $locationEntity = $input->getLocation();

        return new LodgingObject(
            $input->getGuid(),
            $this->contentTranslationStore->getValue('lodging.title', $input->getId(), $input->getTitle()),
            $input->getCover(),
            array_map(fn($pictureEntity) => $pictureEntity->getPath(), $input->getPictures()->toArray()),
            $this->contentTranslationStore->getValue('lodging.description', $input->getId(), $input->getDescription()),
            new HostObject($hostEntity->getFirstname() . ' ' . $hostEntity->getLastname(), $hostEntity->getPicture()),
            $input->getRating(),
            implode(' - ', [
                $this->contentTranslationStore->getValue('location-area.name', $locationEntity->getArea()->getId(), $locationEntity->getArea()->getName()),
                $this->contentTranslationStore->getValue('location.name', $locationEntity->getId(), $locationEntity->getName())
            ]),
            array_map(fn($equipmentEntity) => $this->contentTranslationStore->getValue('equipment.name', $equipmentEntity->getId(), $equipmentEntity->getName()), $input->getEquipments()->toArray()),
            array_map(fn($tagEntity) => $this->contentTranslationStore->getValue('tag.name', $tagEntity->getId(), $tagEntity->getName()), $input->getTags()->toArray())
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

            $newEntity = new Lodging();
            $newEntity->setTitle($createRequest->title);
            $newEntity->setCover($createRequest->cover ?? '');
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
            $this->entityManager->flush();

            if ($newEntity === null)
                throw new BusinessException(500, 'Error occured while creating lodging');

            return $this->convertToLodgingObject($newEntity);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentTranslationStore $contentTranslationStore
    ) {}
}
