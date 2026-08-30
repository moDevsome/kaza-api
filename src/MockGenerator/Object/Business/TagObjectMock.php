<?php

namespace Api\MockGenerator\Object\Business;

use DateTime;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\String\ByteString;
use Api\Object\Business\TagObject;
use Api\Interface\MockInterface;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
class TagObjectMock implements MockInterface
{

    public function generateOne(array $args = []): TagObject
    {
        return $this->generateList($args)[0];
    }

    public function generateList(array $args = []): array
    {
        $timestamp = time();
        $tagObjectsMock = array();
        $i = 1;
        do {
            $dateTime = new DateTime();
            $dateTime->setTimestamp($timestamp - ($i * 3600));
            $tagObjectsMock[] = new TagObject(Ulid::generate($dateTime), $this->generateName());
            $i++;
        } while ($i <= rand(4, 8));

        return $tagObjectsMock;
    }

    public function generateName(): string
    {
        return ByteString::fromRandom(rand(6, 12))->toString();
    }
}
