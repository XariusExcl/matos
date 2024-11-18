<?php

namespace App\Entity;

use App\Repository\UnavailableDaysRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnavailableDaysRepository::class)]
class UnavailableDays
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEnd = null;

    #[ORM\Column]
    private ?bool $preventsLoans = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    #[ORM\ManyToOne]
    private ?EquipmentCategory $restrictedCategory = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): static
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): static
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function isPreventsLoans(): ?bool
    {
        return $this->preventsLoans;
    }

    public function setPreventsLoans(bool $preventsLoans): static
    {
        $this->preventsLoans = $preventsLoans;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getRestrictedCategory(): ?EquipmentCategory
    {
        return $this->restrictedCategory;
    }

    public function setRestrictedCategory(?EquipmentCategory $restrictedCategory): static
    {
        $this->restrictedCategory = $restrictedCategory;

        return $this;
    }
}
