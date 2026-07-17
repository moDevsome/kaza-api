<?php

namespace Api\Object\Business;

abstract class ElementObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $name
    ) {}
}
