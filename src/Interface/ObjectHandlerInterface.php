<?php


namespace Api\Interface;

interface ObjectHandlerInterface
{

    public function loadList(array $criterias = []): array;

    public function loadOne(string|int $id): mixed;
}
