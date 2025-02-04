<?php

declare(strict_types=1);

namespace App\Tests\Api\Controller;

use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    private ?Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create('fr_FR');
    }

    private function generateUserData(): array
    {
        return [
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'email' => $this->faker->email(),
            'password' => 'Password123!',
        ];
    }

    private function buildHeaders(array $additionalHeaders = []): array
    {
        return array_merge([
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-API-TOKEN' => $_ENV['APPLICATION_TOKEN'] ?? 'test_token',
        ], $additionalHeaders);
    }

    public function testSuccessfulRegistration(): void
    {
        $client = static::createClient();
        $userData = $this->generateUserData();

        $client->request(
            Request::METHOD_POST,
            '/api/register',
            server: $this->buildHeaders(),
            content: json_encode($userData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User registered successfully', $responseData['message']);

        $this->assertArrayHasKey('user', $responseData);
        $this->assertEquals($userData['email'], $responseData['user']['email']);
        $this->assertEquals($userData['firstname'], $responseData['user']['firstname']);
        $this->assertEquals($userData['lastname'], $responseData['user']['lastname']);
    }

    public function testRegistrationWithInvalidEmail(): void
    {
        $client = static::createClient();
        $userData = $this->generateUserData();
        $userData['email'] = 'invalid-email';

        $client->request(
            Request::METHOD_POST,
            '/api/register',
            server: $this->buildHeaders(),
            content: json_encode($userData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('email', $responseData['errors']);
    }

    public function testRegistrationWithWeakPassword(): void
    {
        $client = static::createClient();
        $userData = $this->generateUserData();
        $userData['password'] = 'weak';

        $client->request(
            Request::METHOD_POST,
            '/api/register',
            server: $this->buildHeaders(),
            content: json_encode($userData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('errors', $responseData);
        $this->assertArrayHasKey('password', $responseData['errors']);
    }

    public function testSuccessfulLogin(): void
    {
        $client = static::createClient();
        $userData = $this->generateUserData();

        $client->request(
            Request::METHOD_POST,
            '/api/register',
            server: $this->buildHeaders(),
            content: json_encode($userData)
        );

        $client->request(
            Request::METHOD_POST,
            '/api/login-check',
            server: $this->buildHeaders(),
            content: json_encode([
                'email' => $userData['email'],
                'password' => $userData['password'],
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $responseData);
        $this->assertArrayHasKey('refresh_token', $responseData);
        $this->assertArrayHasKey('token_expiration', $responseData);
        $this->assertArrayHasKey('user_roles', $responseData);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $client = static::createClient();

        $client->request(
            Request::METHOD_POST,
            '/api/login-check',
            server: $this->buildHeaders(),
            content: json_encode([
                'email' => 'nonexistent@example.com',
                'password' => 'WrongPassword123!',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetProfileAuthenticated(): void
    {
        $client = static::createClient();
        $userData = $this->generateUserData();

        $client->request(
            Request::METHOD_POST,
            '/api/register',
            server: $this->buildHeaders(),
            content: json_encode($userData)
        );

        $client->request(
            Request::METHOD_POST,
            '/api/login-check',
            server: $this->buildHeaders(),
            content: json_encode([
                'email' => $userData['email'],
                'password' => $userData['password'],
            ])
        );

        $loginData = json_decode($client->getResponse()->getContent(), true);

        $client->request(
            Request::METHOD_GET,
            '/api/profile',
            server: $this->buildHeaders([
                'HTTP_Authorization' => 'Bearer '.$loginData['token'],
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $profileData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($userData['email'], $profileData['email']);
        $this->assertEquals($userData['firstname'], $profileData['firstname']);
        $this->assertEquals($userData['lastname'], $profileData['lastname']);
    }

    public function testGetProfileUnauthenticated(): void
    {
        $client = static::createClient();

        $client->request(
            Request::METHOD_GET,
            '/api/profile',
            server: $this->buildHeaders()
        );
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
