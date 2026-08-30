<?php

namespace Api\MockGenerator\Entity;

use Symfony\Component\DependencyInjection\Attribute\When;
use Api\Interface\MockInterface;
use Api\Entity\User;

#[When(env: 'test')]
class UserMock implements MockInterface
{

    public function generateOne(array $args = []): User
    {
        return $this->generateList($args)[0];
    }

    public function generateList(array $args = []): array
    {
        $output = array();
        $i = 1;
        do {
            $user = new User();
            $user->setEmail('test-user-' . $i . '@test.com');
            $user->setRoles(array('REGISTERED'));
            $user->setPassword('****');

            $output[] = $user;
            $i++;
        } while ($i <= rand(4, 8));

        return $output;
    }
}
