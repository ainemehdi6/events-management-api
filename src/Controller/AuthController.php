<?php

namespace App\Controller;

use App\Dto\UserRegistrationDto;
use App\Transformer\UserTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('api')]
class AuthController extends ApiController
{
    #[Route('/register', name: 'register', methods: [Request::METHOD_POST])]
    public function register(
        Request $request,
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
        } catch (IOException $e) {
            return $this->jsonResponse(
                $request->getUri(),
                $e->getMessage(),
                'An error occured while trying to register the user'
            );
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->jsonResponse(
            $request->getUri(),
            'User successfully created',
            'User has been successfully created',
            ['accountUuid' => $user->getUuid()],
            status: Response::HTTP_CREATED,
        );       
    }
}