<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    public function testCreateUserSuccess()
    {
        $client = static::createClient();

        $client->request('POST', '/api/users', [
            'json' => [
                'email' => 'user@example.com',
                'username' => 'exampleUser',
                'firstname' => 'Example',
                'password' => 'my_secure_password',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
              "@context"  => "/api/contexts/User",
              "@id"       => "/api/users/1",
              "@type"     => "User",
              "email"     => "user@example.com",
              "username"  => "exampleUser",
              "firstname" => "Example",
        ]);
    }

    public function testLoginSuccess()
    {
        $client = static::createClient();

        $client->request('POST', '/api/login', [
            'json' => [
                'email'    => 'user@example.com',
                'password' => 'my_secure_password',
            ],
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
