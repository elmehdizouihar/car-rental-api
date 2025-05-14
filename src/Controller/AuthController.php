<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $credentials = json_decode($request->getContent(), true);

        if (!isset($credentials['email'], $credentials['password'])) {
            return new JsonResponse(
                ['message' => 'L\'email et le mot de passe sont requis.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $credentials['email']]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $credentials['password'])) {
            return new JsonResponse(
                ['message' => 'Identifiants invalides.'],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        return new JsonResponse(['message' => 'Authentification réussie.']);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $user = $this->serializer->deserialize(
                $request->getContent(), 
                User::class, 
                'json'
            );

            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                return $this->json($errors, JsonResponse::HTTP_BAD_REQUEST);
            }

            $hashedPassword = $this->passwordHasher->hashPassword(
                $user, 
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json(
                $user, 
                JsonResponse::HTTP_CREATED, 
                [], 
                ['groups' => 'user:read']
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['message' => 'Erreur lors de l\'enregistrement'],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}