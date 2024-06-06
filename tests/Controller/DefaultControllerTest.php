<?php

namespace Tests\App\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testUnauthenticatedIndexReturnsUnauthorizedResponse(): void
    {
        // Given
        $client = static::createClient();
        $method = 'GET';
        $url = '/';

        // When
        $client->request($method, $url);

        // Then
        $this->assertResponseStatusCodeSame(401);
    }

    public function testAuthenticatedIndexReturns200(): void
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'User1',
            'PHP_AUTH_PW' => 'pass123',
        ]);
        $method = 'GET';
        $url = '/';

        // When
        $client->request($method, $url);

        // Then
        $this->assertResponseStatusCodeSame(200);
    }
}
