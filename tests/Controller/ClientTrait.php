<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait ClientTrait
{
    use UsersTrait;

    private ?KernelBrowser $adminClient = null;

    private function getUnauthenticatedClient(bool $followRedirects = true): KernelBrowser
    {
        $client = static::createClient();

        if ($followRedirects) {
            $client->followRedirects();
        }

        return $client;
    }

    /**
     * @return array{KernelBrowser, User}
     */
    private function getAuthenticatedClient(string $username, bool $followRedirects = true): array
    {
        $client = $this->getUnauthenticatedClient($followRedirects);
        $user = $this->getUser($username);
        $client->loginUser($user);

        return [$client, $user];
    }

    private function getAdminClient(bool $followRedirects = true): KernelBrowser
    {
        if (null === $this->adminClient) {
            $this->adminClient = $this->getUnauthenticatedClient($followRedirects);
            $this->adminClient->loginUser($this->getUser('Admin'));
        }

        return $this->adminClient;
    }
}
