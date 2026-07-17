<?php

namespace Api\Service\Business;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Api\Entity\Tag;
use Api\Exception\BusinessException;
use Api\Service\Business\ContentTranslationStore;
use Api\Interface\ObjectHandlerInterface;
use Api\Object\Business\CreateTagRequestObject;
use Api\Object\Business\TagObject;
use Api\Object\Business\PatchRequestObject;

final class TagObjectHandler implements ObjectHandlerInterface
{
    private function convertToTagObject(Tag $input): TagObject
    {
        // TODO:handle translation
        return new TagObject(
            $input->getId(),
            $this->contentTranslationStore->getValue('tag.name', 0, $input->getName()),
        );
    }

    public function loadList(array $criterias = []): array
    {
        return array_map(
            fn($tagEntity) => $this->convertToTagObject($tagEntity),
            $this->entityManager->getRepository(Tag::class)->findBy($criterias)
        );
    }

    public function loadOne(string $id): TagObject|null
    {

        $tag = $this->entityManager->getRepository(Tag::class)->findOneById($id);
        if (!$tag)
            return null;

        return $this->convertToTagObject($tag);
    }

    /**
     * Create one tag then return the object
     * @param CreateTagRequestObject $createRequest
     * @throws BusinessException
     * @return TagObject
     */
    public function createOne(CreateTagRequestObject $createRequest): TagObject
    {

        //TODO:check if the name already exist

        try {

            $newEntity = new Tag();
            $newEntity->setName($createRequest->name);
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();

            if ($newEntity === null)
                throw new BusinessException(500, 'Error occured while creating tag');

            return $this->convertToTagObject($newEntity);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Update a tag object
     *
     * @param string $id
     * @param CreateTagRequestObject $requestObject
     * @throws BusinessException
     * @return TagObject
     */
    public function updateOne(string $id, CreateTagRequestObject $requestObject): TagObject
    {

        try {

            $tagEntity = $this->entityManager->getRepository(Tag::class)->findOneById($id);
            if ($tagEntity === null)
                throw new BusinessException(404, 'Tag not found');

            $translateProperty = null;

            $tagEntity->setName($requestObject->name);
            $this->entityManager->persist($tagEntity);
            $this->entityManager->flush();

            if ($translateProperty !== null and $requestObject->autoTranslate === true) {
                //TODO:handle translation
            }

            return $this->convertToTagObject($tagEntity);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete a tag object
     *
     * @param string $id
     * @throws BusinessException
     * @return void
     */
    public function deleteOne(string $id): void
    {

        $tagEntity = $this->entityManager->getRepository(Tag::class)->findOneById($id);
        if ($tagEntity !== null) {
            $this->entityManager->remove($tagEntity);
            $this->entityManager->flush();

            //TODO:delete translation
        }
    }

    public function patchOne(string $id, string $property, PatchRequestObject $requestObject): TagObject
    {
        return new TagObject('', '');
    }

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentTranslationStore $contentTranslationStore,
    ) {}
}
