<?php


namespace Api\Interface;

interface MockInterface
{
    public function generateOne(array $args = []): mixed;

    public function generateList(array $args = []): array;
}
