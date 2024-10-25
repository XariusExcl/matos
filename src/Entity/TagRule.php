<?php

namespace App\Entity;

use App\Repository\TagRuleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRuleRepository::class)]
class TagRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $arg1 = null;

    #[ORM\Column(length: 255)]
    private ?string $arg2 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArg1(): ?string
    {
        return $this->arg1;
    }

    public function setArg1(string $arg1): static
    {
        $this->arg1 = $arg1;

        return $this;
    }

    public function getArg2(): ?string
    {
        return $this->arg2;
    }

    public function setArg2(string $arg2): static
    {
        $this->arg2 = $arg2;

        return $this;
    }
}
