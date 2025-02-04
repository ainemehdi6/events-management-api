<?php

declare(strict_types=1);

namespace App\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class JwtAuthenticationSuccessListener
{
    public function __construct(
        private readonly JWTEncoderInterface $jwtEncoder,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $data = $event->getData();
        $this->addTokenExpiration($data);
        $this->addUserRoles($data, $event->getUser());
        $event->setData($data);
    }

    private function addTokenExpiration(array &$data): void
    {
        if (!isset($data['token'])) {
            return;
        }

        try {
            $decodedToken = $this->jwtEncoder->decode($data['token']);
        } catch (JWTDecodeFailureException $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());

            return;
        }

        if (!isset($decodedToken['exp'])) {
            return;
        }

        $data['token_expiration'] = $decodedToken['exp'];
    }

    private function addUserRoles(array &$data, UserInterface $user): void
    {
        if (!isset($data['token'])) {
            return;
        }

        $data['user_roles'] = $user->getRoles();
    }
}
