<?php

namespace Api\Controller\Business;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Exception;
use Api\Entity\Tag;
use Api\Exception\BusinessException;
use Api\Object\Business\CreateTagRequestObject;
use Api\Service\Technical\ResponseBuffer;
use Api\Service\Business\TagObjectHandler;
use Doctrine\ORM\EntityManagerInterface;

final class TagController extends AbstractController
{

    public function __construct(
        private readonly ResponseBuffer $responseBuffer,
        private readonly TagObjectHandler $handler,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $entityManager,
        protected readonly RequestStack $requestStack,
    ) {}

    /**
     * Return a list tag
     */
    #[Route('/tag', name: 'api_tag_list', methods: ['GET'])]
    public function index(): JsonResponse
    {

        return $this->responseBuffer->buildResponse($this->handler->loadList());
    }

    /**
     * Return one tag
     * @param string $id
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/tag/{id}', name: 'api_tag')]
    public function tag(string $id): JsonResponse
    {
        $errorMessage = 'Tag (' . $id . ') not found';

        $tag = $this->handler->loadOne($id);

        if ($tag === null)
            throw new BusinessException(404,  $errorMessage);

        return $this->responseBuffer->buildResponse($tag);
    }

    /**
     * "Create Tag" endpoint for authenticated user
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/auth/tag', name: 'api_create_tag', methods: ['POST'])]
    public function create(): JsonResponse
    {

        $createTagRequestObject = $this->serializer->deserialize(
            $this->requestStack->getCurrentRequest()->getContent(),
            CreateTagRequestObject::class,
            'json'
        );

        $createdTag = $this->handler->createOne($createTagRequestObject);

        $this->responseBuffer->addHeader('Location', '/tag/' . $createdTag->id);
        $this->responseBuffer->setStatusCode(201);
        return $this->responseBuffer->buildResponse($createdTag);
    }

    /**
     * "Update Tag" endpoint for authenticated user
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/auth/tag/{id}', name: 'api_update_tag', methods: ['PUT'])]
    public function update(string $id): JsonResponse
    {

        $createTagRequestObject = $this->serializer->deserialize(
            $this->requestStack->getCurrentRequest()->getContent(),
            CreateTagRequestObject::class,
            'json'
        );

        // Check if the tag is used by a lodging which not belong to the current user
        try {
            $count = $this->entityManager->getRepository(Tag::class)->countTagByUserId($id, $this->getUser()->getId(), true);
        } catch (Exception $e) {
            throw new BusinessException(500, $e->getMessage());
        }

        if ($count !== 0)
            throw new BusinessException(400, 'This tag could not be updated by the current user because it is associated with lodging which not belong to the current user');

        $updatedTag = $this->handler->updateOne($id, $createTagRequestObject);
        return $this->responseBuffer->buildResponse($updatedTag);
    }

    /**
     * "Delete Tag" endpoint for authenticated user
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/auth/tag/{id}', name: 'api_delete_tag', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {

        // Check if the tag is used by a lodging which not belong to the current user
        try {
            $count = $this->entityManager->getRepository(Tag::class)->countTagByUserId($id, $this->getUser()->getId(), true);
        } catch (Exception $e) {
            throw new BusinessException(500, $e->getMessage());
        }

        if ($count !== 0)
            throw new BusinessException(400, 'This tag could not be deleted by the current user because it is associated with lodging which not belong to the current user');

        $this->handler->deleteOne($id);
        return $this->responseBuffer->buildResponse(['OK']);
    }
}
