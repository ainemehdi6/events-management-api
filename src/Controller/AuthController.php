<?php

namespace App\Controller;

use App\Dto\UserRegistrationDto;
use App\Entity\User;
use App\Transformer\UserTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('api')]
class AuthController extends ApiController
{
    #[Route('/register', name: 'register', methods: [Request::METHOD_POST])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserTransformer $transformer,
    ): JsonResponse {   
        $userRegistrationDto = $this->serializer->deserialize(
            $request->getContent(),
            UserRegistrationDto::class,
            JsonEncoder::FORMAT,
        );

        $errors = $validator->validate($userRegistrationDto);

        if (count($errors) > 0) {
            throw new ValidationFailedException($userRegistrationDto, $errors);
        }

        try {
            $user = $transformer->transform($userRegistrationDto);
        } catch (IOException) {
            return $this->jsonResponse(
                $request->getUri(),
                'Unable to handle document',
                'An error occured while trying to handle a document'
            );
        }

        return $this->jsonResponse(
            $request->getUri(),
            'User successfully created',
            'User has been successfully created. A confirmation email has been sent',
            ['accountUuid' => $frontUser->getUuid()],
            status: Response::HTTP_CREATED
        );


        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            throw new BadRequestHttpException('Email and password are required.');
        }

        $user = new User();
        $user->setEmail($email);
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User registered successfully'], 201);
    }
}