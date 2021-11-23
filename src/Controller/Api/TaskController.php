<?php

namespace App\Controller\Api;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
/**
     * @Route("/api/tasks", name="api_task")
     */
    public function task(TaskRepository $taskRepository): Response
    {
        $user = $this->getUser();
        $tasks = $taskRepository->findTaskForOneUser($user);

        return $this->json($tasks, 200, [], ['groups' => 'task_read']);
    }

    /**
     * @Route("/api/task/{id<\d+>}", name="api_task_read", methods="GET")
     */
    public function taskRead(Task $task = null): Response
    {
        // 404 error page
        if ($task === null) {

            // Optional, message for the front
            $message = [
                'status' => Response::HTTP_NOT_FOUND,
                'error' => 'Désolé ce prestataire n\'existe pas.',
            ];

            // We define a custom message and an HTTP 404 status code
            return $this->json($message, Response::HTTP_NOT_FOUND);
        }

        // The 4th argument represents the "context" which will be transmitted to the serializer
        return $this->json($task, 200, [], ['groups' => 'task_read']);
    }

    /**
     * @Route("/api/task/create", name="api_task_create", methods="POST")
     */
    public function taskCreate(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        // Retrieve the content of the request, i.e. the JSON
        $jsonContent = $request->getContent();

        // We deserialize this JSON into a Task entity, thanks to the Serializer
        // We transform the JSON into an object of type App\Entity\Task
        $task = $serializer->deserialize($jsonContent, Task::class, 'json');

        // If linked objects (Users) they will be validated if @Valid annotation
        // present on the $user property of the Task class
        $errors = $validator->validate($task);

        if (count($errors) > 0) {

            // The array of errors is returned as JSON with a status of 422
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $this->getUser();
        $task->setStatus(1);
        $task->setUser($user);

        // We save the task
        $entityManager->persist($task);
        $entityManager->flush();

        // We redirect to task_read
        return $this->json($task, 200, [], ['groups' => 'task_read'], Response::HTTP_CREATED);

    }

    /**
     * @Route("/api/task/update/{id<\d+>}", name="api_task_update")
     */
    public function taskUpdate(Task $task = null, EntityManagerInterface $em, SerializerInterface $serializer, Request $request, ValidatorInterface $validator): Response
    {
        // We want to modify the task whose id is transmitted via the URL

        // 404 ?
        if ($task === null) {
            // We return a JSON message + a 404 status
            return $this->json(['error' => 'La tâche n\'a pas été trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Our JSON which is in the body
        $jsonContent = $request->getContent();

        /* We will have to associate the JSON data received on the existing entity
        We deserialize the data received from the front ($ request-> getContent ()) ...
        ... in the Task object to modify */
        $serializer->deserialize(
            $jsonContent,
            Task::class,
            'json',
            // We have this additional argument which tells the serializer which existing entity to modify
            [AbstractNormalizer::OBJECT_TO_POPULATE => $task]
        );

        // Validation of the deserialized entity
        $errors = $validator->validate($task);
        // Generating errors
        if (count($errors) > 0) {
            // We return the error table in Json to the front with a status code 422
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // On flush $task which has been modified by the Serializer
        $em->flush();

        // Condition the return message in case the entity is not modified
        return $this->json(['message' => 'La tâche a été modifié.'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/task/delete/{id<\d+>}", name="api_task_delete")
     */
    public function taskDelete(Task $task = null, EntityManagerInterface $entityManager): Response
    {
        // 404
        if ($task === null) {

            $message = [
                'status' => Response::HTTP_NOT_FOUND,
                'error' => 'Tâche non trouvé.',
            ];

            // We define a custom message and an HTTP 404 status code
            return $this->json($message, Response::HTTP_NOT_FOUND);
        }

        // Otherwise we delete in base
        $entityManager->remove($task);
        $entityManager->flush();

        // The $task object still exists in PHP memory until the end of the script
        return $this->json(
            ['message' => 'La tâche ' . $task->getName() . ' a été supprimé !'],
            Response::HTTP_OK);
    }
}
