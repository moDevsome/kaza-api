<?php

namespace Api\Tests\Controller\Business;

use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use Api\Service\Business\LocationAreaObjectHandler;
use Api\MockGenerator\Object\Business\LocationAreaObjectMock;

#[When(env: 'test')]
class LocationAreaControllerTest extends WebTestCase
{
    private $setUpBeforeAllStatus = false;
    private $locationAreaObjectsMock = array(); // Array of LocationAreaObject

    private KernelBrowser $client;
    private MockObject&LocationAreaObjectHandler $locationAreaObjectHandlerMock;
    private LocationAreaObjectMock $mockGenerator;

    private function setUpBeforeAll(): void
    {
        /**
         * Create client
         */
        $client = static::createClient();

        /**
         * Defines mocks values
         */
        $this->mockGenerator = $this->getContainer()->get(LocationAreaObjectMock::class);
        $this->locationAreaObjectsMock = $this->mockGenerator->generateList();
        $this->locationAreaObjectHandlerMock = $this->createMock(LocationAreaObjectHandler::class);

        $client->getContainer()->set(LocationAreaObjectHandler::class, $this->locationAreaObjectHandlerMock);

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
        $this->locationAreaObjectHandlerMock->expects(self::once())
            ->method('loadList')
            ->willReturn($this->locationAreaObjectsMock);

        // Request the endpoint
        $this->client->request('GET', '/location-area');

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(array_map('get_object_vars', $this->locationAreaObjectsMock), array_map('get_object_vars', $content));
    }

    public function testLocationArea(): void
    {

        // Pick random tag from the mock
        $locationArea = $this->locationAreaObjectsMock[array_rand($this->locationAreaObjectsMock)];

        // Handle service response
        $this->locationAreaObjectHandlerMock->expects(self::once())
            ->method('loadOne')
            ->with($locationArea->id)
            ->willReturn($locationArea);

        // Request the endpoint
        $this->client->request('GET', '/location-area/' . $locationArea->id);

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(get_object_vars($locationArea), get_object_vars($content));
    }
}
