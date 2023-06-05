<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\InvoiceRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getInvoice'])]
    private ?int $id = null;

    // #[ORM\OneToOne(inversedBy: 'invoice', cascade: ['persist', 'remove'])]
    #[ORM\OneToOne(inversedBy: 'invoice')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['getInvoice'])]
    private ?Order $ordered = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getInvoice'])]
    private ?string $number = null;

    #[ORM\Column]
    #[Groups(['getInvoice'])]
    private ?\DateTimeImmutable $CreatedAt = null;

    // #[ORM\ManyToOne(inversedBy: 'invoices', cascade: ['persist', 'remove'])]
    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[Groups(['getInvoice'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Address $address = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getInvoice'])]
    private ?float $total = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrdered(): ?Order
    {
        return $this->ordered;
    }

    public function setOrdered(Order $ordered): self
    {
        $this->ordered = $ordered;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeImmutable $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): self
    {
        $this->total = $total;

        return $this;
    }
}
