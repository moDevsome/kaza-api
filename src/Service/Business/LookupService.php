<?php

namespace Api\Service\Business;

use Doctrine\ORM\EntityManagerInterface;
use Api\Entity\ContentTranslation;
use Api\Entity\Tag;
use Api\Entity\Equipment;
use Api\Entity\Location;
use Api\Entity\Lodging;

/**
 * This service is made to find any content regarding the given terme
 */
final class LookupService
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentTranslationStore $contentTranslationStore
    ) {}

    /**
     * Content Look up
     * @phpstan-param 'LODGING'|'LOCATION'|'EQUIPMENT'|'TAG' $contentType
     * @param string $query
     * @param ?array $criteria
     * @param ?array $orderBy
     * @param ?int $limitCount
     * @param ?int $limitOffset
     * @return array Array of entities
     */
    public function find(string $contentType, string $query, array $criteria = array(), ?array $orderBy = null, ?int $limitCount = 10000, ?int $limitOffset = 0): array
    {

        if ($contentType === 'LODGING') {

            $lodgingRepository = $this->entityManager->getRepository(Lodging::class);
            if (strlen($query) === 0) {
                return $lodgingRepository->findBy($criteria, $orderBy, $limitCount, $limitOffset);
            }

            // Find tags, equipments and locations by name
            $tagIds = array_map(fn($tag) => $tag->getId(), $this->entityManager->getRepository(Tag::class)->findByName($query, 'REGEXP'));
            $equipmentIds = array_map(fn($equipment) => $equipment->getId(), $this->entityManager->getRepository(Equipment::class)->findByName($query, 'REGEXP'));
            $locationIds = array_map(fn($location) => $location->getId(),  $this->entityManager->getRepository(Location::class)->findByName($query, 'REGEXP'));

            // Find tags, equipments and locations by translation
            $lodgingIdsByTranslations = array();
            $tagIdsByTranslations = array();
            $equipmentIdsByTranslations = array();
            $locationIdsByTranslations = array();
            foreach (
                $this->entityManager->getRepository(ContentTranslation::class)->findByTranslationValue(
                    $query,
                    $this->contentTranslationStore->getCurrentTag(),
                    null,
                    'REGEXP'
                ) as $translationEntity
            ) {
                match ($translationEntity->getTranslationKey()) {
                    'lodging.title' => $lodgingIdsByTranslations[] = $translationEntity->getContentId(),
                    'lodging.description' => $lodgingIdsByTranslations[] = $translationEntity->getContentId(),
                    'tag.name' => $tagIdsByTranslations[] = $translationEntity->getContentId(),
                    'equipment.name' => $equipmentIdsByTranslations[] = $translationEntity->getContentId(),
                    'location.name' => $locationIdsByTranslations[] = $translationEntity->getContentId()
                };
            }

            // Find lodging ids
            $lodgingIdsByContent = $lodgingRepository->findIdsByContent($query, 'REGEXP');
            $lodgingIdsByTagIds = $lodgingRepository->findIdsByTagIds(array_unique(array_merge($tagIds, $tagIdsByTranslations)));
            $lodgingIdsByEquipmentIds = $lodgingRepository->findIdsByEquipmentIds(array_unique(array_merge($equipmentIds, $equipmentIdsByTranslations)));
            $lodgingIdsByLocationIds = array_map(
                fn($lodging) => $lodging->getId(),
                $lodgingRepository->findByLocationIds(array_unique(array_merge($locationIds, $locationIdsByTranslations)))
            );

            $lodgingIds = array_unique(array_merge($lodgingIdsByContent, $lodgingIdsByTagIds, $lodgingIdsByEquipmentIds, $lodgingIdsByLocationIds));

            // Find lodgings
            return $lodgingRepository->findByIds($lodgingIds, $criteria, $orderBy, $limitCount, $limitOffset);
        } else {

            $repository = $this->entityManager->getRepository([
                'LOCATION' => Location::class,
                'EQUIPMENT' => Equipment::class,
                'TAG' => Tag::class
            ][$contentType]);

            // --- Translations ---
            $translations = $this->entityManager->getRepository(ContentTranslation::class)->findByTranslationValue(
                $query,
                $this->contentTranslationStore->getCurrentTag(),
                strtolower($contentType) . '.name',
            );
            if (count($translations) > 0)
                return $repository->findByIds(array_map(fn($translation) => $translation->getContentId(), $translations));

            // --- Entity ---
            return $repository->findByName($query);
        }
    }
}
