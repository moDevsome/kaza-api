<?php

namespace Api\Entity;

use Api\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\Column(type: 'ulid', unique: true)]
    private Ulid $id;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?LocationArea $area = null;

    /**
     * @var Collection<int, Lodging>
     */
    #[ORM\OneToMany(targetEntity: Lodging::class, mappedBy: 'location')]
    private Collection $lodgings;

    public function __construct()
    {
        $this->id = new Ulid();

        $this->lodgings = new ArrayCollection();
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

    public function getArea(): ?LocationArea
    {
        return $this->area;
    }

    public function setArea(?LocationArea $area): static
    {
        $this->area = $area;

        return $this;
    }

    /**
     * @return Collection<int, Lodging>
     */
    public function getLodgings(): Collection
    {
        return $this->lodgings;
    }
}
