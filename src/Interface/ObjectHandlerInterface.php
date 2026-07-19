<?php


namespace Api\Interface;

use Api\Object\Business\PatchRequestObject;

interface ObjectHandlerInterface
{

    /**
     * Load an object list according to the given criterias
     * @param array $criterias
     * @param int $limitCount
     * @param int $limitOffset
     * @return array
     */
    public function loadList(array $criterias, int $limitCount, int $limitOffset): array;

    /**
     * Load one object according to the given id
     * @param string $id,
     */
    public function loadOne(string $id): mixed;

    /**
     * Update a specific property of an object and then return the full object
     * @param string $id,
     * @param string $property,
     * @param PatchRequestObject $requestObject
     * @param bool $applyTranslation
     * @return mixed
     */
    public function patchOne(string $id, string $property, PatchRequestObject $requestObject, bool $applyTranslation): mixed;
}
