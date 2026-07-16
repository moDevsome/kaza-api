<?php

namespace Api\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Api\Repository\EquipmentRepository;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
class Equipment
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid', unique: true)]
    private Ulid $id;

    #[ORM\Column(length: 80)]
    private ?string $name = null;

    /**
     * @var Collection<int, Lodging>
     */
    #[ORM\ManyToMany(targetEntity: Lodging::class, inversedBy: 'equipment')]
    private Collection $lodging;

    public function __construct()
    {
        $this->id = new Ulid();

        $this->lodging = new ArrayCollection();
    }

    public function getId(): Ulid
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
