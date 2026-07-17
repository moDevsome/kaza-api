<?php

namespace Api\Object\Business;

abstract class CreateElementRequestObject
{
    public function __construct(
        public readonly string $name,
        public readonly bool $autoTranslate = false, // Only available for some properties, if TRUE, the translation will be applied as well according to the current user-lang-tag
    ) {}
}
