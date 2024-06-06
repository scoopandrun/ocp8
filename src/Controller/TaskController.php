<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/tasks", name="task")
 */
class TaskController extends AbstractController
{
    /**
     * @Route("", name=".list", methods={"GET"})
     */
    public function list(EntityManagerInterface $entityManager): Response
    {
        $tasks = $entityManager->getRepository(Task::class)->findAll();
        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }

    /**
     * @Route("/create", name=".create", methods={"GET", "POST"})
     */
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task.list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{id}/edit", name=".edit", methods={"GET", "POST"})
     */
    public function edit(Task $task, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task.list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/{id}/toggle", name=".toggle", methods={"GET"})
     */
    public function toggle(Task $task, EntityManagerInterface $entityManager): Response
    {
        $task->toggle(!$task->isDone());
        $entityManager->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task.list');
    }

    /**
     * @Route("/{id}/delete", name=".delete", methods={"GET"})
     */
    public function delete(Task $task, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($task);
        $entityManager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task.list');
    }
}
