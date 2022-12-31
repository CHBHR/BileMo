<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getCustomer", 'getClientCustomers', 'getClientCustomerDetail', 'customer'])]
    private ?int $id = null;
    
    #[ORM\Column(length: 50, unique: true)]
    #[Groups(["getCustomer", 'getClientCustomerDetail', 'customer'])]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    #[Groups(["getClientCustomers", 'getClientCustomerDetail', 'customer'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    #[Groups(["getClientCustomers", 'getClientCustomerDetail', 'customer'])]
    private ?string $lastName = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\ManyToOne(inversedBy: 'customers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?client $client = null;

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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_CUSTOMER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getClient(): ?client
    {
        return $this->client;
    }

    public function setClient(?client $client): self
    {
        $this->client = $client;

        return $this;
    }

}
