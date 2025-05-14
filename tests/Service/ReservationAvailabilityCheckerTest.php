<?php 

namespace App\Tests\Service\Reservation;

use App\Entity\Car;
use App\Entity\Reservation;
use App\Entity\User;
use App\Service\Reservation\ReservationAvailabilityChecker;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReservationAvailabilityCheckerTest extends TestCase
{
    private MockObject&EntityManagerInterface $entityManager;
    private ReservationAvailabilityChecker $checker;
    
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->checker = new ReservationAvailabilityChecker($this->entityManager);
    }
    
    public function testCheckAvailabilityNoOverlap()
    {
        // 1. Configurez le mock de Query
        $query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        $query->method('getResult')->willReturn([]);

        // 2. Configurez le mock de QueryBuilder
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('getQuery')->willReturn($query);
        $qb->method('select')->willReturnSelf();
        $qb->method('from')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();

        // 3. Configurez EntityManager
        $this->entityManager->method('createQueryBuilder')->willReturn($qb);

        // 4. Créez une voiture valide avec ID
        $car = new Car();
        $carReflection = new \ReflectionClass($car);
        $carIdProperty = $carReflection->getProperty('id');
        $carIdProperty->setAccessible(true);
        $carIdProperty->setValue($car, 1);

        // 5. Configurez la réservation
        $reservation = new Reservation();
        $reservation->setCar($car);
        $reservation->setStartDate(new \DateTime('today'));
        $reservation->setEndDate(new \DateTime('tomorrow'));

        // 6. Exécutez le test
        $this->checker->checkAvailability($reservation);
        $this->assertTrue(true, 'Aucune exception ne devrait être levée');
    }
    
    public function testCheckUserOwnershipValid()
    {
        // Crée un vrai User plutôt qu'un mock
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('password');
        
        // Utilise la réflexion pour définir l'ID
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, 1);
        
        $original = new Reservation();
        $original->setUser($user);
        
        $updated = new Reservation();
        $updated->setUser($user);
        
        $this->checker->checkUserOwnership($original, $updated);
        $this->assertTrue(true);
    }
}