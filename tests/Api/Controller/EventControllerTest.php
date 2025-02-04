<?php

declare(strict_types=1);

namespace App\Tests\Api\Controller;

use App\Tests\DatabasePrimer;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class EventControllerTest extends WebTestCase
{
    private ?Generator $faker;
    private ?KernelBrowser $client;
    private array $adminCredentials = [
        'email' => 'admin@example.com',
        'password' => 'Admin123!',
    ];
    private array $userCredentials = [
        'email' => 'user@example.com',
        'password' => 'User123!',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create('fr_FR');
        $this->client = static::createClient();

        $container = static::getContainer();
        DatabasePrimer::prime(
            $container->get(EntityManagerInterface::class),
            $container->get(UserPasswordHasherInterface::class)
        );
    }

    private function buildHeaders(array $additionalHeaders = []): array
    {
        return array_merge([
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X-API-TOKEN' => $_ENV['APPLICATION_TOKEN'] ?? 'test_token',
        ], $additionalHeaders);
    }

    private function getAuthToken(string $email, string $password): ?string
    {
        if ($email !== $this->adminCredentials['email']) {
            $this->client->request(
                Request::METHOD_POST,
                '/api/register',
                server: $this->buildHeaders(),
                content: json_encode([
                    'email' => $email,
                    'password' => $password,
                    'firstname' => 'Test',
                    'lastname' => 'User',
                ])
            );
        }

        $this->client->request(
            Request::METHOD_POST,
            '/api/login-check',
            server: $this->buildHeaders(),
            content: json_encode([
                'email' => $email,
                'password' => $password,
            ])
        );

        if ($this->client->getResponse()->getStatusCode() !== Response::HTTP_OK) {
            return null;
        }

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        return $responseData['token'] ?? null;
    }

    public function testGetEventsSuccessfully(): void
    {
        $token = $this->getAuthToken($this->userCredentials['email'], $this->userCredentials['password']);
        $this->assertNotNull($token, 'Failed to get authentication token');

        $this->client->request(
            Request::METHOD_GET,
            '/api/events',
            server: $this->buildHeaders([
                'HTTP_Authorization' => 'Bearer ' . $token
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);

        if (!empty($responseData)) {
            $event = reset($responseData);
            $this->assertArrayHasKey('id', $event);
            $this->assertArrayHasKey('title', $event);
            $this->assertArrayHasKey('description', $event);
            $this->assertArrayHasKey('date', $event);
            $this->assertArrayHasKey('category', $event);
            $this->assertArrayHasKey('status', $event);
        }
    }

    public function testGetEventById(): void
    {
        $token = $this->getAuthToken($this->userCredentials['email'], $this->userCredentials['password']);
        $this->assertNotNull($token, 'Failed to get authentication token');

        $this->client->request(
            Request::METHOD_GET,
            '/api/events',
            server: $this->buildHeaders([
                'HTTP_Authorization' => 'Bearer ' . $token
            ])
        );

        $events = json_decode($this->client->getResponse()->getContent(), true);

        if (empty($events)) {
            $this->markTestSkipped('No events available for testing');
        }

        $eventId = $events[0]['id'];

        $this->client->request(
            Request::METHOD_GET,
            "/api/events/{$eventId}",
            server: $this->buildHeaders([
                'HTTP_Authorization' => 'Bearer ' . $token
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals($eventId, $responseData['id']);
    }

    public function testGetNonExistentEvent(): void
    {
        $token = $this->getAuthToken($this->userCredentials['email'], $this->userCredentials['password']);
        $this->assertNotNull($token, 'Failed to get authentication token');

        $this->client->request(
            Request::METHOD_GET,
            '/api/events/00000000-0000-0000-0000-000000000000',
            server: $this->buildHeaders([
                'HTTP_Authorization' => 'Bearer ' . $token
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteEventAsNonAdmin(): void
    {
        $token = $this->getAuthToken($this->userCredentials['email'], $this->userCredentials['password']);
        $this->assertNotNull($token, 'Failed to get authentication token');

        $this->client->request(
            Request::METHOD_GET,
            '/api/events',
            server: $this->buildHeaders([
                'HTTP_Authorization' => 'Bearer ' . $token
            ])
        );

        $events = json_decode($this->client->getResponse()->getContent(), true);

        if (empty($events)) {
            $this->markTestSkipped('No events available for testing');
        }

        $eventId = $events[0]['id'];

        $this->client->request(
            Request::METHOD_DELETE,
            "/api/events/{$eventId}",
            server: $this->buildHeaders([
                'HTTP_Authorization' => 'Bearer ' . $token
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteEventAsAdmin(): void
    {
        $token = $this->getAuthToken($this->adminCredentials['email'], $this->adminCredentials['password']);
        $this->assertNotNull($token, 'Failed to get admin authentication token');

        $this->client->request(
            Request::METHOD_GET,
            '/api/events',
            server: $this->buildHeaders([
                'HTTP_Authorization' => 'Bearer ' . $token
            ])
        );

        $events = json_decode($this->client->getResponse()->getContent(), true);

        if (empty($events)) {
            $this->markTestSkipped('No events available for testing');
        }

        $eventId = $events[0]['id'];

        $this->client->request(
            Request::METHOD_DELETE,
            "/api/events/{$eventId}",
            server: $this->buildHeaders([
                'HTTP_Authorization' => 'Bearer ' . $token
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->client->request(
            Request::METHOD_GET,
            "/api/events/{$eventId}",
            server: $this->buildHeaders([
                'HTTP_Authorization' => 'Bearer ' . $token
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteEventUnauthenticated(): void
    {
        $token = $this->getAuthToken($this->userCredentials['email'], $this->userCredentials['password']);
        $this->assertNotNull($token, 'Failed to get authentication token');

        $this->client->request(
            Request::METHOD_GET,
            '/api/events',
            server: $this->buildHeaders([
                'HTTP_Authorization' => 'Bearer ' . $token
            ])
        );

        $events = json_decode($this->client->getResponse()->getContent(), true);

        if (empty($events)) {
            $this->markTestSkipped('No events available for testing');
        }

        $eventId = $events[0]['id'];

        $this->client->request(
            Request::METHOD_DELETE,
            "/api/events/{$eventId}",
            server: $this->buildHeaders()
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->client = null;
    }
}