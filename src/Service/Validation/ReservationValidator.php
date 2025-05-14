<?php

namespace App\Service\Validation;

use App\Entity\Reservation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReservationValidator
{
    public function __construct(
        private ValidatorInterface $validator
    ) {
    }

    public function validate(Reservation $reservation): ?JsonResponse
    {
        $errors = $this->validator->validate(
            $reservation, 
            null, 
            ['reservation_validation']
        );
        
        if (count($errors) > 0) {
            return $this->createValidationErrorResponse($errors);
        }

        return null;
    }

    private function createValidationErrorResponse($errors): JsonResponse
    {
        $errorMessages = [];
        
        foreach ($errors as $error) {
            $errorMessages[$error->getPropertyPath()] = $error->getMessage();
        }
        
        return new JsonResponse(
            [
                'message' => 'Validation failed',
                'errors' => $errorMessages
            ],
            JsonResponse::HTTP_BAD_REQUEST
        );
    }
}