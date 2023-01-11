<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation("self", href = @Hateoas\Route( "api_detailCustomer", parameters= {"id" = "expr(object.getId())"},),
 * exclusion = @Hateoas\Exclusion(groups="getCustomers"))
 *
 * @Hateoas\Relation("self", href = @Hateoas\Route( "api_clientCustomerDetail", parameters= {"clientId" = "expr(object.getClient().getId())","customerId" = "expr(object.getId())"}),
 * exclusion = @Hateoas\Exclusion(groups="getClientCustomers"))
 *
 * @Hateoas\Relation("list", href = @Hateoas\Route( "api_customer"),
 * exclusion = @Hateoas\Exclusion(groups="getCustomers"))
 *
 * @Hateoas\Relation("list", href = @Hateoas\Route( "api_clientCustomers", parameters= {"clientId" = "expr(object.getClient().getId())"}),
 * exclusion = @Hateoas\Exclusion(groups="getClientCustomers"))
 *
 * @Hateoas\Relation("delete", href = @Hateoas\Route( "api_deleteClientCustomer", parameters= {"clientId" = "expr(object.getClient().getId())","customerId" = "expr(object.getId())"}),
 * exclusion = @Hateoas\Exclusion(groups="getClientCustomers"))
 *
 * @Hateoas\Relation("post", href = @Hateoas\Route( "api_createClientCustomer", parameters= {"clientId" = "expr(object.getClient().getId())"}),
 * exclusion = @Hateoas\Exclusion(groups="getClientCustomers"))
 */
#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getCustomers", 'getClientCustomers', 'getClientCustomerDetail', 'customer'])]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(["getCustomers", 'getClientCustomerDetail', 'customer'])]
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
