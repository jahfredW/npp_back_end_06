<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DiscountRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DiscountRepository::class)]
class Discount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getDiscount'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getDiscount'])]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups(['getDiscount'])]
    private ?float $rate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'discount', targetEntity: Order::class)]
    private Collection $ordered;

    #[ORM\Column]
    #[Groups(['getDiscount'])]
    private ?int $articles = null;

    public function __construct()
    {
        $this->ordered = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(float $rate): self
    {
        $this->rate = $rate;

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

    /**
     * @return Collection<int, Order>
     */
    public function getOrdered(): Collection
    {
        return $this->ordered;
    }

    public function addOrdered(Order $ordered): self
    {
        if (!$this->ordered->contains($ordered)) {
            $this->ordered->add($ordered);
            $ordered->setDiscount($this);
        }

        return $this;
    }

    public function removeOrdered(Order $ordered): self
    {
        if ($this->ordered->removeElement($ordered)) {
            // set the owning side to null (unless already changed)
            if ($ordered->getDiscount() === $this) {
                $ordered->setDiscount(null);
            }
        }

        return $this;
    }

    public function getArticles(): ?int
    {
        return $this->articles;
    }

    public function setArticles(int $articles): self
    {
        $this->articles = $articles;

        return $this;
    }
}
