<?php


namespace Api\Interface;

use Api\Object\Business\PatchRequestObject;

interface ObjectHandlerInterface
{

    public function loadList(array $criterias = []): array;

    public function loadOne(string|int $id): mixed;

    public function patchOne(string $guid, string $property, PatchRequestObject $requestObject): mixed;
}
