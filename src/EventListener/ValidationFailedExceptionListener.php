<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: 'kernel.exception', method: 'onKernelException')]
class ValidationFailedExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $previousException = $exception->getPrevious();

        if ($exception instanceof HttpException && $previousException instanceof ValidationFailedException) {
            $exception = $previousException;
        }

        if (!$exception instanceof ValidationFailedException) {
            return;
        }

        $event->setResponse($this->generateBadRequestResponse($event->getRequest(), $exception->getViolations()));
    }

    private function generateBadRequestResponse(Request $request, ConstraintViolationListInterface $violationErrors): JsonResponse
    {
        $errors = [];

        foreach ($violationErrors as $violationError) {
            $errors[] = [
                'name' => $violationError->getPropertyPath(),
                'reason' => $violationError->getMessage(),
            ];
        }

        return new JsonResponse([
            'statusCode' => Response::HTTP_BAD_REQUEST,
            'instance' => $request->getUri(),
            'title' => 'Invalid request payload',
            'invalidParams' => $errors,
        ], Response::HTTP_BAD_REQUEST);
    }
}
