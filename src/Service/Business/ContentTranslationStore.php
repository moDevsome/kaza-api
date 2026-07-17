<?php

namespace Api\Service\Business;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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
use Symfony\Component\Uid\Ulid;

/**
 * This class is made to load and store all the content translations
 */
final class ContentTranslationStore
{
    private array $contentTranslations = [];
    private array $allowedLangTags = [];
    private string $currentTag = 'fr-FR'; //TODO:use the symfony translate system to get the current tag

    private function loadCurrentTag(): void
    {

        $xUserLangHeader = $this->requestStack->getCurrentRequest()->headers->all()['x-user-lang-tag'] ?? array(null);
        $currentTag = count($xUserLangHeader) > 0 ? $xUserLangHeader[0] : null;

        if ($currentTag === null)
            throw new BusinessException(400, 'No user lang tag provided, please check "x-user-lang-tag" header');

        if (!in_array($currentTag, $this->allowedLangTags))
            throw new BusinessException(400, 'The given lang tag "' . $currentTag . '" is not allowed to be used as user lang, please check "x-user-lang-tag" header, allowed tags: ' . implode(', ', $this->allowedLangTags));

        $this->currentTag = $currentTag;
    }

    public function getCurrentTag(): string
    {
        return $this->currentTag;
    }

    public function getAllowedLangTags(): array
    {
        return $this->allowedLangTags;
    }

    public function setValues(
        string $contentId,
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

        // Load the target content
        $targetContentEntity = $this->entityManager->getRepository(
            match ($type->value) {
                'Lodging' => Lodging::class,
                'Tag' => Tag::class,
                'Equipment' => Equipment::class,
                'Location' => Location::class,
                'LocationArea' => LocationArea::class,
            }
        )->find($contentId);
        if ($targetContentEntity === null)
            throw new BusinessException(400, $type->value . ' (' . $contentId . ') not found');

        $translationKey = strtolower($type->value) . '.' . strtolower($property->value);
        $currentEntities = $this->entityManager->getRepository(ContentTranslation::class)->findBy(
            ['translationKey' => $translationKey, 'contentId' => $contentId]
        );

        foreach ($values as $value) {

            if (!in_array($value->tag, $this->allowedLangTags))
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
                $newEntity->setContentId($targetContentEntity->getId());
                $this->entityManager->persist($newEntity);
            }
        }
        $this->entityManager->flush();
    }

    public function getValue(string $key, Ulid $contentId, string $fallBack = ''): string
    {
        $translation = array_find(
            $this->contentTranslations,
            fn($row) => $row->getTranslationKey() === $key and $row->getContentId()->toString() === $contentId->toString() and $row->getTag() === $this->currentTag
        );

        return $translation !== null ? $translation->getTranslationValue() : $fallBack;
    }

    public function __construct(private readonly EntityManagerInterface $entityManager, private RequestStack $requestStack,)
    {

        $this->allowedLangTags = (isset($_ENV['ALLOWED_LANG_TAGS']) and is_string($_ENV['ALLOWED_LANG_TAGS'])) ? StringHelper::explode(',', $_ENV['ALLOWED_LANG_TAGS']) : ['fr-FR'];

        $this->loadCurrentTag();

        // Load lodging content translations
        $this->contentTranslations = $this->entityManager->getRepository(ContentTranslation::class)->findAll();
    }
}
