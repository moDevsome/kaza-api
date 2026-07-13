<?php

namespace Api\Object\Business;

class ContentTranslationRequestValueObject
{
    public function __construct(
        public readonly string $tag,
        public readonly string $value,
    ) {}
}
