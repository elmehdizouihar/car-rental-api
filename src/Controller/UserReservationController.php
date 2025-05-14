<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Reservation\UserReservationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[AsController]
class UserReservationController extends AbstractController
{
    #[Route('/api/users/{id}/reservations', name: 'api_user_reservations', methods: ['GET'])]
    public function getUserReservations(
        User $user,
        #[CurrentUser] User $authenticatedUser,
        UserReservationManager $userReservationManager
    ): JsonResponse {
        $reservations = $userReservationManager->getUserReservations($user, $authenticatedUser);
        
        return $this->json(
            $reservations,
            200,
            [],
            ['groups' => ['reservation:read']]
        );
    }
}