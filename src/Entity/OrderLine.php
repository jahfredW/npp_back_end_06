<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrderLineRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderLineRepository::class)]
class OrderLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['getOrders', 'getOrderLines'])]
    private ?int $quantity = null;

    #[ORM\ManyToOne(inversedBy: 'orderLines')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?Order $ordered = null;

    #[ORM\Column]
    #[Groups(['getOrders', 'getOrderLines'])]
    private ?float $price = null;

    #[ORM\Column]
    #[Groups(['getOrders'])]
    private ?float $total = null;

    // #[ORM\ManyToOne(inversedBy: 'orderLines')]
    // #[ORM\JoinColumn(nullable: false)]
    #[ORM\Column]
    #[Groups(['getOrders', 'getOrderLines'])]
    private ?int $pictureId = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getOrdered(): ?Order
    {
        return $this->ordered;
    }

    public function setOrdered(?Order $ordered): self
    {
        $this->ordered = $ordered;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getPictureId(): ?int
    {
        return $this->pictureId;
    }

    public function setPictureId(?int $pictureId): self
    {
        $this->pictureId = $pictureId;

        return $this;
    }


}