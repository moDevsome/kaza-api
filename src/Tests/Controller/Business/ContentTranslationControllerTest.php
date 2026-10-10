<?php

namespace Api\Tests\Controller\Business;

use moDevsome\Palmo\Palmo;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Api\Entity\User;
use Api\Enum\Business\ContentTranslationLodgingProperty;
use Api\Object\Business\ContentTranslationRequestValueObject;
use Api\Enum\Business\ContentTranslationType;
use Api\Service\Business\ContentTranslationStore;
use Api\Service\Business\LodgingObjectHandler;


class ContentTranslationControllerTest extends WebTestCase
{

    private bool $setUpBeforeAllStatus = false;
    private int $userId;

    private Palmo $palmo;
    private KernelBrowser $client;
    private MockObject&ContentTranslationStore $contentTranslationStoreMock;
    private MockObject&LodgingObjectHandler $lodgingObjectHandlerMock;

    private function setUpBeforeAll(): void
    {

        $this->palmo = new Palmo();

        /**
         * Create client
         */
        $client = static::createClient();

        /**
         * Defines mocks values
         */
        $this->contentTranslationStoreMock = $this->createMock(ContentTranslationStore::class);
        $this->lodgingObjectHandlerMock = $this->createMock(LodgingObjectHandler::class);

        // Mock security service to get the logged user with "getUser"();
        $this->userId = $this->palmo->number->int(1, 9999);
        $userStub = $this->createStub(User::class);
        $userStub->method('getId')->willReturn($this->userId);
        $userStub->method('getEmail')->willReturn($this->palmo->contact->email());
        $userStub->method('getRoles')->willReturn(['REGISTERED']);
        $securityStub = $this->createStub(Security::class);
        $securityStub->method('getUser')->willReturn($userStub);

        // Handle auth
        $client->loginUser($userStub);

        $client->getContainer()->set(ContentTranslationStore::class, $this->contentTranslationStoreMock);
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

    public function testTranslate(): void
    {
        $contentIdMock = $this->palmo->string->alphanum(12);
        $typeMock = ContentTranslationType::Lodging;
        $propertyMock = ContentTranslationLodgingProperty::Title;
        $valuesMock = [
            new ContentTranslationRequestValueObject('fr-FR', $this->palmo->lorem->word(22, 2))
        ];

        $this->lodgingObjectHandlerMock->expects(self::once())
            ->method('checkHost')
            ->with($this->userId, null, $contentIdMock);

        $this->contentTranslationStoreMock->expects(self::once())
            ->method('setValues')
            ->with($contentIdMock, $typeMock, $propertyMock, $valuesMock);

        // Request the endpoint
        $this->client->request('PATCH', '/auth/translate', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'contentId' => $contentIdMock,
            'type' => $typeMock,
            'property' => $propertyMock,
            'values' => $valuesMock
        ]));

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame(['OK'], $content);
    }

    public function testAllowedLangTags(): void
    {
        //return $this->responseBuffer->buildResponse($this->store->getAllowedLangTags());
        $tagsMock = ['fr-FR', 'en-GB', 'es-ES'];

        $this->contentTranslationStoreMock->expects(self::once())
            ->method('getAllowedLangTags')
            ->willReturn($tagsMock);

        // Request the endpoint
        $this->client->request('GET', '/allowed-lang-tags', server: ['CONTENT_TYPE' => 'application/json']);

        // Validate a successful response and some content
        $responseContent = json_decode($this->client->getResponse()->getContent());
        $content = $responseContent->content;

        $this->assertResponseFormatSame('json');
        $this->assertResponseIsSuccessful();
        $this->assertNotNull($content);
        $this->assertSame($tagsMock, $content);
    }
}
