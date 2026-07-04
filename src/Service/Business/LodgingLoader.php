<?php

namespace Api\Service\Business;

use Api\Entity\Lodging;
use Api\Interface\ObjectLoaderInterface;
use Api\Object\Business\HostObject;
use Api\Object\Business\LodgingObject;
use Doctrine\ORM\EntityManagerInterface;

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
            $input->getTitle(),
            $input->getCover(),
            array_map(fn($pictureEntity) => $pictureEntity->getPath(), $input->getPictures()->toArray()),
            $input->getDescription(),
            new HostObject($hostEntity->getFirstname() . ' ' . $hostEntity->getLastname(), $hostEntity->getPicture()),
            $input->getRating(),
            $locationEntity->getArea()->getName() . ' - ' . $locationEntity->getName(),
            array_map(fn($equipmentEntity) => $equipmentEntity->getName(), $input->getEquipments()->toArray()),
            array_map(fn($tagEntity) => $tagEntity->getName(), $input->getTags()->toArray())
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

    public function __construct(private readonly EntityManagerInterface $entityManager) {}
}
