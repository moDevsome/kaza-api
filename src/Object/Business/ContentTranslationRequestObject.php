<?php

namespace Api\Object\Business;

use Api\Enum\Business\ContentTranslationEquipmentProperty;
use Api\Enum\Business\ContentTranslationLodgingProperty;
use Api\Enum\Business\ContentTranslationTagProperty;
use Api\Enum\Business\ContentTranslationType;

class ContentTranslationRequestObject
{
    public function __construct(
        public readonly string|int $contentId,
        public readonly ContentTranslationType $type,
        public readonly ContentTranslationLodgingProperty|ContentTranslationEquipmentProperty|ContentTranslationTagProperty $property,
        public readonly array $values // Array of ContentTranslationRequestValueObject
    ) {}
}
