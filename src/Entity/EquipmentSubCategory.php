<?php

namespace App\Entity;

use App\Repository\EquipmentSubCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

enum EquipmentSubCategoryType: int
{
    case LIST = 0;
    case CARD = 1;
}

#[ORM\Entity(repositoryClass: EquipmentSubCategoryRepository::class)]
class EquipmentSubCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'equipmentSubCategory', targetEntity: Equipment::class)]
    private Collection $equipment;

    #[ORM\Column]
    private ?int $formDisplayType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->equipment = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
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
     * @return Collection<int, Equipment>
     */
    public function getEquipment(): Collection
    {
        return $this->equipment;
    }

    public function addEquipment(Equipment $equipment): static
    {
        if (!$this->equipment->contains($equipment)) {
            $this->equipment->add($equipment);
            $equipment->setSubCategory($this);
        }

        return $this;
    }

    public function removeEquipment(Equipment $equipment): static
    {
        if ($this->equipment->removeElement($equipment)) {
            // set the owning side to null (unless already changed)
            if ($equipment->getSubCategory() === $this) {
                $equipment->setSubCategory(null);
            }
        }

        return $this;
    }

    public function getFormDisplayType(): ?int
    {
        return $this->formDisplayType;
    }

    public function setFormDisplayType(int $formDisplayType): static
    {
        $this->formDisplayType = $formDisplayType;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}
