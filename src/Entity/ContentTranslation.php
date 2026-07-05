<?php

namespace Api\Entity;

use Api\Repository\ContentTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentTranslationRepository::class)]
class ContentTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private ?string $translationKey = null;

    #[ORM\Column(length: 2500)]
    private ?string $translationValue = null;

    #[ORM\Column(length: 6)]
    private ?string $tag = null;

    #[ORM\Column]
    private ?int $contentId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTranslationKey(): ?string
    {
        return $this->translationKey;
    }

    public function setTranslationKey(string $translationKey): static
    {
        $this->translationKey = $translationKey;

        return $this;
    }

    public function getTranslationValue(): ?string
    {
        return $this->translationValue;
    }

    public function setTranslationValue(string $TranslationValue): static
    {
        $this->translationValue = $TranslationValue;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getContentId(): ?int
    {
        return $this->contentId;
    }

    public function setContentId(int $content_id): static
    {
        $this->contentId = $content_id;

        return $this;
    }
}
