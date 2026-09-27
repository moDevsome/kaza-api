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
use Api\Service\Business\EquipmentObjectHandler;
use Api\MockGenerator\Object\Business\EquipmentObjectMock;
use Api\Object\Business\EquipmentObject;
use Api\Repository\EquipmentRepository;

#[When(env: 'test')]
class EquipmentControllerTest extends WebTestCase
{
    private $setUpBeforeAllStatus = false;
    private $equipmentObjectsMock = array(); // Array of EquipmentObject

    private KernelBrowser $client;
    private MockObject&EquipmentObjectHandler $equipmentObjectHandlerMock;
    private EquipmentObjectMock $mockGenerator;

    private function setUpBeforeAll(): void
    {
        /**
         * Create client
         */
        $client = static::createClient();

        /**
         * Defines mocks values
         */
        $this->mockGenerator = $this->getContainer()->get(EquipmentObjectMock::class);
        $this->equipmentObjectsMock = $this->mockGenerator->generateList();
        $this->equipmentObjectHandlerMock = $this->createMock(EquipmentObjectHandler::class);

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

        $client->getContainer()->set(EquipmentObjectHandler::class, $this->equipmentObjectHandlerMock);

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
        $this->equipmentObjectHandlerMock->expects(self::once())
            ->method('loadList')
            ->willReturn($this->equipmentObjectsMock);

        // Request the endpoint
        $this->client->request('GET', '/equipment');

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(array_map('get_object_vars', $this->equipmentObjectsMock), array_map('get_object_vars', $content));
    }

    public function testEquipment(): void
    {

        // Pick random equipment from the mock
        $equipment = $this->equipmentObjectsMock[array_rand($this->equipmentObjectsMock)];

        // Handle service response
        $this->equipmentObjectHandlerMock->expects(self::once())
            ->method('loadOne')
            ->with($equipment->id)
            ->willReturn($equipment);

        // Request the endpoint
        $this->client->request('GET', '/equipment/' . $equipment->id);

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(get_object_vars($equipment), get_object_vars($content));
    }

    public function testCreate(): void
    {
        $this->equipmentObjectHandlerMock->expects(self::once())
            ->method('createOne')
            ->willReturn($this->equipmentObjectsMock[0]);

        // Request the endpoint
        $this->client->request('POST', '/auth/equipment', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'name' => $this->equipmentObjectsMock[0]->name
        ]));

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(get_object_vars($this->equipmentObjectsMock[0]), get_object_vars($content));
    }

    public function testUpdate(): void
    {
        $equipment = $this->equipmentObjectsMock[0];
        $updatedObject = new EquipmentObject($equipment->id, $this->mockGenerator->generateName());

        $equipmentRepositoryMock = $this->createMock(EquipmentRepository::class);
        $equipmentRepositoryMock->expects(self::once())
            ->method('countEquipmentByUserId')
            ->willReturn(0);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::once())
            ->method('getRepository')
            ->willReturn($equipmentRepositoryMock);

        $this->client->getContainer()->set(EntityManagerInterface::class, $entityManagerMock);

        $this->equipmentObjectHandlerMock->expects(self::once())
            ->method('updateOne')
            ->willReturn($updatedObject);

        // Request the endpoint
        $this->client->request('PUT', '/auth/equipment/' . $equipment->id, server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'name' => $updatedObject->name
        ]));

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(get_object_vars($updatedObject), get_object_vars($content));
    }

    public function testDelete(): void
    {
        $equipment = $this->equipmentObjectsMock[0];

        $equipmentRepositoryMock = $this->createMock(EquipmentRepository::class);
        $equipmentRepositoryMock->expects(self::once())
            ->method('countEquipmentByUserId')
            ->willReturn(0);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::once())
            ->method('getRepository')
            ->willReturn($equipmentRepositoryMock);

        $this->client->getContainer()->set(EntityManagerInterface::class, $entityManagerMock);

        $this->equipmentObjectHandlerMock->expects(self::once())
            ->method('deleteOne');

        // Request the endpoint
        $this->client->request('DELETE', '/auth/equipment/' . $equipment->id);

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(['OK'], $content);
    }
}
