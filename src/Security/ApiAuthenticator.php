<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiAuthenticator extends AbstractAuthenticator
{
    public const API_TOKEN_HEADER_KEY = 'X-API-TOKEN';

    public function __construct(
        private readonly string $applicationToken,
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::API_TOKEN_HEADER_KEY);
    }

    public function authenticate(Request $request): Passport
    {
        $token = $request->headers->get(self::API_TOKEN_HEADER_KEY);

        if (empty($token)) {
            throw new CustomUserMessageAuthenticationException('The API token provided is empty');
        }

        if ($token !== $this->applicationToken) {
            throw new CustomUserMessageAuthenticationException('The API token provided is invalid');
        }

        return new SelfValidatingPassport(
            new UserBadge($token, function ($token) {
                return new InMemoryUser('api-front', $token, ['ROLE_API']);
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'error' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ], Response::HTTP_UNAUTHORIZED);
    }
}