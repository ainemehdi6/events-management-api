<?php

declare(strict_types=1);

namespace App\Security\Handler;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationFailureHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class PostAuthenticationHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    public function __construct(
        private readonly AuthenticationSuccessHandler $authenticationSuccessHandler,
        private readonly AuthenticationFailureHandler $authenticationFailureHandler,
        private readonly string $applicationToken,
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $this->checkApiToken($request);

        /** @var User $user */
        $user = $token->getUser();

        return $this->authenticationSuccessHandler->handleAuthenticationSuccess($user);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $this->checkApiToken($request);

        return $this->authenticationFailureHandler->onAuthenticationFailure($request, $exception);
    }

    public function checkApiToken(Request $request): void
    {
        if (!$request->headers->has('X-API-TOKEN') || empty($request->headers->get('X-API-TOKEN'))) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        if ($request->headers->get('X-API-TOKEN') !== $this->applicationToken) {
            throw new CustomUserMessageAuthenticationException('Invalid API token');
        }
    }
}