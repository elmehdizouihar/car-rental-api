<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    // src/Repository/ReservationRepository.php
    // src/Repository/ReservationRepository.php
    public function findOverlappingReservations(int $carId, \DateTimeInterface $startDate, \DateTimeInterface $endDate, ?int $excludeReservationId = null): array
    {
        $qb = $this->createQueryBuilder('r')
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

//    /**
//     * @return Reservation[] Returns an array of Reservation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Reservation
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
