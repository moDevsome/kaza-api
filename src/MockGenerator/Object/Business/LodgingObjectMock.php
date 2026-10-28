<?php

namespace Api\MockGenerator\Object\Business;

use Symfony\Component\DependencyInjection\Attribute\When;
use moDevsome\Palmo\Palmo;
use Api\Object\Business\LodgingObject;
use Api\Interface\MockInterface;
use Api\MockGenerator\Object\Business\HostObjectMock;

#[When(env: 'test')]
class LodgingObjectMock implements MockInterface
{

    private Palmo $palmo;
    private HostObjectMock $hostGenerator;

    public function generateOne(array $args = []): LodgingObject
    {
        return new LodgingObject(
            $this->palmo->id->ulid(),
            $this->generateTitle(),
            $this->palmo->string->alpha(),
            [$this->palmo->string->alpha(), $this->palmo->string->alpha(), $this->palmo->string->alpha()],
            $this->palmo->lorem->paragraph(),
            $this->hostGenerator->generateOne(),
            (string) $this->palmo->number->int(1, 5),
            $this->palmo->address->city() . ' - ' . $this->palmo->address->country(),
            [$this->palmo->lorem->word(), $this->palmo->lorem->word(), $this->palmo->lorem->word()],
            [$this->palmo->lorem->word(), $this->palmo->lorem->word(), $this->palmo->lorem->word()],
        );
    }

    public function generateList(array $args = []): array
    {
        $lodgingObjectsMock = array();
        $i = 1;
        do {
            $lodgingObjectsMock[] = $this->generateOne();
            $i++;
        } while ($i <= rand(4, 8));

        return $lodgingObjectsMock;
    }

    public function generateTitle(): string
    {
        return $this->palmo->lorem->sentence();
    }

    public function addElement(LodgingObject $originalObject, string $type, string $value): LodgingObject
    {
        $tags = $originalObject->tags;
        $equipments = $originalObject->equipments;
        $pictures = $originalObject->pictures;

        if ($type === 'tag') {
            $tags[] = $value;
        } elseif ($type === 'equipment') {
            $equipments[] = $value;
        } elseif ($type === 'picture') {
            $pictures[] = $value;
        }

        return new LodgingObject(
            $originalObject->id,
            $originalObject->title,
            $originalObject->cover,
            $pictures,
            $originalObject->description,
            $originalObject->host,
            $originalObject->rating,
            $originalObject->location,
            $equipments,
            $tags,
        );
    }

    public function __construct()
    {
        $this->palmo = new Palmo();
        $this->hostGenerator = new HostObjectMock();
    }
}
