<?php

namespace Api\Object\Business;

abstract class CreateElementRequestObject
{
    public function __construct(
        public readonly string $name,
    ) {}
}
