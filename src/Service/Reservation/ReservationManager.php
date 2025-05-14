<?php

namespace App\Service\Reservation;

use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ReservationManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ReservationAvailabilityChecker $availabilityChecker
    ) {
    }

    public function checkAndCreateReservation(Reservation $reservation): JsonResponse|bool
    {
        try {
            $this->availabilityChecker->checkAvailability($reservation);
            
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();
            
            return true;
        } catch (BadRequestHttpException $e) {
            return $this->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->createErrorResponse(
                'Une erreur est survenue lors de la création',
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    public function checkAndUpdateReservation(
        Reservation $originalReservation,
        Reservation $updatedData
    ): JsonResponse|bool {
        try {
            $this->availabilityChecker->checkUserOwnership($originalReservation, $updatedData);
            
            $this->updateReservationFields($originalReservation, $updatedData);
            $this->availabilityChecker->checkAvailability($originalReservation);
            $originalReservation->updateTimestamps();
            
            $this->entityManager->flush();
            
            return true;
        } catch (AccessDeniedHttpException $e) {
            return $this->createErrorResponse($e->getMessage(), JsonResponse::HTTP_FORBIDDEN);
        } catch (BadRequestHttpException $e) {
            return $this->createErrorResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->createErrorResponse(
                'Une erreur est survenue lors de la mise à jour',
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    private function updateReservationFields(
        Reservation $original,
        Reservation $updated
    ): void {
        $original->setStartDate($updated->getStartDate());
        $original->setEndDate($updated->getEndDate());
        $original->setCar($updated->getCar());
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