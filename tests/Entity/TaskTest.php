<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testGetId(): void
    {
        // Given
        $task = new Task();

        // Then
        $this->assertNull($task->getId());
    }

    public function testGetCreatedAt(): void
    {
        // Given
        $task = new Task();

        // Then
        $this->assertInstanceOf(\DateTime::class, $task->getCreatedAt());
    }

    public function testSetCreatedAt(): void
    {
        // Given
        $task = new Task();
        $createdAt = new \DateTime();

        // When
        $task->setCreatedAt($createdAt);

        // Then
        $this->assertEquals($createdAt, $task->getCreatedAt());
    }

    public function testGetTitle(): void
    {
        // Given
        $task = new Task();

        // Then
        $this->assertNull($task->getTitle());
    }

    public function testSetTitle(): void
    {
        // Given
        $task = new Task();
        $title = 'Test Title';

        // When
        $task->setTitle($title);

        // Then
        $this->assertEquals($title, $task->getTitle());
    }

    public function testGetContent(): void
    {
        // Given
        $task = new Task();

        // Then
        $this->assertNull($task->getContent());
    }

    public function testSetContent(): void
    {
        // Given
        $task = new Task();
        $content = 'Test Content';

        // When
        $task->setContent($content);

        // Then
        $this->assertEquals($content, $task->getContent());
    }

    public function testIsDone(): void
    {
        // Given
        $task = new Task();

        // Then
        $this->assertFalse($task->isDone());
    }

    public function testToggle(): void
    {
        // Given
        $task = new Task();

        // When
        $task->toggle(true);

        // Then
        $this->assertTrue($task->isDone());
    }
}
