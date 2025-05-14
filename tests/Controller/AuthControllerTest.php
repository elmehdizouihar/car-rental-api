<?php 

namespace App\Tests\Controller;

use App\Controller\AuthController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthControllerTest extends TestCase
{
    private $controller;
    private $entityManager;
    private $passwordHasher;
    private $serializer;
    private $validator;
    
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        
        $this->controller = new AuthController();
    }
    
    public function testLoginWithInvalidCredentials()
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'email' => 'test@test.com',
            'password' => 'wrong'
        ]));
        
        $repo = $this->createMock(UserRepository::class);
        $repo->method('findOneBy')->willReturn(null);
        $this->entityManager->method('getRepository')->willReturn($repo);
        
        $response = $this->controller->login(
            $request, 
            $this->entityManager, 
            $this->passwordHasher,
            $this->serializer,
            $this->validator
        );
        
        $this->assertEquals(401, $response->getStatusCode());
    }
}