<?php

namespace App\Tests\Api\Controller;

use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;

class SecurityControllerTest extends WebTestCase
{
    use Factories;

    private const UUID_REGEX_PATTERN = '/^[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}$/';

    private ?Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create('fr_FR');
    }

    private function createAuthenticatedClient(string $email, string $password): KernelBrowser
    {
        $client = static::createClient();

        $client->request(
            Request::METHOD_POST,
            '/api/login-check',
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

        $firstname = ucfirst($this->faker->firstName());
        $lastname = ucfirst($this->faker->lastName());

        $email = mb_strtolower(sprintf('%s%s%s@%s',
            substr($firstname, 0, 1),
            $lastname,
            $this->faker->randomNumber(5),
            $this->faker->safeEmailDomain(),
        ));

        return [
            'firstname' => $firstname,
            'lastname' => $lastname,
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
            '/api/register',
            server: $this->buildHeaders(),
            content: json_encode([
                'firstname' => $identity['firstname'],
                'lastname' => $identity['lastname'],
                'password' => $identity['password'],
                'email' => $identity['email'],
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertNotEmpty($responseData['title']);
        $this->assertEquals('User successfully created', $responseData['title']);

        $this->assertNotEmpty($responseData['accountUuid']);
        $this->assertMatchesRegularExpression(self::UUID_REGEX_PATTERN, $responseData['accountUuid']);
    }

    public function testRegisterIndividualUserWithEmailWithoutPasswordFails(): void
    {
        $client = static::createClient();

        $identity = $this->generateIdentity();

        $client->request(
            Request::METHOD_POST,
            '/api/register',
            server: $this->buildHeaders(),
            content: json_encode([
                'firstname' => $identity['firstname'],
                'lastname' => $identity['lastname'],
                'email' => $identity['email'],
            ])
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
        $this->assertEquals('The password is required', $error['reason']);
    }
}
