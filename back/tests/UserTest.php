<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * User API Test Suite
 */
class UserTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    private string $schemaDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schemaDir = __DIR__ . '/schemas/';
    }

    /**
     * Test user creation and immediate login
     */
    public function testCreateUserSuccess(): void
    {
        $client = static::createClient();

        $data = [
            'email' => 'user@example.com',
            'username' => 'exampleUser',
            'firstname' => 'Example',
            'password' => 'my_secure_password',
        ];

        $response = $client->request('POST', '/api/users', [
            'json' => $data,
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        
        $this->assertMatchesJsonSchema(
            json_decode(file_get_contents($this->schemaDir . 'user.json'))
        );

        $responseData = json_decode($response->getContent());
        $this->assertTrue($responseData->email === $data['email']);
        $this->assertTrue($responseData->username === $data['username']);
        $this->assertTrue($responseData->firstname === $data['firstname']);

        $this->loginSuccess($data);
    }

    /**
     * Attempts to login with given credentials
     */
    private function loginSuccess(array $data): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [
            'json' => [
                'email' => $data['email'],
                'password' => $data['password'],
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $this->assertMatchesJsonSchema(
            json_decode(file_get_contents($this->schemaDir . 'token.json'))
        );
    }
}
