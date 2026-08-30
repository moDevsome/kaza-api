<?php

namespace Api\Tests\Controller\Business;

use PHPUnit\Framework\MockObject\MockObject;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Bundle\SecurityBundle\Security;
use Api\Entity\User;
use Api\MockGenerator\Entity\UserMock;
use Api\Service\Business\TagObjectHandler;
use Api\MockGenerator\Object\Business\TagObjectMock;
use Api\Object\Business\TagObject;
use Api\Repository\TagRepository;

#[When(env: 'test')]
class TagControllerTest extends WebTestCase
{
    private $setUpBeforeAllStatus = false;
    private $tagObjectsMock = array(); // Array of TagObject

    private KernelBrowser $client;
    private MockObject&TagObjectHandler $tagObjectHandlerMock;
    private TagObjectMock $mockGenerator;

    private function setUpBeforeAll(): void
    {
        /**
         * Create client
         */
        $client = static::createClient();

        /**
         * Defines mocks values
         */
        $this->mockGenerator = $this->getContainer()->get(TagObjectMock::class);
        $this->tagObjectsMock = $this->mockGenerator->generateList();
        $this->tagObjectHandlerMock = $this->createMock(TagObjectHandler::class);

        // Mock security service to get the logged user with "getUser"
        $userGenerator = $this->getContainer()->get(UserMock::class);
        $user = $userGenerator->generateOne();
        $security = $this->createStub(Security::class);

        $userMock = $this->createStub(User::class);
        $userMock->method('getId')->willReturn(42);
        $userMock->method('getEmail')->willReturn($user->getEmail());
        $userMock->method('getRoles')->willReturn($user->getRoles());
        $security->method('getUser')->willReturn($userMock);

        // Handle auth
        $client->loginUser($userMock);

        $client->getContainer()->set(TagObjectHandler::class, $this->tagObjectHandlerMock);

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
        $this->tagObjectHandlerMock->expects(self::once())
            ->method('loadList')
            ->willReturn($this->tagObjectsMock);

        // Request the endpoint
        $this->client->request('GET', '/tag');

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(array_map('get_object_vars', $this->tagObjectsMock), array_map('get_object_vars', $content));
    }

    public function testTag(): void
    {

        // Pick random tag from the mock
        $tag = $this->tagObjectsMock[array_rand($this->tagObjectsMock)];

        // Handle service response
        $this->tagObjectHandlerMock->expects(self::once())
            ->method('loadOne')
            ->with($tag->id)
            ->willReturn($tag);

        // Request the endpoint
        $this->client->request('GET', '/tag/' . $tag->id);

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(get_object_vars($tag), get_object_vars($content));
    }

    public function testCreate(): void
    {
        $this->tagObjectHandlerMock->expects(self::once())
            ->method('createOne')
            ->willReturn($this->tagObjectsMock[0]);

        // Request the endpoint
        $this->client->request('POST', '/auth/tag', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'name' => $this->tagObjectsMock[0]->name
        ]));

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(get_object_vars($this->tagObjectsMock[0]), get_object_vars($content));
    }

    public function testUpdate(): void
    {
        $tag = $this->tagObjectsMock[0];
        $updatedObject = new TagObject($tag->id, $this->mockGenerator->generateName());

        $tagRepositoryMock = $this->createMock(TagRepository::class);
        $tagRepositoryMock->expects(self::once())
            ->method('countTagByUserId')
            ->willReturn(0);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::once())
            ->method('getRepository')
            ->willReturn($tagRepositoryMock);

        $this->client->getContainer()->set(EntityManagerInterface::class, $entityManagerMock);

        $this->tagObjectHandlerMock->expects(self::once())
            ->method('updateOne')
            ->willReturn($updatedObject);

        // Request the endpoint
        $this->client->request('PUT', '/auth/tag/' . $tag->id, server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
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
        $tag = $this->tagObjectsMock[0];

        $tagRepositoryMock = $this->createMock(TagRepository::class);
        $tagRepositoryMock->expects(self::once())
            ->method('countTagByUserId')
            ->willReturn(0);

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::once())
            ->method('getRepository')
            ->willReturn($tagRepositoryMock);

        $this->client->getContainer()->set(EntityManagerInterface::class, $entityManagerMock);

        $this->tagObjectHandlerMock->expects(self::once())
            ->method('deleteOne');

        // Request the endpoint
        $this->client->request('DELETE', '/auth/tag/' . $tag->id);

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(['OK'], $content);
    }
}
