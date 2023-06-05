<?php

namespace App\Entity;

use App\Entity\Order;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Email doit être unique')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // public static function loadValidatorMetadata(ClassMetadata $metadata)
    // {
    //     $metadata->addPropertyConstraint('email', new Assert\Unique());
    // }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getUsers'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['getUsers', 'getInvoice'])]
    #[Assert\Email(message : "Email non valide")]
    #[Assert\NotBlank(message : "Vous devez indiquer un email")]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['getUsers'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getUsers', 'getInvoice'])]
    private ?string $pseudo = null;
    
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Order::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['getUsers'])]
    private ?Collection $order_id = null ;
    

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Address::class, cascade:[ "persist", "remove"])]
    // #[ORM\OneToMany(mappedBy: 'user', targetEntity: Address::class)]
    #[Groups(['getUsers'])]
    private ?Collection $addresses = null;


    public function __construct()
    {
        $this->order_id = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        // $this->test = new ArrayCollection();
    }

    // #[ORM\OneToMany(mappedBy: 'user', targetEntity: OrderLine::class)]
    // private Collection $orderLines;

    // public function __construct()
    // {
    //     $this->orderLines = new ArrayCollection();
    // }

    // #[ORM\OneToMany(mappedBy: 'user', targetEntity: Order::class)]
    // private Collection $orders;

    // public function __construct()
    // {
    //     $this->orders = new ArrayCollection();
    // }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(string $resetToken): self
    {
     $this->resetToken = $resetToken;

     return $this;
    }
    /**
     * @return Collection<int, Order>
     */
    public function getOrderId(): Collection
    {
        return $this->order_id;
    }

    public function addOrderId(Order $orderId): self
    {
        if (!$this->order_id->contains($orderId)) {
            $this->order_id->add($orderId);
            $orderId->setUser($this);
        }

        return $this;
    }

    public function removeOrderId(Order $orderId): self
    {
        if ($this->order_id->removeElement($orderId)) {
            // set the owning side to null (unless already changed)
            if ($orderId->getUser() === $this) {
                $orderId->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Address>
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setUser($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->removeElement($address)) {
            // set the owning side to null (unless already changed)
            if ($address->getUser() === $this) {
                $address->setUser(null);
            }
        }

        return $this;
    }

   

}
