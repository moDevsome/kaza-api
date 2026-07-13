<?php

namespace Api\Service\Business;

use Doctrine\ORM\EntityManagerInterface;
use Api\Entity\ContentTranslation;
use Api\Entity\Tag;
use Api\Entity\Equipment;
use Api\Entity\Location;
use Api\Entity\LocationArea;
use Api\Entity\Lodging;
use Api\Enum\Business\ContentTranslationEquipmentProperty;
use Api\Enum\Business\ContentTranslationLocationAreaProperty;
use Api\Enum\Business\ContentTranslationLocationProperty;
use Api\Enum\Business\ContentTranslationLodgingProperty;
use Api\Enum\Business\ContentTranslationTagProperty;
use Api\Enum\Business\ContentTranslationType;
use Api\Exception\BusinessException;
use Api\Helper\StringHelper;

/**
 * This class is made to load and store all the content translations
 */
final class ContentTranslationStore
{
    private array $contentTranslations = [];
    private array $allowedLangs = [];
    private string $currentTag = 'fr-FR'; //TODO:use the symfony translate system to get the current tag

    public function setValues(
        string|int $contentId,
        ContentTranslationType $type,
        ContentTranslationLodgingProperty|ContentTranslationEquipmentProperty|ContentTranslationTagProperty $property,
        array $values
    ) {

        $propertiesByType = [
            ContentTranslationType::Lodging->value => [
                ContentTranslationLodgingProperty::Title->value,
                ContentTranslationLodgingProperty::Description->value
            ],
            ContentTranslationType::Tag->value => [
                ContentTranslationTagProperty::Name->value
            ],
            ContentTranslationType::Equipment->value => [
                ContentTranslationEquipmentProperty::Name->value
            ],
            ContentTranslationType::Location->value => [
                ContentTranslationLocationProperty::Name->value
            ],
            ContentTranslationType::LocationArea->value => [
                ContentTranslationLocationAreaProperty::Name->value
            ]
        ];

        if (!in_array($property->value, $propertiesByType[$type->value]))
            throw new BusinessException(400, 'The property must belong to a backed enumeration of type Api\\Enum\\Business\\ContentTranslation' . $type->value . 'Property"');

        // Check if the content exist
        if ($this->entityManager->getRepository(
            match ($type->value) {
                'Lodging' => Lodging::class,
                'Tag' => Tag::class,
                'Equipment' => Equipment::class,
                'Location' => Location::class,
                'LocationArea' => LocationArea::class,
            }
        )->find($contentId) === null)
            throw new BusinessException(400, $type->value . ' (' . $contentId . ') not found');

        $translationKey = strtolower($type->value) . '.' . strtolower($property->value);
        $currentEntities = $this->entityManager->getRepository(ContentTranslation::class)->findBy(
            ['translationKey' => $translationKey, 'contentId' => $contentId]
        );

        foreach ($values as $value) {

            if (!in_array($value->tag, $this->allowedLangs))
                throw new BusinessException(400, 'The lang tag "' . $value->tag . '" is not allowed');

            $currentEntity = array_find($currentEntities, fn($entity) => $entity->getTag() === $value->tag);
            if ($currentEntity !== null) {
                $currentEntity->setTranslationValue($value->value);
                $this->entityManager->persist($currentEntity);
            } else {
                $newEntity = new ContentTranslation();
                $newEntity->setTranslationKey($translationKey);
                $newEntity->setTranslationValue($value->value);
                $newEntity->setTag($value->tag);
                $newEntity->setContentId($contentId);
                $this->entityManager->persist($newEntity);
            }
        }
        $this->entityManager->flush();
    }

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

        $this->allowedLangs = (isset($_ENV['ALLOWED_LANGS']) and is_string($_ENV['ALLOWED_LANGS'])) ? StringHelper::explode(',', $_ENV['ALLOWED_LANGS']) : ['fr-FR'];

        // Load lodging content translations
        $this->contentTranslations = $this->entityManager->getRepository(ContentTranslation::class)->findAll();
    }
}
