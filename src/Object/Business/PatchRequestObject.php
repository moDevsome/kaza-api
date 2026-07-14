<?php

namespace Api\Object\Business;

class PatchRequestObject
{
    public function __construct(
        public readonly string|array|int $value,
        public readonly bool $autoTranslate = false, // Only available for some properties, if TRUE, the translation will be applied as well according to the current user-lang-tag
    ) {}
}
