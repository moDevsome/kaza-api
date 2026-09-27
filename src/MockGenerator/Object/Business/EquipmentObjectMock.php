<?php

namespace Api\MockGenerator\Object\Business;

use DateTime;
use moDevsome\Palmo\Palmo;
use Symfony\Component\Uid\Ulid;
use Api\Object\Business\EquipmentObject;
use Api\Interface\MockInterface;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
class EquipmentObjectMock implements MockInterface
{

    private Palmo $palmo;

    public function generateOne(array $args = []): EquipmentObject
    {
        return $this->generateList($args)[0];
    }

    public function generateList(array $args = []): array
    {
        $timestamp = time();
        $equipmentObjectsMock = array();
        $i = 1;
        do {
            $dateTime = new DateTime();
            $dateTime->setTimestamp($timestamp - ($i * 3600));
            $equipmentObjectsMock[] = new EquipmentObject(Ulid::generate($dateTime), $this->generateName());
            $i++;
        } while ($i <= rand(4, 8));

        return $equipmentObjectsMock;
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
