<?php

namespace App\Entity;

use App\Repository\LoanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

enum LoanStatus: int {
    case PENDING = 0;
    case ACCEPTED = 1;
    case REFUSED = 2;
    case RETURNED = 3;
    case CANCELLED_BY_LOANER = 4;
}

#[ORM\Entity(repositoryClass: LoanRepository::class)]
class Loan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $departure_date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $return_date = null;

    #[ORM\ManyToMany(targetEntity: Equipment::class, inversedBy: 'loans', fetch:'EAGER')]
    private Collection $equipmentLoaned;

    #[ORM\Column]
    private ?int $status = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'loans')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $loaner = null;

    public function __construct()
    {
        $this->equipmentLoaned = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepartureDate(): ?\DateTimeInterface
    {
        return $this->departure_date;
    }

    public function setDepartureDate(\DateTimeInterface $departure_date): static
    {
        $this->departure_date = $departure_date;

        return $this;
    }

    public function getReturnDate(): ?\DateTimeInterface
    {
        return $this->return_date;
    }

    public function setReturnDate(\DateTimeInterface $return_date): static
    {
        $this->return_date = $return_date;

        return $this;
    }

    /**
     * @return Collection<int, Equipment>
     */
    public function getEquipmentLoaned(): Collection
    {
        return $this->equipmentLoaned;
    }

    public function addEquipmentLoaned(Equipment $equipmentLoaned): static
    {
        if (!$this->equipmentLoaned->contains($equipmentLoaned)) {
            $this->equipmentLoaned->add($equipmentLoaned);
        }

        return $this;
    }

    public function removeEquipmentLoaned(Equipment $equipmentLoaned): static
    {
        $this->equipmentLoaned->removeElement($equipmentLoaned);

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

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

    public function getLoaner(): ?User
    {
        return $this->loaner;
    }

    public function setLoaner(?User $loaner): static
    {
        $this->loaner = $loaner;

        return $this;
    }
}
