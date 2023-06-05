<?php

namespace App\Entity;

use App\Repository\PictureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PictureRepository::class)]
class Picture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'pictures', cascade:["persist"])]
    private ?Album $album = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(length: 255)]
    private ?string $thumbnail = null;

    #[ORM\ManyToOne(inversedBy: 'pictures')]
    private ?Products $product = null;

    // #[ORM\OneToMany(mappedBy: 'picture', targetEntity: OrderLine::class)]
    // private Collection $orderLines;

    // public function __construct()
    // {
    //     $this->orderLines = new ArrayCollection();
    // }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

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

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): self
    {
        $this->album = $album;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    // /**
    //  * @return Collection<int, OrderLine>
    //  */
    // public function getOrderLines(): Collection
    // {
    //     return $this->orderLines;
    // }

    // public function addOrderLine(OrderLine $orderLine): self
    // {
    //     if (!$this->orderLines->contains($orderLine)) {
    //         $this->orderLines->add($orderLine);
    //         $orderLine->setPicture($this);
    //     }

    //     return $this;
    // }

    // public function removeOrderLine(OrderLine $orderLine): self
    // {
    //     if ($this->orderLines->removeElement($orderLine)) {
    //         // set the owning side to null (unless already changed)
    //         if ($orderLine->getPicture() === $this) {
    //             $orderLine->setPicture(null);
    //         }
    //     }

    //     return $this;
    // }

    public function getProduct(): ?Products
    {
        return $this->product;
    }

    public function setProduct(?Products $product): self
    {
        $this->product = $product;

        return $this;
    }
}
