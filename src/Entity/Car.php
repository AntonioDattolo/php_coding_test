<?php

namespace App\Entity;

use App\Repository\CarRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Event\LifecycleEventArgs;

#[ORM\Entity(repositoryClass: CarRepository::class)]
#dopo prima migrazione il nome della tabella è al singolare
#[ORM\Table(name: 'cars')]  // Forza il nome della tabella a 'cars'
#gestisti autonomamente i timestamps
#[ORM\HasLifecycleCallbacks]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The brand cannot be empty.")]
    private ?string $brand = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The model cannot be empty.")]
    private ?string $model = null;

    #[ORM\Column]
    #[Assert\Positive(message: "The production year must be positive and cannot be greater than the current year.")]
    private ?int $production_year = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\Positive(message: "The price must be positive.")]
    private ?string $price = null;

    //tecnicamente dal front sono da impostare le checkbox 'required' 
    #[ORM\Column(type: Types::BOOLEAN)]
    #[Assert\Choice(message:  "Specify the status of the car (sold/available).")]
    private ?bool $state = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Assert\Choice(message: "Specify the condition of the car (new/used).")]
    private ?bool $isNew = true;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;


    //Per soft delete, aggiungo la colonna deletedAt
    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        if (empty($brand)) {
            throw new \InvalidArgumentException("Brand can't be nullable");
        }
        $this->brand = $brand;
        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        if (empty($model)) {
            throw new \InvalidArgumentException("Model can't be nullable");
        }
        $this->model = $model;

        return $this;
    }

    public function getProductionYear(): ?int
    {
        return $this->production_year;
    }

    public function setProductionYear(int $production_year): static
    {
        
        //ho bisogno dell'anno corrente
        $currentYear = (int) Date('Y');
        $this->production_year = $production_year;
        if($production_year < 0){
            throw new \InvalidArgumentException("The production year must be positive and cannot be greater than the current year.");
        }

        if($production_year > $currentYear){
            throw new \InvalidArgumentException("The production year must be positive and cannot be greater than the current year.");
        }
        
        return $this;
    }

    public function getPrice(): ?string
    {
    
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
        if($price < 0){
            throw new \InvalidArgumentException("The price must be positive.");
        }
        return $this;
    }

    //******************************PER SOFTDELETE

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    //*****************aggiunti timestamps

    // Callback per la creazione dell'entità
    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    // Callback per la modifica dell'entità
    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    //************************************ */ aggiunti Stato e Condizione

    public function getState(): bool
    {
        return $this->state;
    }

    public function setState($state): static
    {
        $this->state = $state;
        // valore null genera un'eccezzione, solo in caso il front non gestisca bene la validazione
        if ($state == null) {
            throw new \InvalidArgumentException("Specify the status of the car (sold/available).");
        }

        return $this;
    }

    public function getIsNew(): bool
    {
        return $this->isNew;
    }

    public function setIsNew($isNew): static
    {
        $this->isNew = $isNew;
        if ($isNew == null) {
            throw new \InvalidArgumentException("Specify the condition of the car (new/used).");
        }
        return $this;
    }
}
