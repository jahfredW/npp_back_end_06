<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getOrders', 'getInvoice'])]
    
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['getOrders', 'getInvoice'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getOrders', 'getInvoice'])]
    private ?string $status = null;

    // #[ORM\ManyToOne(inversedBy: 'orders')]
    // private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'ordered', targetEntity: OrderLine::class)]
    #[Groups(['getOrders'])]
    private Collection $orderLines;

    #[ORM\Column]
    #[Groups(['getOrders'])]
    private ?bool $isActive = null;

    #[ORM\ManyToOne(inversedBy: 'order_id')]
    #[Groups(['getInvoice'])]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getOrders'])]
    private ?string $stripe_id = null;

    // #[ORM\OneToOne(mappedBy: 'ordered', cascade: ['persist', 'remove'])]
    #[ORM\OneToOne(mappedBy: 'ordered')]
    #[Groups(['getOrders'])]
    private ?Invoice $invoice = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    // #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Address $address = null;

    #[ORM\ManyToOne(inversedBy: 'ordered')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Discount $discount = null;

    #[ORM\OneToOne(inversedBy: 'ordered', cascade: ['persist', 'remove'])]
    private ?Cart $cart = null;

    public function __construct()
    {
        $this->orderLines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    // public function getUser(): ?User
    // {
    //     return $this->user;
    // }

    // public function setUser(?User $user): self
    // {
    //     $this->userId = $user;

    //     return $this;
    // }

    /**
     * @return Collection<int, OrderLine>
     */
    public function getOrderLines(): Collection
    {
        return $this->orderLines;
    }

    public function addOrderLine(OrderLine $orderLine): self
    {
        if (!$this->orderLines->contains($orderLine)) {
            $this->orderLines->add($orderLine);
            $orderLine->setOrdered($this);
        }

        return $this;
    }

    public function removeOrderLine(OrderLine $orderLine): self
    {
        if ($this->orderLines->removeElement($orderLine)) {
            // set the owning side to null (unless already changed)
            if ($orderLine->getOrdered() === $this) {
                $orderLine->setOrdered(null);
            }
        }

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
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

    public function getStripeId(): ?string
    {
        return $this->stripe_id;
    }

    public function setStripeId(string $stripe_id): self
    {
        $this->stripe_id = $stripe_id;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(Invoice $invoice): self
    {
        // set the owning side of the relation if necessary
        if ($invoice->getOrdered() !== $this) {
            $invoice->setOrdered($this);
        }

        $this->invoice = $invoice;

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

    public function getDiscount(): ?Discount
    {
        return $this->discount;
    }

    public function setDiscount(?Discount $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getCart(): ?Cart
    {
        return $this->cart;
    }

    public function setCart(?Cart $cart): self
    {
        $this->cart = $cart;

        return $this;
    }
}
