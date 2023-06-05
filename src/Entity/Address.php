<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AddressRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getInvoice'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'addresses')]
    // #[Groups(['getUsers'])]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers', 'getInvoice', 'getAdress'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers', 'getInvoice', 'getAdress'])]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers','getInvoice', 'getAdress'])]
    private ?string $lastname = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getUsers', 'getInvoice', 'getAdress'])]
    private ?string $company = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers','getInvoice', 'getAdress'])]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers', 'getInvoice', 'getAdress'])]
    private ?string $postal = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers', 'getInvoice', 'getAdress'])]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers', 'getInvoice', 'getAdress'])]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getUsers', 'getInvoice', 'getAdress'])]
    private ?string $phone = null;
    
    #[ORM\OneToMany(mappedBy: 'address', targetEntity: Invoice::class)]
    private ?Collection $invoices = null;

    #[ORM\Column]
    private ?bool $IsSelected = null;

    #[ORM\OneToMany(mappedBy: 'address', targetEntity: Order::class)]
    private ?Collection $orders = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPostal(): ?string
    {
        return $this->postal;
    }

    public function setPostal(string $postal): self
    {
        $this->postal = $postal;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices->add($invoice);
            $invoice->setAddress($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getAddress() === $this) {
                $invoice->setAddress(null);
            }
        }

        return $this;
    }

    public function isIsSelected(): ?bool
    {
        return $this->IsSelected;
    }

    public function setIsSelected(bool $IsSelected): self
    {
        $this->IsSelected = $IsSelected;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setAddress($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getAddress() === $this) {
                $order->setAddress(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

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
}
