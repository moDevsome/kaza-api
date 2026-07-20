<?php

namespace Api\Service\Business;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Api\Entity\Tag;
use Api\Enum\Business\ContentTranslationTagProperty;
use Api\Enum\Business\ContentTranslationType;
use Api\Exception\BusinessException;
use Api\Service\Business\ContentTranslationStore;
use Api\Interface\ObjectHandlerInterface;
use Api\Object\Business\ContentTranslationRequestValueObject;
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
            $this->contentTranslationStore->getValue('tag.name', $input->getId(), $input->getName()),
        );
    }

    public function loadList(array $criterias = [], int $limitCount = 40, int $limitOffset = 0): array
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
    public function createOne(CreateTagRequestObject $createRequest,  bool $applyTranslation): TagObject
    {

        //TODO:check if the name already exist

        try {

            $newEntity = new Tag();
            $newEntity->setName($createRequest->name);
            $this->entityManager->persist($newEntity);
            $this->entityManager->flush();

            if ($newEntity === null)
                throw new BusinessException(500, 'Error occured while creating tag');

            if ($applyTranslation === true) {

                $this->contentTranslationStore->setValues(
                    $newEntity->getId(),
                    ContentTranslationType::Tag,
                    ContentTranslationTagProperty::Name,
                    [
                        new ContentTranslationRequestValueObject($this->contentTranslationStore->getCurrentTag(), $createRequest->name)
                    ]
                );
            }

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
     * @param bool $applyTranslation
     * @throws BusinessException
     * @return TagObject
     */
    public function updateOne(string $id, CreateTagRequestObject $requestObject, bool $applyTranslation): TagObject
    {

        try {

            $tagEntity = $this->entityManager->getRepository(Tag::class)->findOneById($id);
            if ($tagEntity === null)
                throw new BusinessException(404, 'Tag not found');

            $tagEntity->setName($requestObject->name);
            $this->entityManager->persist($tagEntity);
            $this->entityManager->flush();

            if ($applyTranslation === true) {

                $this->contentTranslationStore->setValues(
                    $tagEntity->getId(),
                    ContentTranslationType::Tag,
                    ContentTranslationTagProperty::Name,
                    [
                        new ContentTranslationRequestValueObject($this->contentTranslationStore->getCurrentTag(), $requestObject->name)
                    ]
                );
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

            $this->contentTranslationStore->deleteValues(
                $tagEntity->getId(),
                ContentTranslationType::Tag,
                ContentTranslationTagProperty::Name
            );
        }
    }

    public function patchOne(string $id, string $property, PatchRequestObject $requestObject, bool $applyTranslation): TagObject
    {
        return new TagObject('', '');
    }

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ContentTranslationStore $contentTranslationStore,
    ) {}
}
