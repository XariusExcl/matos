<?php

namespace App\Entity;

use App\Repository\EquipmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: EquipmentRepository::class)]
#[Vich\Uploadable]
class Equipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'equipments', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;
    
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $serial_number = null;

    #[ORM\Column(options: ['default' => true])]
    private ?bool $loanable = true;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'equipment')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    #[ORM\ManyToMany(targetEntity: Loan::class, mappedBy: 'equipmentLoaned')]
    private Collection $loans;

    #[ORM\ManyToOne(inversedBy: 'equipment')]
    private ?EquipmentCategory $category = null;

    #[ORM\ManyToOne(inversedBy: 'equipment')]
    private ?EquipmentSubCategory $subCategory = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?bool $showInTable = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $numbered = null;

    public function __construct()
    {
        $this->loans = new ArrayCollection();
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
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serial_number;
    }

    public function setSerialNumber(?string $serial_number): static
    {
        $this->serial_number = $serial_number;

        return $this;
    }

    public function isLoanable(): ?bool
    {
        return $this->loanable;
    }

    public function setLoanable(bool $isLoanable): static
    {
        $this->loanable = $isLoanable;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, Loan>
     */
    public function getLoans(): Collection
    {
        return $this->loans;
    }

    public function addLoan(Loan $loan): static
    {
        if (!$this->loans->contains($loan)) {
            $this->loans->add($loan);
            $loan->addEquipmentLoaned($this);
        }

        return $this;
    }

    public function removeLoan(Loan $loan): static
    {
        if ($this->loans->removeElement($loan)) {
            $loan->removeEquipmentLoaned($this);
        }

        return $this;
    }

    public function getCategory(): ?EquipmentCategory
    {
        return $this->category;
    }

    public function setCategory(?EquipmentCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getSubCategory(): ?EquipmentSubCategory
    {
        return $this->subCategory;
    }

    public function setSubCategory(?EquipmentSubCategory $subCategory): static
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function isShowInTable(): ?bool
    {
        return $this->showInTable;
    }

    public function setShowInTable(bool $showInTable): static
    {
        $this->showInTable = $showInTable;

        return $this;
    }

    public function isNumbered(): bool
    {
        return $this->numbered;
    }

    public function setNumbered(bool $numbered): static
    {
        $this->numbered = $numbered;

        return $this;
    }
}
