<?php

namespace App\Service\Reservation;

use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ReservationDeletionManager
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function deleteReservation(Reservation $reservation, User $user): JsonResponse
    {
        try {
            $this->validateOwnership($reservation, $user);
            
            $this->entityManager->remove($reservation);
            $this->entityManager->flush();

            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        } catch (AccessDeniedHttpException $e) {
            return $this->createErrorResponse($e->getMessage(), JsonResponse::HTTP_FORBIDDEN);
        } catch (\Exception $e) {
            return $this->createErrorResponse(
                'Une erreur est survenue lors de la suppression',
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    private function validateOwnership(Reservation $reservation, User $user): void
    {
        if ($reservation->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedHttpException(
                'You cannot delete a reservation that does not belong to you.'
            );
        }
    }

    private function createErrorResponse(
        string $message, 
        int $statusCode,
        ?string $errorDetail = null
    ): JsonResponse {
        $responseData = ['message' => $message];
        
        if ($errorDetail) {
            $responseData['error'] = $errorDetail;
        }

        return new JsonResponse($responseData, $statusCode);
    }
}