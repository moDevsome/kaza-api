<?php

namespace Api\Object\Business;

class CreateLocationRequestObject
{
    public function __construct(
        public readonly string $name,
        public readonly string $locationAreaId,
    ) {}
}
