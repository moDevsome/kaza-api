<?php

namespace Api\MockGenerator\Object\Business;

use moDevsome\Palmo\Palmo;
use Api\Interface\MockInterface;
use Api\Object\Business\HostObject;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
class HostObjectMock implements MockInterface
{

    private Palmo $palmo;

    public function generateOne(array $args = []): HostObject
    {
        return new HostObject($this->palmo->person->firstName(), $this->palmo->string->alpha());
    }

    public function generateList(array $args = []): array
    {
        $hostObjectsMock = array();
        $i = 1;
        do {
            $hostObjectsMock[] = $this->generateOne();
            $i++;
        } while ($i <= rand(4, 8));

        return $hostObjectsMock;
    }

    public function __construct()
    {
        $this->palmo = new Palmo();
    }
}
