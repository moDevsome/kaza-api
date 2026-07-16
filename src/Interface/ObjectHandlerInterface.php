<?php


namespace Api\Interface;

use Api\Object\Business\PatchRequestObject;

interface ObjectHandlerInterface
{

    public function loadList(array $criterias = []): array;

    public function loadOne(string $id): mixed;

    public function patchOne(string $id, string $property, PatchRequestObject $requestObject): mixed;
}
