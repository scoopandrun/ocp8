<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TaskControllerTest extends WebTestCase
{
    private $user1;

    public function setUp()
    {
        $this->user1 = [
            'username' => 'User1',
            'password' => 'pass123',
        ];
    }

    public function testTasksPageIsUp()
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user1['username'],
            'PHP_AUTH_PW' => $this->user1['password'],
        ]);

        // When
        $client->request('GET', '/tasks');

        // Then
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testUnauthenticatedAccessToTasksRedirectsToLogin()
    {
        // Given
        $client = static::createClient();

        // When
        $client->request('GET', '/tasks/create');
        $response = $client->getResponse();

        // Then
        $this->assertInstanceOf(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $this->assertEquals('http://localhost/login', $response->getTargetUrl());
    }

    /**
     * @return int ID of the created task.
     */
    public function testTaskCanBeCreated()
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user1['username'],
            'PHP_AUTH_PW' => $this->user1['password'],
        ]);
        $taskTitle = 'Test task' . uniqid();
        $taskContent = 'Test task content';

        // When
        $crawler = $client->request('GET', '/tasks/create');
        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = $taskTitle;
        $form['task[content]'] = $taskContent;
        $client->submit($form);
        $response = $client->getResponse();

        // Then
        $this->assertInstanceOf(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $this->assertEquals('/tasks', $response->getTargetUrl());
        $crawler = $client->followRedirect();
        $this->assertContains($taskTitle, $crawler->filter('body')->text());
        $this->assertContains($taskContent, $crawler->filter('body')->text());
        $this->assertContains('La tâche a été bien été ajoutée.', $crawler->filter('body')->text());

        // Get the ID of the created task
        $taskId = preg_replace('/[^0-9]/', '', $crawler->filter("a:contains('{$taskTitle}')")->attr('href'));

        // Return info of the created task
        return [
            'title' => $taskTitle,
            'content' => $taskContent,
            'id' => $taskId,
        ];
    }

    /**
     * @depends testTaskCanBeCreated
     * 
     * @param array $taskInfo Info of the task to edit.
     */
    public function testTaskCanBeEdited($taskInfo)
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user1['username'],
            'PHP_AUTH_PW' => $this->user1['password'],
        ]);
        $taskId = $taskInfo['id'];
        $taskTitle = $taskInfo['title'];
        $taskContent = $taskInfo['content'];
        $editedTaskTitle = $taskTitle . ' edited';
        $editedTaskContent = $taskContent . ' edited';

        // When
        $crawler = $client->request('GET', "/tasks/{$taskId}/edit");
        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]'] = $editedTaskTitle;
        $form['task[content]'] = $editedTaskContent;
        $client->submit($form);
        $response = $client->getResponse();

        // Then
        $this->assertInstanceOf(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $this->assertEquals('/tasks', $response->getTargetUrl());
        $crawler = $client->followRedirect();
        $this->assertContains($editedTaskTitle, $crawler->filter('body')->text());
        $this->assertContains($editedTaskContent, $crawler->filter('body')->text());
        $this->assertContains('La tâche a bien été modifiée.', $crawler->filter('body')->text());
    }

    /**
     * @depends testTaskCanBeCreated
     * 
     * @param array $taskId Info of the task to toggle.
     */
    public function testTaskCanBeToggledDone($taskInfo)
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user1['username'],
            'PHP_AUTH_PW' => $this->user1['password'],
        ]);
        $taskId = $taskInfo['id'];

        // When
        $crawler = $client->request('GET', '/tasks');
        $taskTitle = $crawler->filter("a[href='/tasks/{$taskId}/edit']")->text();
        $toggleForm = $crawler->filter("form[action='/tasks/{$taskId}/toggle']")->first()->form();
        $client->submit($toggleForm);
        $response = $client->getResponse();

        // Then
        $this->assertInstanceOf(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $this->assertEquals('/tasks', $response->getTargetUrl());
        $crawler = $client->followRedirect();
        $this->assertContains("La tâche {$taskTitle} a bien été marquée comme faite.", $crawler->filter('body')->text());
        // Check if the task is marked as done
        $checkSpan = $crawler
            ->filter("a[href='/tasks/{$taskId}/edit']")
            ->first()
            ->parents()
            ->eq(0)
            ->siblings();
        $this->assertCount(1, $checkSpan->filter('span.glyphicon-ok'));
        $this->assertCount(0, $checkSpan->filter('span.glyphicon-remove'));
    }

    /**
     * @depends testTaskCanBeCreated
     * 
     * @param array $taskId Info of the task to toggle.
     */
    public function testTaskCanBeToggledUndone($taskInfo)
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user1['username'],
            'PHP_AUTH_PW' => $this->user1['password'],
        ]);
        $taskId = $taskInfo['id'];

        // When
        $crawler = $client->request('GET', '/tasks');
        $taskTitle = $crawler->filter("a[href='/tasks/{$taskId}/edit']")->text();
        $toggleForm = $crawler->filter("form[action='/tasks/{$taskId}/toggle']")->first()->form();
        $client->submit($toggleForm);
        $response = $client->getResponse();

        // Then
        $this->assertInstanceOf(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $this->assertEquals('/tasks', $response->getTargetUrl());
        $crawler = $client->followRedirect();
        // $this->assertContains("La tâche {$taskTitle} a bien été marquée comme non terminée.", $crawler->filter('body')->text());
        // Check if the task is marked as undone
        $checkSpan = $crawler
            ->filter("a[href='/tasks/{$taskId}/edit']")
            ->first()
            ->parents()
            ->eq(0)
            ->siblings();
        $this->assertCount(0, $checkSpan->filter('span.glyphicon-ok'));
        $this->assertCount(1, $checkSpan->filter('span.glyphicon-remove'));
    }

    /**
     * @depends testTaskCanBeCreated
     * 
     * @param array $taskId Info of the task to delete.
     */
    public function testTaskCanBeDeleted($taskInfo)
    {
        // Given
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->user1['username'],
            'PHP_AUTH_PW' => $this->user1['password'],
        ]);
        $taskId = $taskInfo['id'];

        // When
        $crawler = $client->request('GET', '/tasks');
        $taskTitle = $crawler->filter("a[href='/tasks/{$taskId}/edit']")->text();
        $deleteForm = $crawler->filter("form[action='/tasks/{$taskId}/delete']")->first()->form();
        $client->submit($deleteForm);
        $response = $client->getResponse();

        // Then
        $this->assertInstanceOf(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $this->assertEquals('/tasks', $response->getTargetUrl());
        $crawler = $client->followRedirect();
        $this->assertNotContains($taskTitle, $crawler->filter('body')->text());
    }
}
