<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class ApiController extends AbstractController
{

    public const LIMIT_PARAMETER_NAME = 'limit';

    private const LIMIT_PARAMETER_DEFAULT_VALUE = 15;

    protected SerializerInterface $serializer;

    public function __construct(
        protected TranslatorInterface $translator,
    ) {
        $this->serializer = new Serializer(
            normalizers: [
                new UidNormalizer(),
                new ArrayDenormalizer(),
                new ObjectNormalizer(
                    propertyTypeExtractor: new PropertyInfoExtractor(
                        typeExtractors: [new PhpDocExtractor(), new ReflectionExtractor()],
                    ),
                ),
            ],
            encoders: [
                new JsonEncoder(),
            ],
        );
    }

    protected function jsonResponse(
        string $instance,
        string $title,
        ?string $details = null,
        array $specificDetails = [],
        mixed $content = null,
        int $status = Response::HTTP_OK,
        array $headers = [],
        array $context = [],
    ): JsonResponse {
        $responseData = [
            'statusCode' => $status,
            'instance' => $instance,
            'title' => $title,
        ];

        if (!empty($details)) {
            $responseData['details'] = $details;
        }

        if (!empty($specificDetails)) {
            $responseData = array_merge($responseData, $specificDetails);
        }

        if (null !== $content) {
            $responseData['content'] = $content;
        }

        return $this->json($responseData, $status, $headers, $context);
    }

    private function formatUrl(string $url, array $queryParameters = []): string
    {
        return sprintf('%s?%s', $url, http_build_query($queryParameters));
    }

    private function extractQueryParameters(Request $request): array
    {
        return array_filter(
            $request->query->all(),
            fn (string $queryParamName) => self::LIMIT_PARAMETER_NAME !== $queryParamName && self::PAGE_PARAMETER_NAME !== $queryParamName,
            ARRAY_FILTER_USE_KEY
        );
    }
}
