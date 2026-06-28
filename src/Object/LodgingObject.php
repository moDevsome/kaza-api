<?php

namespace Api\Object;

class LodgingObject
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $cover,
        public readonly array $pictures, // Array of <string>
        public readonly string $description,
        public readonly HostObject $host,
        public readonly string $rating,
        public readonly string $location,
        public readonly array $equipments, // Array of <string>
        public readonly array $tags, // Array of <string>
    ) {}
}
