<?php


namespace Api\Interface;

interface ObjectLoaderInterface
{

    public function loadList(array $criterias = []): array;

    public function loadOne(string|int $id): mixed;
}
