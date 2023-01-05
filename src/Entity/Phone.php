<?php

namespace App\Entity;

use App\Repository\PhoneRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation("self", href = @Hateoas\Route( "api_detailPhone", parameters= {"id" = "expr(object.getId())"},), 
 * exclusion = @Hateoas\Exclusion(groups="getPhones"))
 *  
 * @Hateoas\Relation("list", href = @Hateoas\Route( "api_phones"), 
 * exclusion = @Hateoas\Exclusion(groups="getPhones"))
 * 
 * @Hateoas\Relation("delete", href = @Hateoas\Route( "api_deletePhone", parameters= {"id" = "expr(object.getId())"},), 
 * exclusion = @Hateoas\Exclusion(groups="getPhones"))  
 */
#[ORM\Entity(repositoryClass: PhoneRepository::class)]
class Phone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getPhones"])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(["getPhones"])]
    private string $name;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(["getPhones"])]
    private ?string $brand = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $os = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $screenSize = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getOs(): ?string
    {
        return $this->os;
    }

    public function setOs(?string $os): self
    {
        $this->os = $os;

        return $this;
    }

    public function getScreenSize(): ?string
    {
        return $this->screenSize;
    }

    public function setScreenSize(?string $screenSize): self
    {
        $this->screenSize = $screenSize;

        return $this;
    }
    
}
