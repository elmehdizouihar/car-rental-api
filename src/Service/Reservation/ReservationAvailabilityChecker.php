<?php

namespace App\Service\Reservation;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ReservationAvailabilityChecker
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function checkAvailability(Reservation $reservation): void
    {
        $car = $reservation->getCar();
        if (!$car || !$car->getId()) {
            throw new \InvalidArgumentException('Reservation must have a car with valid ID.');
        }

        if ($this->hasOverlappingReservations($reservation)) {
            throw new BadRequestHttpException('The car is already booked for these dates.');
        }
    }

    public function checkUserOwnership(Reservation $original, Reservation $updated): void
    {
        if ($original->getUser()->getId() !== $updated->getUser()->getId()) {
            throw new AccessDeniedHttpException('You cannot modify a reservation that does not belong to you.');
        }
    }

    private function hasOverlappingReservations(Reservation $reservation): bool
    {
        $overlappingReservations = $this->findOverlappingReservations(
            $reservation->getCar()->getId(),
            $reservation->getStartDate(),
            $reservation->getEndDate(),
            $reservation->getId()
        );

        return count($overlappingReservations) > 0;
    }

    private function findOverlappingReservations(
        int $carId, 
        \DateTimeInterface $startDate, 
        \DateTimeInterface $endDate, 
        ?int $excludeReservationId = null
    ): array {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(Reservation::class, 'r')
            ->where('r.car = :carId')
            ->andWhere('r.startDate < :endDate')
            ->andWhere('r.endDate > :startDate')
            ->setParameter('carId', $carId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($excludeReservationId) {
            $qb->andWhere('r.id != :excludeId')
               ->setParameter('excludeId', $excludeReservationId);
        }

        return $qb->getQuery()->getResult();
    }
}