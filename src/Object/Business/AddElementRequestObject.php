<?php

namespace Api\Object\Business;

class AddElementRequestObject
{
    public function __construct(
        public readonly string|int $value,
    ) {}
}
