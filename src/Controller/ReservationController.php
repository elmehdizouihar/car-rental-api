<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\User;
use App\Service\Reservation\ReservationDeletionManager;
use App\Service\Reservation\ReservationManager;
use App\Service\Validation\ReservationValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ReservationController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ReservationValidator $reservationValidator,
        private ReservationManager $reservationManager
    ) {
    }

    #[Route('/api/reservations', name: 'api_reservation_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $reservation = $this->serializer->deserialize(
                $request->getContent(), 
                Reservation::class, 
                'json'
            );
            
            if ($errorResponse = $this->reservationValidator->validate($reservation)) {
                return $errorResponse;
            }

            $result = $this->reservationManager->checkAndCreateReservation($reservation);
            if ($result !== true) {
                return $result;
            }

            return $this->json(
                $reservation, 
                JsonResponse::HTTP_CREATED, 
                [], 
                ['groups' => 'reservation:read']
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'message' => 'Une erreur est survenue',
                    'error' => $e->getMessage()
                ], 
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }

    #[Route('/api/reservations/{id}', name: 'api_reservation_update', methods: ['PUT'])]
    public function update(
        Reservation $reservation,
        Request $request
    ): JsonResponse {
        try {
            $updatedData = $this->serializer->deserialize(
                $request->getContent(), 
                Reservation::class, 
                'json'
            );

            if ($errorResponse = $this->reservationValidator->validate($updatedData)) {
                return $errorResponse;
            }

            $result = $this->reservationManager->checkAndUpdateReservation(
                $reservation, 
                $updatedData
            );
            
            if ($result !== true) {
                return $result;
            }

            return $this->json(
                $reservation, 
                JsonResponse::HTTP_OK, 
                [], 
                ['groups' => 'reservation:read']
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'message' => 'Une erreur est survenue',
                    'error' => $e->getMessage()
                ], 
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }

    public function delete(
        Reservation $reservation,
        #[CurrentUser] User $user,
        ReservationDeletionManager $deletionManager
    ): JsonResponse {
        return $deletionManager->deleteReservation($reservation, $user);
    }
}