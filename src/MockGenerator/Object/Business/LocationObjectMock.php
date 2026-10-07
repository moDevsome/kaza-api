<?php

namespace Api\MockGenerator\Object\Business;

use DateTime;
use moDevsome\Palmo\Palmo;
use Symfony\Component\Uid\Ulid;
use Api\Object\Business\LocationObject;
use Api\Interface\MockInterface;
use Api\MockGenerator\Object\Business\LocationAreaObjectMock;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
class LocationObjectMock implements MockInterface
{

    private LocationAreaObjectMock $locationAreaMockGenerator;
    private Palmo $palmo;

    public function generateOne(array $args = []): LocationObject
    {
        return $this->generateList($args)[0];
    }

    public function generateList(array $args = []): array
    {
        $timestamp = time();
        $locationObjectsMock = array();
        $i = 1;
        do {
            $dateTime = new DateTime();
            $dateTime->setTimestamp($timestamp - ($i * 3600));
            $locationObjectsMock[] = new LocationObject(Ulid::generate($dateTime), $this->generateName(), $this->locationAreaMockGenerator->generateOne());
            $i++;
        } while ($i <= rand(4, 8));

        return $locationObjectsMock;
    }

    public function generateName(): string
    {
        return $this->palmo->lorem->word();
    }

    public function __construct()
    {
        $this->locationAreaMockGenerator = new LocationAreaObjectMock();
        $this->palmo = new Palmo();
    }
}
