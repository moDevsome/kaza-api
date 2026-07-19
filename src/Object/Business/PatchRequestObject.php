<?php

namespace Api\Object\Business;

class PatchRequestObject
{
    public function __construct(
        public readonly string|array|int $value,
    ) {}
}
