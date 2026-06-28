<?php

namespace Api\Entity;

use Api\Repository\EquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80)]
    private ?string $name = null;

    /**
     * @var Collection<int, Lodging>
     */
    #[ORM\ManyToMany(targetEntity: Lodging::class, inversedBy: 'equipment')]
    private Collection $lodging;

    public function __construct()
    {
        $this->lodging = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Lodging>
     */
    public function getLodgings(): Collection
    {
        return $this->lodging;
    }

    public function addLodging(Lodging $lodging): static
    {
        if (!$this->lodging->contains($lodging)) {
            $this->lodging->add($lodging);
        }

        return $this;
    }

    public function removeLodging(Lodging $lodging): static
    {
        $this->lodging->removeElement($lodging);

        return $this;
    }
}
