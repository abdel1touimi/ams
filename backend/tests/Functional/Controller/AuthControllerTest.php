<?php

namespace App\Tests\Functional\Controller;

use App\Tests\Functional\TestTrait\FunctionalTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends WebTestCase
{
    use FunctionalTestTrait;

    public function testRegisterSuccess(): void
    {
        $userData = [
            'email' => 'newuser@example.com',
            'password' => 'Password123',
            'name' => 'New User'
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertTrue($content['success']);
        self::assertArrayHasKey('data', $content);
        self::assertEquals($userData['email'], $content['data']['email']);
        self::assertEquals($userData['name'], $content['data']['name']);
        self::assertArrayNotHasKey('password', $content['data']);
    }

    public function testRegisterWithInvalidData(): void
    {
        $invalidData = [
            'email' => 'invalid-email',
            'password' => 'weak',
            'name' => ''
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($invalidData)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertFalse($content['success']);
        self::assertArrayHasKey('data', $content);

        // Verify validation errors
        $errors = $content['data'];
        self::assertArrayHasKey('email', $errors);
        self::assertArrayHasKey('password', $errors);
        self::assertArrayHasKey('name', $errors);
    }

    public function testGetUserProfile(): void
    {
        $user = $this->createUser();
        $headers = $this->getAuthorizationHeader();

        $this->client->request(
            'GET',
            '/api/me',
            [],
            [],
            $headers
        );

        self::assertResponseIsSuccessful();

        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertTrue($content['success']);
        self::assertArrayHasKey('data', $content);
        self::assertEquals($user->getEmail(), $content['data']['email']);
        self::assertEquals($user->getName(), $content['data']['name']);
    }

    public function testUpdateProfile(): void
    {
        $this->createUser();
        $headers = $this->getAuthorizationHeader();

        $updateData = [
            'email' => 'updated@example.com',
            'name' => 'Updated Name'
        ];

        $this->client->request(
            'PUT',
            '/api/me',
            [],
            [],
            array_merge($headers, ['CONTENT_TYPE' => 'application/json']),
            json_encode($updateData)
        );

        self::assertResponseIsSuccessful();

        $content = json_decode($this->client->getResponse()->getContent(), true);
        self::assertTrue($content['success']);
        self::assertArrayHasKey('data', $content);
        self::assertEquals($updateData['email'], $content['data']['email']);
        self::assertEquals($updateData['name'], $content['data']['name']);
    }

    public function testChangePassword(): void
    {
        $initialPassword = 'Password123';
        $this->createUser(password: $initialPassword);
        $headers = $this->getAuthorizationHeader();

        $passwordData = [
            'current_password' => $initialPassword,
            'new_password' => 'NewPassword123',
            'confirm_password' => 'NewPassword123'
        ];

        $this->client->request(
            'PUT',
            '/api/me/password',
            [],
            [],
            array_merge($headers, ['CONTENT_TYPE' => 'application/json']),
            json_encode($passwordData)
        );

        self::assertResponseIsSuccessful();

        // Verify can login with new password
        $newHeaders = $this->getAuthorizationHeader(password: 'NewPassword123');
        self::assertArrayHasKey('HTTP_AUTHORIZATION', $newHeaders);
    }

    public function testUnauthorizedAccess(): void
    {
        $this->client->request('GET', '/api/me');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        // Get the response content
        $responseContent = $this->client->getResponse()->getContent();
        self::assertNotEmpty($responseContent);

        $content = json_decode($responseContent, true);
        self::assertIsArray($content);
        self::assertArrayHasKey('code', $content);
        self::assertEquals(Response::HTTP_UNAUTHORIZED, $content['code']);
    }

    public function testProtectedRouteWithInvalidToken(): void
    {
        $this->client->request(
            'GET',
            '/api/me',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer invalid.token.here']
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testProtectedRouteWithMissingToken(): void
    {
        $this->client->request(
            'GET',
            '/api/me',
            [],
            [],
            ['HTTP_AUTHORIZATION' => '']
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
