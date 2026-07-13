<?php

namespace Api\Controller\Business;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Api\Service\Technical\ResponseBuffer;
use Api\Object\Business\ContentTranslationRequestObject;
use Api\Object\Business\ContentTranslationRequestValueObject;
use Api\Service\Business\ContentTranslationStore;
use Api\Service\Business\LodgingObjectHandler;
use Api\Enum\Business\ContentTranslationType;
use Api\Entity\Tag;
use Api\Entity\Equipment;
use Api\Entity\Location;
use Api\Entity\LocationArea;
use Api\Exception\BusinessException;

final class ContentTranslationController extends AbstractController
{
    /**
     * Add or Update content translation
     * @throws BusinessException
     * @return JsonResponse
     */
    #[
        Route(
            '/auth/translate',
            name: 'api_translate',
            methods: ['PATCH'],
        )
    ]
    public function translate(): JsonResponse
    {

        $userId = $this->getUser()->getId();

        $translateRequestObject = $this->serializer->deserialize(
            $this->requestStack->getCurrentRequest()->getContent(),
            ContentTranslationRequestObject::class,
            'json'
        );

        $values = $this->serializer->deserialize(
            json_encode($translateRequestObject->values),
            ContentTranslationRequestValueObject::class . '[]',
            'json'
        );

        // Specific handling for Lodging
        if ($translateRequestObject->type === ContentTranslationType::Lodging) {
            $this->lodgingObjectHandler->checkHost($userId, lodgingId: $translateRequestObject->contentId);
        }

        // Specific handling for Tag
        if ($translateRequestObject->type === ContentTranslationType::Tag) {

            // Check if the tag is used by a lodging which not belong to the current user
            try {
                $count = $this->entityManager->getRepository(Tag::class)->countTagByUserId($translateRequestObject->contentId, $userId, true);
            } catch (Exception $e) {
                throw new BusinessException(500, $e->getMessage());
            }

            if ($count !== 0)
                throw new BusinessException(400, 'This tag could not be translated by the current user because it is associated with lodging which not belong to the current user');
        }

        // Specific handling for Equipment
        if ($translateRequestObject->type === ContentTranslationType::Equipment) {

            // Check if the equipment is used by a lodging which not belong to the current user
            try {
                $count = $this->entityManager->getRepository(Equipment::class)->countEquipmentByUserId($translateRequestObject->contentId, $userId, true);
            } catch (Exception $e) {
                throw new BusinessException(500, $e->getMessage());
            }

            if ($count !== 0)
                throw new BusinessException(400, 'This equipment could not be translated by the current user because it is also associated with lodging which not belong to the current user');
        }

        // Specific handling for Location
        if ($translateRequestObject->type === ContentTranslationType::Location) {

            // Check if the location is associated to a lodging which not belong to the current user
            try {
                $count = $this->entityManager->getRepository(Location::class)->countLocationByUserId($translateRequestObject->contentId, $userId, true);
            } catch (Exception $e) {
                throw new BusinessException(500, $e->getMessage());
            }

            if ($count !== 0)
                throw new BusinessException(400, 'This location could not be translated by the current user because it is also associated with lodging which not belong to the current user');
        }

        // Specific handling for LocationArea
        if ($translateRequestObject->type === ContentTranslationType::LocationArea) {

            // Check if the location area is associated to a lodging which not belong to the current user
            try {
                $count = $this->entityManager->getRepository(LocationArea::class)->countLocationAreaByUserId($translateRequestObject->contentId, $userId, true);
            } catch (Exception $e) {
                throw new BusinessException(500, $e->getMessage());
            }

            if ($count !== 0)
                throw new BusinessException(400, 'This location area could not be translated by the current user because it is also associated with lodging which not belong to the current user');
        }

        $this->store->setValues(
            $translateRequestObject->contentId,
            $translateRequestObject->type,
            $translateRequestObject->property,
            $values
        );

        return $this->responseBuffer->buildResponse(['OK']);
    }

    public function __construct(
        private readonly ResponseBuffer $responseBuffer,
        private readonly ContentTranslationStore $store,
        private readonly LodgingObjectHandler $lodgingObjectHandler,
        private readonly EntityManagerInterface $entityManager,
        protected readonly RequestStack $requestStack,
        private readonly SerializerInterface $serializer
    ) {}
}
