<?php

namespace App\Tests\Api\v1\Controller;

use App\Tests\Api\v1\Constant\Document;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;

class SecurityControllerTest extends WebTestCase
{
    use Factories;

    private const string UUID_REGEX_PATTERN = '/^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}$/';

    private ?Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('FR');

        parent::__construct();
    }

    private function createAuthenticatedClient(string $email, string $password): KernelBrowser
    {
        $client = static::createClient();

        $client->request(
            Request::METHOD_POST,
            '/api/v1/login-check',
            server: $this->buildHeaders(),
            content: json_encode([
                'email' => $email,
                'password' => $password,
            ])
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }

    private function generateIdentity(): array
    {
        $passParts = [
            $this->faker->regexify('[A-Z]'),
            $this->faker->regexify('[0-9]'),
            $this->faker->regexify('[a-z]{10}'),
        ];
        $password = str_shuffle(join('', $passParts));

        $email = mb_strtolower(sprintf('%s%s%s@%s',
            $this->faker->randomNumber(5),
            $this->faker->safeEmailDomain(),
        ));

        return [
            'email' => $email,
            'password' => $password,
        ];
    }

    public function buildHeaders(array $additionnalHeaders = []): array
    {
        return [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_TOKEN' => $_ENV['APPLICATION_TOKEN'],
            ...$additionnalHeaders,
        ];
    }

    public function testRegisterIndividualUserWithEmail(): void
    {
        $client = static::createClient();

        $identity = $this->generateIdentity();

        $client->request(
            Request::METHOD_POST,
            '/api/v1/register/step/1',
            parameters: [
                'body' => json_encode([
                    'password' => $identity['password'],
                    'email' => $identity['email'],
                ]),
            ],
            server: [
                'CONTENT_TYPE' => 'multipart/form-data',
                'HTTP_X_API_TOKEN' => $_ENV['APPLICATION_TOKEN'],
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertNotEmpty($responseData['title']);
        $this->assertEquals('User successfully created', $responseData['title']);

        $this->assertNotEmpty($responseData['accountUuid']);
        $this->assertNotFalse(preg_match(self::UUID_REGEX_PATTERN, $responseData['accountUuid']));
    }

    public function testResgisterIndividualUserWithEmailWithoutPasswordFails(): void
    {
        $client = static::createClient();

        $identity = $this->generateIdentity();

        $client->request(
            Request::METHOD_POST,
            '/api/v1/register/step/1',
            parameters: [
                'body' => json_encode([
                    'email' => $identity['email'],
                ]),
            ],
            server: [
                'CONTENT_TYPE' => 'multipart/form-data',
                'HTTP_X_API_TOKEN' => $_ENV['APPLICATION_TOKEN'],
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertNotEmpty($responseData['title']);
        $this->assertEquals('Invalid request payload', $responseData['title']);

        $this->assertNotEmpty($responseData['invalidParams']);

        $error = reset($responseData['invalidParams']);

        $this->assertNotEmpty($error['name']);
        $this->assertEquals('password', $error['name']);

        $this->assertNotEmpty($error['reason']);
        $this->assertEquals('Password is required', $error['reason']);
    }
}