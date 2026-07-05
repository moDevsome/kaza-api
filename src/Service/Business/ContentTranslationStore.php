<?php

namespace Api\Service\Business;

use Api\Entity\ContentTranslation;
use Doctrine\ORM\EntityManagerInterface;

/**
 * This class is made to load and store all the content translations
 */
final class ContentTranslationStore
{
    private array $contentTranslations = [];
    private string $currentTag = 'fr-FR'; //TODO:use the symfony translate system to get the current tag

    public function getValue(string $key, int $contentId, string $fallBack = ''): string
    {

        $translation = array_find(
            $this->contentTranslations,
            fn($row) => $row->getTranslationKey() === $key and $row->getContentId() === $contentId and $row->getTag() === $this->currentTag
        );

        return $translation !== null ? $translation->getTranslationValue() : $fallBack;
    }

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {

        // Load lodging content translations
        $this->contentTranslations = $this->entityManager->getRepository(ContentTranslation::class)->findAll();
    }
}
