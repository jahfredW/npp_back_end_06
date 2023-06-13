<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // #[ORM\Column(nullable: true)]
    // private ?int $ItemId = null;

    // #[ORM\Column(nullable: true)]
    // private ?int $ClientId = null;

    #[ORM\Column(nullable: true)]
    private ?string $CookieId = null;

    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartLine::class)]
    private Collection $cartLines;

    #[ORM\OneToOne(mappedBy: 'cart', cascade: ['persist', 'remove'])]
    private ?Order $ordered = null;

    public function __construct()
    {
        $this->cartLines = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    // public function getItemId(): ?int
    // {
    //     return $this->ItemId;
    // }

    // public function setItemId(?int $ItemId): self
    // {
    //     $this->ItemId = $ItemId;

    //     return $this;
    // }

    // public function getClientId(): ?int
    // {
    //     return $this->ClientId;
    // }

    // public function setClientId(?int $ClientId): self
    // {
    //     $this->ClientId = $ClientId;

    //     return $this;
    // }

    public function getCookieId(): ?string
    {
        return $this->CookieId;
    }

    public function setCookieId(?string $CookieId): self
    {
        $this->CookieId = $CookieId;

        return $this;
    }

    /**
     * @return Collection<int, CartLine>
     */
    public function getCartLines(): Collection
    {
        return $this->cartLines;
    }

    public function addCartLine(CartLine $cartLine): self
    {
        if (!$this->cartLines->contains($cartLine)) {
            $this->cartLines->add($cartLine);
            $cartLine->setCart($this);
        }

        return $this;
    }

    public function removeCartLine(CartLine $cartLine): self
    {
        if ($this->cartLines->removeElement($cartLine)) {
            // set the owning side to null (unless already changed)
            if ($cartLine->getCart() === $this) {
                $cartLine->setCart(null);
            }
        }

        return $this;
    }

    public function getOrdered(): ?Order
    {
        return $this->ordered;
    }

    public function setOrdered(?Order $ordered): self
    {
        // unset the owning side of the relation if necessary
        if ($ordered === null && $this->ordered !== null) {
            $this->ordered->setCart(null);
        }

        // set the owning side of the relation if necessary
        if ($ordered !== null && $ordered->getCart() !== $this) {
            $ordered->setCart($this);
        }

        $this->ordered = $ordered;

        return $this;
    }
}
