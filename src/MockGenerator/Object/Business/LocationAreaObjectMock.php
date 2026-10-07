<?php

namespace Api\MockGenerator\Object\Business;

use DateTime;
use moDevsome\Palmo\Palmo;
use Symfony\Component\Uid\Ulid;
use Api\Object\Business\LocationAreaObject;
use Api\Interface\MockInterface;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
class LocationAreaObjectMock implements MockInterface
{

    private Palmo $palmo;

    public function generateOne(array $args = []): LocationAreaObject
    {
        return $this->generateList($args)[0];
    }

    public function generateList(array $args = []): array
    {
        $timestamp = time();
        $locationAreaObjectsMock = array();
        $i = 1;
        do {
            $dateTime = new DateTime();
            $dateTime->setTimestamp($timestamp - ($i * 3600));
            $locationAreaObjectsMock[] = new LocationAreaObject(Ulid::generate($dateTime), $this->generateName());
            $i++;
        } while ($i <= rand(4, 8));

        return $locationAreaObjectsMock;
    }

    public function generateName(): string
    {
        return $this->palmo->lorem->word();
    }

    public function __construct()
    {
        $this->palmo = new Palmo();
    }
}
