<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\UserFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use App\Factory\ProductFactory;

/**
 * Product API Test Suite
 * 
 * Tests all CRUD operations on products with both authorized and unauthorized users
 */
class ProductTest extends ApiTestCase
{
    use ResetDatabase, Factories;

    private string $schemaDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schemaDir = __DIR__ . '/schemas/';
    }

    /**
     * Creates an authenticated client with JWT token
     * 
     * @param string $email The user's email (defaults to admin@admin.com)
     * @param string $contentType The Content-Type header value
     * @return \ApiPlatform\Symfony\Bundle\Test\Client The authenticated API Platform test client
     */
    private function createAuthenticatedClient(string $email = 'admin@admin.com', string $contentType = 'application/ld+json'): \ApiPlatform\Symfony\Bundle\Test\Client
    {
        $user = UserFactory::createOne([
            'email'     => $email,
            'password'  => 'password',
            'firstname' => 'admin',
            'username'  => 'admin'
        ]);

        $jwtManager = static::getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $token = $jwtManager->create($user);

        return static::createClient([], ['headers' => ['Authorization' => 'Bearer '.$token, 'Content-Type' => $contentType]]);
    }

    /**
     * Tests successful product creation by admin user
     * 
     * Verifies that:
     * 1. Admin can create a product
     * 2. Response is successful
     * 3. Response matches JSON schema
     */
    public function testSuccessProductCreation(): void
    {
        $data = [
            "code"        => "string",
            "name"        => "string",
            "description" => "string",
            "image"       => "string",
            "category"    => "string",
            "price"       => 60,
            "quantity"    => 10,
            "internalReference" => "string",
            "shellId"     => 0,
            "inventoryStatus" => "INSTOCK",
            "rating"      => 0
        ];

        $this->createAuthenticatedClient()
            ->request('POST', '/api/products', [
                'json' => $data
            ]);

        $this->assertResponseIsSuccessful();
        $this->assertMatchesJsonSchema(
            json_decode(file_get_contents($this->schemaDir . 'product.json'))
        );
    }

    /**
     * Tests failed product creation by non-admin user
     * 
     * Verifies that:
     * 1. Non-admin user cannot create a product
     * 2. Response is 403 Forbidden
     */
    public function testFailedProductCreation(): void
    {
        $data = [
            "code" => "string",
            "name" => "string",
            "description" => "string",
            "image" => "string",
            "category" => "string",
            "price" => 0,
            "quantity" => 0,
            "internalReference" => "string",
            "shellId" => 0,
            "inventoryStatus" => "INSTOCK",
            "rating" => 0,
            "createdAt" => "2024-12-05T21:08:46.365Z",
            "updatedAt" => "2024-12-05T21:08:46.365Z",
        ];

        $this->createAuthenticatedClient('user@gmail.com')
            ->request('POST', '/api/products', [
                'json' => $data,
            ]);

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Tests successful product update by admin user
     * 
     * Verifies that:
     * 1. Admin can update a product
     * 2. Response is successful
     * 3. Response matches JSON schema
     */
    public function testSuccessUpdateProduct(): void
    {
        $productFactory = ProductFactory::createOne();

        $data = [
            "code"        => "string",
            "name"        => "string",
            "description" => "string",
            "image"       => "string",
            "category"    => "string",
            "price"       => 60,
            "quantity"    => 10,
            "internalReference" => "string",
            "shellId"     => 0,
            "inventoryStatus" => "INSTOCK",
            "rating"      => 0
        ];

        $this->createAuthenticatedClient(contentType: 'application/merge-patch+json')
            ->request('PATCH', '/api/products/'.$productFactory->getId(), [
                'json' => $data
            ]);

        $this->assertResponseIsSuccessful();
        $this->assertMatchesJsonSchema(
            json_decode(file_get_contents($this->schemaDir . 'product.json'))
        );
    }

    /**
     * Tests failed product update by non-admin user
     * 
     * Verifies that:
     * 1. Non-admin user cannot update a product
     * 2. Response is 403 Forbidden
     */
    public function testFailedUpdateProduct(): void
    {
        $productFactory = ProductFactory::createOne();

        $data = [
            "code" => "string",
            "name" => "string",
            "description" => "string",
            "image" => "string",
            "category" => "string",
            "price" => 0,
            "quantity" => 0,
            "internalReference" => "string",
            "shellId" => 0,
            "inventoryStatus" => "INSTOCK",
            "rating" => 0,
            "createdAt" => "2024-12-05T21:08:46.365Z",
            "updatedAt" => "2024-12-05T21:08:46.365Z",
        ];

        $this->createAuthenticatedClient(email:'user@gmail.com', contentType: 'application/merge-patch+json')
            ->request('PATCH', '/api/products/'.$productFactory->getId(), [
                'json' => $data
            ]);

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Tests successful product deletion by admin user
     * 
     * Verifies that:
     * 1. Admin can delete a product
     * 2. Response is successful
     */
    public function testSuccessDeleteProduct(): void
    {
        $productFactory = ProductFactory::createOne();

        $this->createAuthenticatedClient(contentType: 'application/json')
            ->request('DELETE', '/api/products/'.$productFactory->getId());

        $this->assertResponseIsSuccessful();
    }

    /**
     * Tests failed product deletion by non-admin user
     * 
     * Verifies that:
     * 1. Non-admin user cannot delete a product
     * 2. Response is 403 Forbidden
     */
    public function testFailedDeleteProduct(): void
    {
        $productFactory = ProductFactory::createOne();

        $this->createAuthenticatedClient(email:'user@gmail.com', contentType: 'application/json')
            ->request('DELETE', '/api/products/'.$productFactory->getId());

        $this->assertResponseStatusCodeSame(403);
    }

    /**
     * Tests successful retrieval of products list
     * 
     * Verifies that:
     * 1. Products list can be retrieved
     * 2. Response is successful (200)
     */
    public function testSuccessProductsList(): void
    {
        ProductFactory::createMany(10);

        $this->createAuthenticatedClient()
            ->request('GET', '/api/products');

        $this->assertResponseStatusCodeSame(200);
    }
}
