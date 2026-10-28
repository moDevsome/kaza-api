<?php

namespace Api\Tests\Controller\Business;

use Api\Entity\Host;
use PHPUnit\Framework\MockObject\MockObject;
use \PHPUnit\Framework\MockObject\Stub;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Uid\Ulid;
use moDevsome\Palmo\Palmo;
use Api\Object\Business\AddElementRequestObject;
use Api\Entity\Lodging;
use Api\Entity\User;
use Api\Service\Business\LodgingObjectHandler;
use Api\MockGenerator\Object\Business\LodgingObjectMock;
use Api\Object\Business\LodgingObject;
use Api\Repository\HostRepository;
use Api\Repository\LodgingRepository;

#[When(env: 'test')]
class LodgingControllerTest extends WebTestCase
{
    private $setUpBeforeAllStatus = false;
    private $lodgingObjectsMock = array(); // Array of LodgingObject
    private LodgingObject $lodgingObjectMock;

    private KernelBrowser $client;
    private MockObject&LodgingObjectHandler $lodgingObjectHandlerMock;
    private LodgingObjectMock $mockGenerator;

    private Palmo $palmo;

    private User&Stub $userStub;
    private \Api\Entity\Host&Stub $hostStub;
    private HostRepository $hostRepositoryMock;
    private \Api\Entity\Lodging&Stub $lodgingStub;
    private LodgingRepository $lodgingRepositoryMock;

    private function setUpBeforeAll(): void
    {
        /**
         * Create client
         */
        $client = static::createClient();

        /**
         * Defines mocks values
         */
        $this->palmo = new Palmo();
        $this->mockGenerator = $this->getContainer()->get(LodgingObjectMock::class);
        $this->lodgingObjectsMock = $this->mockGenerator->generateList();
        $this->lodgingObjectMock = $this->lodgingObjectsMock[0];
        $this->lodgingObjectHandlerMock = $this->createMock(LodgingObjectHandler::class);

        // Mock security service to get the logged user with "getUser"();
        $palmo = new Palmo();
        $this->userStub = $this->createStub(User::class);
        $this->userStub->method('getId')->willReturn($palmo->number->int(1, 9999));
        $this->userStub->method('getEmail')->willReturn($palmo->contact->email());
        $this->userStub->method('getRoles')->willReturn(['REGISTERED']);
        $securityStub = $this->createStub(Security::class);
        $securityStub->method('getUser')->willReturn($this->userStub);

        // Mock host repository
        $this->hostStub = $this->createStub(Host::class);
        $this->hostStub->method('getId')->willReturn(Ulid::fromString($this->palmo->id->ulid()));
        $this->hostStub->method('getLastname')->willReturn($this->palmo->person->lastName());
        $this->hostStub->method('getFirstname')->willReturn($this->palmo->person->firstName());
        $this->hostStub->method('getPicture')->willReturn($this->palmo->string->alpha());

        $this->hostRepositoryMock = $this->createMock(HostRepository::class);

        // Mock lodging repository
        $this->lodgingStub = $this->createStub(Lodging::class);
        $this->lodgingStub->method('getId')->willReturn(Ulid::fromString($this->lodgingObjectMock->id));
        $this->lodgingStub->method('getHost')->willReturn($this->hostStub);

        $this->lodgingRepositoryMock = $this->createMock(LodgingRepository::class);

        // Handle auth
        $client->loginUser($this->userStub);

        $client->getContainer()->set(HostRepository::class, $this->hostRepositoryMock);
        $client->getContainer()->set(LodgingRepository::class, $this->lodgingRepositoryMock);
        $client->getContainer()->set(LodgingObjectHandler::class, $this->lodgingObjectHandlerMock);

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
        $this->lodgingObjectHandlerMock->expects(self::once())
            ->method('loadList')
            ->willReturn($this->lodgingObjectsMock);

        // Request the endpoint
        $this->client->request('GET', '/lodging');

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        foreach ($this->lodgingObjectsMock as $lodgingObjectKey => $lodgingObjectValue) {
            $this->assertSame(json_encode($lodgingObjectValue), json_encode($content[$lodgingObjectKey]));
        }
    }

    public function testLodging(): void
    {

        // Pick random lodging from the mock
        $lodging = $this->lodgingObjectsMock[array_rand($this->lodgingObjectsMock)];

        // Handle service response
        $this->lodgingObjectHandlerMock->expects(self::once())
            ->method('loadOne')
            ->with($lodging->id)
            ->willReturn($lodging);

        // Request the endpoint
        $this->client->request('GET', '/lodging/' . $lodging->id);

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(json_encode($lodging), json_encode($content));
    }

    public function testCreate(): void
    {
        $this->hostRepositoryMock->expects(self::once())
            ->method('findOneByUserId')
            ->willReturn($this->hostStub);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::once())
            ->method('getRepository')
            ->willReturn($this->hostRepositoryMock);

        $this->client->getContainer()->set(EntityManagerInterface::class, $entityManagerMock);

        $this->lodgingObjectHandlerMock->expects(self::once())
            ->method('createOne')
            ->willReturn($this->lodgingObjectMock);

        // Request the endpoint
        $this->client->request('POST', '/auth/lodging', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'title' => $this->lodgingObjectMock->title,
            'cover' => $this->lodgingObjectMock->cover,
            'pictures' => $this->lodgingObjectMock->pictures,
            'description' => $this->lodgingObjectMock->description,
            'locationId' => $this->palmo->id->ulid(),
            'equipmentIds' => [$this->palmo->id->ulid(), $this->palmo->id->ulid(), $this->palmo->id->ulid()],
            'tagIds' => [$this->palmo->id->ulid(), $this->palmo->id->ulid(), $this->palmo->id->ulid()],
        ]));

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(json_encode($this->lodgingObjectMock), json_encode($content));
    }

    public function testAddElement(): void
    {
        $lodgingId = $this->lodgingStub->getId();
        $allowedElementsType = ['picture', 'tag', 'equipment'];
        $elementType = $allowedElementsType[array_rand($allowedElementsType)];
        $elementValue = $this->palmo->lorem->word();

        $updatedLodgingObject = $this->mockGenerator->addElement($this->lodgingObjectMock, $elementType, $elementValue);

        $this->lodgingRepositoryMock->expects(self::once())
            ->method('findOneBy')
            ->with(['id' => $lodgingId])
            ->willReturn($this->lodgingStub);

        $this->lodgingObjectHandlerMock->expects(self::once())
            ->method('addElement')
            ->with($lodgingId, $elementType, new AddElementRequestObject($elementValue))
            ->willReturn($updatedLodgingObject);

        // Called by checkOwner()
        $this->hostRepositoryMock->expects(self::once())
            ->method('findOneByUserId')
            ->with($this->userStub->getId())
            ->willReturn($this->hostStub);

        // Request the endpoint
        $this->client->request('POST', '/auth/lodging/' . $lodgingId . '/add-' . $elementType, server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'value' => $elementValue,
        ]));

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(json_encode($updatedLodgingObject), json_encode($content));
    }

    public function testRemoveElement(): void
    {
        $this->markTestSkipped('Temporary disabled, need to use "findOneBy" real method instead of virtual methode');

        /*
        $lodgingId = $this->lodgingStub->getId();
        $allowedElementsType = ['picture', 'tag', 'equipment'];
        $elementType = $allowedElementsType[array_rand($allowedElementsType)];

        $this->lodgingRepositoryMock->expects(self::once())
            ->method('findOneById')
            ->with(['id' => $lodgingId])
            ->willReturn($this->lodgingStub);

        $this->lodgingObjectHandlerMock->expects(self::once())
            ->method('removeElement')
            ->with($lodgingId, $elementType, new RemoveElementRequestObject(''))
            ->willReturn($this->lodgingObjectMock);

        // Called by checkOwner()
        $this->hostRepositoryMock->expects(self::once())
            ->method('findOneByUserId')
            ->with($this->userStub->getId())
            ->willReturn($this->hostStub);

        // Request the endpoint
        $this->client->request('DELETE', '/auth/lodging/' . $lodgingId . '/remove-' . $elementType, server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'value' => '',
        ]));

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(json_encode($this->lodgingObjectMock), json_encode($content));
        */
    }
}
