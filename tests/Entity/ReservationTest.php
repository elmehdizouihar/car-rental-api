<?php 

namespace App\Tests\Entity;

use App\Entity\Reservation;
use PHPUnit\Framework\TestCase;

class ReservationTest extends TestCase
{
    public function testOverlapsWith()
    {
        $reservation = new Reservation();
        $reservation->setStartDate(new \DateTime('2023-01-01'));
        $reservation->setEndDate(new \DateTime('2023-01-10'));
        
        // Test chevauchement
        $this->assertTrue($reservation->overlapsWith(
            new \DateTime('2023-01-05'), 
            new \DateTime('2023-01-15')
        ));
        
        // Test non chevauchement
        $this->assertFalse($reservation->overlapsWith(
            new \DateTime('2023-01-11'), 
            new \DateTime('2023-01-15')
        ));
    }
}