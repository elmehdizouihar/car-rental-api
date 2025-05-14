<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Repository\CarRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CarRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(name: 'api_cars_list'),
        new Get(name: 'api_car_detail')
    ],
    normalizationContext: ['groups' => ['car:read']],
    denormalizationContext: ['groups' => ['car:write']],
)]
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['car:read', 'reservation:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['car:read', 'car:write', 'reservation:read'])]
    #[Assert\NotBlank(message: 'La marque est obligatoire.')]
    private ?string $brand = null;

    #[ORM\Column(length: 255)]
    #[Groups(['car:read', 'car:write', 'reservation:read'])]
    #[Assert\NotBlank(message: 'Le modèle est obligatoire.')]
    private ?string $model = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['car:read', 'car:write'])]
    #[Assert\NotBlank(message: 'Le numéro d\'immatriculation est obligatoire.')]
    private ?string $registrationNumber = null;

    #[ORM\Column]
    #[Groups(['car:read', 'car:write', 'reservation:read'])]
    #[Assert\NotBlank(message: 'Le tarif journalier est obligatoire.')]
    #[Assert\Positive(message: 'Le tarif journalier doit être positif.')]
    private ?float $dailyRate = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(['car:read'])]
    private bool $isAvailable = true;

    #[ORM\OneToMany(mappedBy: 'car', targetEntity: Reservation::class, orphanRemoval: true)]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

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
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(string $registrationNumber): static
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getDailyRate(): ?float
    {
        return $this->dailyRate;
    }

    public function setDailyRate(float $dailyRate): static
    {
        $this->dailyRate = $dailyRate;

        return $this;
    }

    public function isAvailable(): bool
    {
        $now = new \DateTime();
        
        foreach ($this->reservations as $reservation) {
            if ($reservation->getStartDate() <= $now && $reservation->getEndDate() >= $now) {
                return false;
            }
        }
        
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setCar($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation) && $reservation->getCar() === $this) {
            $reservation->setCar(null);
        }

        return $this;
    }
}