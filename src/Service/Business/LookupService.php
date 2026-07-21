<?php

namespace Api\Service\Business;

use Doctrine\ORM\EntityManagerInterface;
use Api\Entity\ContentTranslation;
use Api\Entity\Tag;
use Api\Entity\Equipment;
use Api\Entity\Location;

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
     * @param array $extraCriteria
     */
    public function find(string $contentType, string $query, array $extraCriteria = array()): array
    {

        if ($contentType === 'LODGING') {
            //TODO:handle loging lookup
            return array();
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
