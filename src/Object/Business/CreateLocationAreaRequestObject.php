<?php

namespace Api\Object\Business;

class CreateLocationAreaRequestObject
{
    public function __construct(
        public readonly string $name,
    ) {}
}
