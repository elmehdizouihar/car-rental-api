<?php

namespace App\Service\Reservation;

use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserReservationManager
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function getUserReservations(
        User $requestedUser, 
        User $authenticatedUser
    ): array {
        $this->validateUserAccess($requestedUser, $authenticatedUser);

        return $this->entityManager->getRepository(Reservation::class)
            ->findBy(
                ['user' => $requestedUser],
                ['createdAt' => 'DESC']
            );
    }

    private function validateUserAccess(
        User $requestedUser,
        User $authenticatedUser
    ): void {
        if ($requestedUser->getId() !== $authenticatedUser->getId()) {
            throw new AccessDeniedHttpException(
                'Vous ne pouvez accéder qu\'à vos propres réservations.'
            );
        }
    }
}