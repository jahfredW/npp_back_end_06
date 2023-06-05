<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AlbumRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AlbumRepository::class)]
class Album
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAlbums', 'getAlbumInfo'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getAlbums', 'getAlbumInfo'])]
    #[Assert\Unique(message: "Il semblerait que le tittre existe déja")]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['getAlbums'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getAlbums'])]
    private ?string $coverPicture = null;

    #[ORM\Column]
    #[Groups(['getAlbums', 'getAlbumInfo'])]
    private ?bool $morning = null;

    #[ORM\Column]
    #[Groups(['getAlbums', 'getAlbumInfo'])]
    private ?bool $isActive = null;

    #[ORM\OneToMany(mappedBy: 'album', targetEntity: Picture::class, cascade:["remove", "persist"])]
    #[Groups(['getAlbums'])]
    private Collection $pictures;

    #[ORM\ManyToOne(inversedBy: 'album')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getAlbums', 'getAlbumInfo'])]
    private ?Category $category = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['getAlbums', 'getAlbumInfo'])]
    private ?\DateTimeImmutable $expireAt = null;

    #[ORM\ManyToOne(inversedBy: 'albums')]
    private ?Products $product = null;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
    }

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCoverPicture(): ?string
    {
        return $this->coverPicture;
    }

    public function setCoverPicture(?string $coverPicture): self
    {
        $this->coverPicture = $coverPicture;

        return $this;
    }

    public function isMorning(): ?bool
    {
        return $this->morning;
    }

    public function setMorning(bool $morning): self
    {
        $this->morning = $morning;

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

    /**
     * @return Collection<int, Picture>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures->add($picture);
            $picture->setAlbum($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getAlbum() === $this) {
                $picture->setAlbum(null);
            }
        }

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

    public function getExpireAt(): ?\DateTimeImmutable
    {
        return $this->expireAt;
    }

    public function setExpireAt(?\DateTimeImmutable $expireAt): self
    {
        $this->expireAt = $expireAt;

        return $this;
    }

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
