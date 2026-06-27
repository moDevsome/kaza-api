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
    private ?string $TranslationKey = null;

    #[ORM\Column(length: 2500)]
    private ?string $TranslationValue = null;

    #[ORM\Column(length: 6)]
    private ?string $Tag = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTranslationKey(): ?string
    {
        return $this->TranslationKey;
    }

    public function setTranslationKey(string $TranslationKey): static
    {
        $this->TranslationKey = $TranslationKey;

        return $this;
    }

    public function getTranslationValue(): ?string
    {
        return $this->TranslationValue;
    }

    public function setTranslationValue(string $TranslationValue): static
    {
        $this->TranslationValue = $TranslationValue;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->Tag;
    }

    public function setTag(string $Tag): static
    {
        $this->Tag = $Tag;

        return $this;
    }
}
