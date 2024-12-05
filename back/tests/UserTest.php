<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * User API Test Suite
 * 
 * Tests the user registration and authentication endpoints
 */
class UserTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    /**
     * Test user creation and immediate login
     * 
     * This test:
     * 1. Creates a new user via the API
     * 2. Verifies the response format and content
     * 3. Attempts to login with the created credentials
     * 
     * @return void
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

        $this->assertMatchesJsonSchema('{
              "$schema": "http://json-schema.org/draft-04/schema#",
              "type": "object",
              "properties": {
                "@context": {
                  "type": "string"
                },
                "@id": {
                  "type": "string"
                },
                "@type": {
                  "type": "string"
                },
                "email": {
                  "type": "string"
                },
                "username": {
                  "type": "string"
                },
                "firstname": {
                  "type": "string"
                }
              },
              "required": [
                "@context",
                "@id",
                "@type",
                "email",
                "username",
                "firstname"
              ]
            }'
        );

        $responseData = json_decode($response->getContent());
        $this->assertTrue($responseData->email === $data['email']);
        $this->assertTrue($responseData->username === $data['username']);
        $this->assertTrue($responseData->firstname === $data['firstname']);

        $this->loginSuccess($data);
    }

    /**
     * Attempts to login with given credentials
     * 
     * Verifies:
     * 1. Successful authentication
     * 2. JWT token is returned
     * 3. Response format is correct
     * 
     * @param array{email: string, password: string, username: string, firstname: string} $data User credentials
     * @return void
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
        
        $this->assertMatchesJsonSchema('{
              "$schema": "http://json-schema.org/draft-04/schema#",
              "type": "object",
              "properties": {
                "token": {
                  "type": "string"
                }
              },
              "required": [
                "token"
              ]
            }'
        );
    }
}
