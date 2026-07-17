<?php

namespace Api\Object\Business;

class CreateLodgingRequestObject
{
    public function __construct(
        public readonly string $title, // The default title of the lodging
        public readonly string|null $cover,
        public readonly array|null $pictures, // Array of <string>
        public readonly string $description,  // The default description of the lodging
        public readonly string $locationId,
        public readonly array|null $equipmentIds, // Array of <string>
        public readonly array|null $tagIds, // Array of <string>
    ) {}
}
