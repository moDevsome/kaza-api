<?php

namespace Api\Object\Technical;

class ResponseBufferObject
{
    public function __construct(
        public readonly array|object|null $content,
        public readonly array $errors,
        public readonly array $warnings
    ) {}
}
