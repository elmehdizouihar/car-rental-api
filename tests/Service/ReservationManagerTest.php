<?php 

namespace App\Tests\Service\Reservation;

use App\Entity\Reservation;
use App\Service\Reservation\ReservationAvailabilityChecker;
use App\Service\Reservation\ReservationManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ReservationManagerTest extends TestCase
{
    private MockObject&EntityManagerInterface $entityManager;
    private MockObject&ReservationAvailabilityChecker $availabilityChecker;
    private ReservationManager $manager;
    
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->availabilityChecker = $this->createMock(ReservationAvailabilityChecker::class);
        $this->manager = new ReservationManager($this->entityManager, $this->availabilityChecker);
    }
    
    public function testSuccessfulReservationCreation()
    {
        $reservation = new Reservation();
        
        $this->availabilityChecker->method('checkAvailability');
        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');
        
        $result = $this->manager->checkAndCreateReservation($reservation);
        $this->assertTrue($result);
    }
    
    public function testFailedReservationCreation()
    {
        $reservation = new Reservation();
        
        $this->availabilityChecker->method('checkAvailability')
            ->willThrowException(new BadRequestHttpException('Conflict'));
            
        $result = $this->manager->checkAndCreateReservation($reservation);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(400, $result->getStatusCode());
    }
}