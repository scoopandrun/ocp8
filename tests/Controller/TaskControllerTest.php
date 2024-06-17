<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    use ClientTrait, TasksTrait, UsersTrait;

    public function testTasksPageIsUp(): void
    {
        // Given
        [$client] = $this->getAuthenticatedClient('User1', followRedirects: false);

        // When
        $client->request('GET', '/tasks');

        // Then
        $this->assertResponseStatusCodeSame(200);
    }

    public function testUnauthenticatedAccessReturnsUnauthorizedResponse(): void
    {
        // Given
        $client = $this->getUnauthenticatedClient(followRedirects: false);

        // When
        $client->request('GET', '/tasks/create');

        // Then
        $this->assertResponseStatusCodeSame(401);
    }

    public function testTaskCanBeCreated(): void
    {
        // Given
        [$client] = $this->getAuthenticatedClient('User1', followRedirects: false);
        $task = $this->createRandomTask(persist: false);

        // When
        $crawler = $client->request('GET', '/tasks/create');
        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = (string) $task->getTitle();
        $form['task[content]'] = (string) $task->getContent();
        $client->submit($form);

        // Then
        $this->assertResponseRedirects('/tasks');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été ajoutée.');
        $this->assertSelectorTextContains('body', $task->getTitle());
        $this->assertSelectorTextContains('body', $task->getContent());
    }

    public function testTaskCanBeToggledDone(): void
    {
        // Given
        [$client, $user] = $this->getAuthenticatedClient('User1', followRedirects: false);
        $task = $this->createRandomTask($user, persist: true);

        // When
        $crawler = $client->request('GET', '/tasks');
        $toggleForm = $crawler->filter("#task-{$task->getId()}-toggle")->form();
        $client->submit($toggleForm);

        // Then
        $this->assertResponseRedirects('/tasks');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "La tâche {$task->getTitle()} a bien été marquée comme faite.");
        // Check if the task is marked as done
        $icon = $crawler->filter("#task-{$task->getId()}-icon");
        $this->assertCount(1, $icon->filter('i.bi-check'));
        $this->assertCount(0, $icon->filter('i.bi-x'));
    }

    public function testTaskCanBeToggledUndone(): void
    {
        // Given
        [$client, $user] = $this->getAuthenticatedClient('User1', followRedirects: false);
        $task = $this->createRandomTask($user, true, persist: true);

        // When
        $crawler = $client->request('GET', '/tasks');
        $toggleForm = $crawler->filter("#task-{$task->getId()}-toggle")->first()->form();
        $client->submit($toggleForm);

        // Then
        $this->assertResponseRedirects('/tasks');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', "La tâche {$task->getTitle()} a bien été marquée comme non terminée.");
        // Check if the task is marked as undone
        $icon = $crawler->filter("#task-{$task->getId()}-icon");
        $this->assertCount(0, $icon->filter('i.bi-check'));
        $this->assertCount(1, $icon->filter('i.bi-x'));
    }

    public function testTaskCanBeEdited(): void
    {
        // Given
        [$client, $user] = $this->getAuthenticatedClient('User1', followRedirects: false);
        $task = $this->createRandomTask($user, persist: true);
        $editedTaskTitle = $task->getTitle() . ' edited';
        $editedTaskContent = $task->getContent() . ' edited';

        // When
        $crawler = $client->request('GET', "/tasks/{$task->getId()}/edit");
        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]'] = $editedTaskTitle;
        $form['task[content]'] = $editedTaskContent;
        $client->submit($form);

        // Then
        $this->assertResponseRedirects('/tasks');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextContains('.alert-success', 'La tâche a bien été modifiée.');
        $this->assertSelectorTextContains('body', $editedTaskTitle);
        $this->assertSelectorTextContains('body', $editedTaskContent);
    }

    public function testTaskCanNotBeDeletedByOtherUser(): void
    {
        // Given
        [$client] = $this->getAuthenticatedClient('User2', followRedirects: false);
        $task = $this->createRandomTask($this->getUser('User1'), persist: true);

        // When
        $client->request('DELETE', "/tasks/{$task->getId()}");

        // Then
        $this->assertResponseStatusCodeSame(403);
    }

    public function testTaskCanBeDeletedByAuthor(): void
    {
        // Given
        [$client, $user] = $this->getAuthenticatedClient('User1', followRedirects: false);
        $task = $this->createRandomTask($user, persist: true);

        // When
        $crawler = $client->request('GET', '/tasks');
        $deleteForm = $crawler->filter("#task-{$task->getId()}-delete")->first()->form();
        $client->submit($deleteForm);

        // Then
        $this->assertResponseRedirects('/tasks');
        $crawler = $client->followRedirect();
        $this->assertSelectorTextNotContains('body', $task->getTitle());
    }

    public function testAnonymousTaskCannotBeDeletedByOtherUser(): void
    {
        // Given
        [$client] = $this->getAuthenticatedClient('User2', followRedirects: false);
        $task = $this->createRandomTask(persist: true);

        // When
        $client->request('DELETE', "/tasks/{$task->getId()}");

        // Then
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAnonymousTaskCanBeDeletedByAdmin(): void
    {
        // Given
        $client = $this->getAdminClient(followRedirects: false);
        $task = $this->createRandomTask(persist: true);

        // When
        $client->request('DELETE', "/tasks/{$task->getId()}");

        // Then
        $this->assertResponseRedirects('/tasks');
    }

    public function testTaskPageWithNoFilterShowsAllTasks(): void
    {
        // Given
        [$client, $user] = $this->getAuthenticatedClient('User1', followRedirects: false);
        $doneTask = $this->createRandomTask($user, true, persist: true);
        $undoneTask = $this->createRandomTask($user, false, persist: true);

        // When
        $client->request('GET', '/tasks');

        // Then
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('body', $doneTask->getTitle());
        $this->assertSelectorTextContains('body', $undoneTask->getTitle());
    }

    public function testTaskPageWithFilterDoneOnlyShowsDoneTasks(): void
    {
        // Given
        [$client, $user] = $this->getAuthenticatedClient('User1', followRedirects: false);
        $doneTask = $this->createRandomTask($user, true, persist: true);
        $undoneTask = $this->createRandomTask($user, false, persist: true);

        // When
        $client->request('GET', '/tasks?done=1');

        // Then
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('body', $doneTask->getTitle());
        $this->assertSelectorTextNotContains('body', $undoneTask->getTitle());
    }

    public function testTaskPageWithFilterUndoneOnlyShowsUndoneTasks(): void
    {
        // Given
        [$client, $user] = $this->getAuthenticatedClient('User1', followRedirects: false);
        $doneTask = $this->createRandomTask($user, true, persist: true);
        $undoneTask = $this->createRandomTask($user, false, persist: true);

        // When
        $client->request('GET', '/tasks?done=0');

        // Then
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextNotContains('body', $doneTask->getTitle());
        $this->assertSelectorTextContains('body', $undoneTask->getTitle());
    }

    public function testTaskListByUserPageOnlyShowsTasksByUser(): void
    {
        // Given
        [$client, $user1] = $this->getAuthenticatedClient('User1', followRedirects: false);
        $user2 = $this->getUser('User2');
        $user1Task = $this->createRandomTask($user1, persist: true);
        $user2Task = $this->createRandomTask($user2, persist: true);

        // When
        $client->request('GET', "/tasks/user/{$user1->getId()}");

        // Then
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorTextContains('body', $user1Task->getTitle());
        $this->assertSelectorTextNotContains('body', $user2Task->getTitle());
    }

    public function testDeletingATaskFromListByUserPageRedirectsToSamePage(): void
    {
        // Given
        [$client, $user] = $this->getAuthenticatedClient('User1', followRedirects: false);
        $task = $this->createRandomTask($user, persist: true);

        // When
        $crawler = $client->request('GET', "/tasks/user/{$user->getId()}");
        $deleteForm = $crawler->filter("#task-{$task->getId()}-delete")->first()->form();
        $client->submit($deleteForm);

        // Then
        $this->assertResponseRedirects("/tasks/user/{$user->getId()}");
    }
}
