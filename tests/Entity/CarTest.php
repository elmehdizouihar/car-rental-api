<?php 

namespace App\Tests\Entity;

use App\Entity\Car;
use App\Entity\Reservation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class CarTest extends TestCase
{
    public function testIsAvailableWithNoReservations()
    {
        $car = new Car();
        $car->setIsAvailable(true);
        $this->assertTrue($car->isIsAvailable());
    }
    
    public function testIsAvailableWithFutureReservation()
    {
        $car = new Car();
        $reservation = new Reservation();
        $reservation->setStartDate(new \DateTime('tomorrow'));
        $reservation->setEndDate(new \DateTime('+2 days'));
        $car->addReservation($reservation);
        
        $this->assertTrue($car->isIsAvailable());
    }
    
    public function testIsNotAvailableWithCurrentReservation()
    {
        $car = new Car();
        $reservation = new Reservation();
        $reservation->setStartDate(new \DateTime('yesterday'));
        $reservation->setEndDate(new \DateTime('tomorrow'));
        $car->addReservation($reservation);
        
        $this->assertFalse($car->isIsAvailable());
    }
    
    public function testValidation()
    {
        // Crée le validateur Symfony
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping() // Utilisez enableAttributeMapping() au lieu de enableAnnotationMapping()
            ->getValidator();
            
        $car = new Car();
        // Ne pas définir les propriétés obligatoires
        $errors = $validator->validate($car);
        
        // Vérifie qu'on a bien des erreurs de validation
        $this->assertGreaterThan(0, count($errors), 'La validation devrait échouer sans données');
        
        // Liste des propriétés obligatoires qui devraient échouer
        $requiredProperties = ['brand', 'model', 'registrationNumber', 'dailyRate'];
        $foundErrors = [];
        
        foreach ($errors as $error) {
            $foundErrors[] = $error->getPropertyPath();
        }
        
        // Vérifie que toutes les propriétés obligatoires ont généré une erreur
        foreach ($requiredProperties as $property) {
            $this->assertContains($property, $foundErrors, "La propriété $property devrait être requise");
        }
        
        // Test avec des données valides
        $car->setBrand('Toyota')
            ->setModel('Corolla')
            ->setRegistrationNumber('AB-123-CD')
            ->setDailyRate(50.0);
            
        $errors = $validator->validate($car);
        $this->assertCount(0, $errors, 'La validation devrait réussir avec des données valides');
    }
}