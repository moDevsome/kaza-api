<?php

namespace Api\Tests\Controller\Business;

use PHPUnit\Framework\MockObject\MockObject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Bundle\SecurityBundle\Security;
use moDevsome\Palmo\Palmo;
use Api\Entity\User;
use Api\Service\Business\LocationObjectHandler;
use Api\MockGenerator\Object\Business\LocationObjectMock;
use Api\Object\Business\LocationAreaObject;
use Api\Object\Business\LocationObject;
use Api\Repository\LocationRepository;

#[When(env: 'test')]
class LocationControllerTest extends WebTestCase
{
    private $setUpBeforeAllStatus = false;
    private $locationObjectsMock = array(); // Array of LocationObject

    private KernelBrowser $client;
    private MockObject&LocationObjectHandler $locationObjectHandlerMock;
    private LocationObjectMock $mockGenerator;

    private function setUpBeforeAll(): void
    {
        /**
         * Create client
         */
        $client = static::createClient();

        /**
         * Defines mocks values
         */
        $this->mockGenerator = $this->getContainer()->get(LocationObjectMock::class);
        $this->locationObjectsMock = $this->mockGenerator->generateList();
        $this->locationObjectHandlerMock = $this->createMock(LocationObjectHandler::class);

        // Mock security service to get the logged user with "getUser"();
        $palmo = new Palmo();
        $userStub = $this->createStub(User::class);
        $userStub->method('getId')->willReturn($palmo->number->int(1, 9999));
        $userStub->method('getEmail')->willReturn($palmo->contact->email());
        $userStub->method('getRoles')->willReturn(['REGISTERED']);
        $securityStub = $this->createStub(Security::class);
        $securityStub->method('getUser')->willReturn($userStub);

        // Handle auth
        $client->loginUser($userStub);

        $client->getContainer()->set(LocationObjectHandler::class, $this->locationObjectHandlerMock);

        $this->client = $client;
    }

    // Before each
    protected function setUp(): void
    {
        if ($this->setUpBeforeAllStatus === false) {
            $this->setUpBeforeAll();
        }
        parent::setUp();
    }

    public function testIndex(): void
    {
        $this->locationObjectHandlerMock->expects(self::once())
            ->method('loadList')
            ->willReturn($this->locationObjectsMock);

        // Request the endpoint
        $this->client->request('GET', '/location');

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        foreach ($this->locationObjectsMock as $locationObjectKey => $locationObjectValue) {
            $this->assertSame(json_encode($locationObjectValue), json_encode($content[$locationObjectKey]));
        }
    }

    public function testLocation(): void
    {

        // Pick random location from the mock
        $location = $this->locationObjectsMock[array_rand($this->locationObjectsMock)];

        // Handle service response
        $this->locationObjectHandlerMock->expects(self::once())
            ->method('loadOne')
            ->with($location->id)
            ->willReturn($location);

        // Request the endpoint
        $this->client->request('GET', '/location/' . $location->id);

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(json_encode($location), json_encode($content));
    }

    public function testCreate(): void
    {
        $locationMock = $this->locationObjectsMock[0];

        $this->locationObjectHandlerMock->expects(self::once())
            ->method('createOne')
            ->willReturn($locationMock);

        // Request the endpoint
        $this->client->request('POST', '/auth/location', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'name' => $locationMock->name,
            'locationAreaId' => $locationMock->area->id,
        ]));

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(json_encode($locationMock), json_encode($content));
    }

    public function testUpdate(): void
    {
        $locationMock = $this->locationObjectsMock[0];
        $updatedObject = new LocationObject($locationMock->id, $this->mockGenerator->generateName(), $locationMock->area);

        $locationRepositoryMock = $this->createMock(LocationRepository::class);
        $locationRepositoryMock->expects(self::once())
            ->method('countLocationByUserId')
            ->willReturn(0);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::once())
            ->method('getRepository')
            ->willReturn($locationRepositoryMock);

        $this->client->getContainer()->set(EntityManagerInterface::class, $entityManagerMock);

        $this->locationObjectHandlerMock->expects(self::once())
            ->method('updateOne')
            ->willReturn($updatedObject);

        // Request the endpoint
        $this->client->request('PUT', '/auth/location/' . $locationMock->id, server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'name' => $updatedObject->name,
            'locationAreaId' => $locationMock->area->id,
        ]));

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(json_encode($updatedObject), json_encode($content));
    }

    public function testDelete(): void
    {
        $location = $this->locationObjectsMock[0];

        $locationRepositoryMock = $this->createMock(LocationRepository::class);
        $locationRepositoryMock->expects(self::once())
            ->method('countLocationByUserId')
            ->willReturn(0);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::once())
            ->method('getRepository')
            ->willReturn($locationRepositoryMock);

        $this->client->getContainer()->set(EntityManagerInterface::class, $entityManagerMock);

        $this->locationObjectHandlerMock->expects(self::once())
            ->method('deleteOne');

        // Request the endpoint
        $this->client->request('DELETE', '/auth/location/' . $location->id);

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(['OK'], $content);
    }
}
