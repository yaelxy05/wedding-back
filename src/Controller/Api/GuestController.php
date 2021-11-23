<?php

namespace App\Controller\Api;

use App\Entity\Guest;
use App\Repository\GuestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GuestController extends AbstractController
{
    /**
     * @Route("/api/guest", name="api_guest")
     */
    public function guest(GuestRepository $guestRepository): Response
    {
        $guests = $guestRepository->findAll();

        return $this->json($guests, 200, [], ['groups' => 'guest_read']);
    }

    /**
     * @Route("/api/guest/{id<\d+>}", name="api_guest_read", methods="GET")
     */
    public function guestRead(Guest $guest = null): Response
    {
        // 404 error page
        if ($guest === null) {

            // Optional, message for the front
            $message = [
                'status' => Response::HTTP_NOT_FOUND,
                'error' => 'Désolé cet invité n\'existe pas.',
            ];

            // We define a custom message and an HTTP 404 status code
            return $this->json($message, Response::HTTP_NOT_FOUND);
        }

        // The 4th argument represents the "context" which will be transmitted to the serializer
        return $this->json($guest, 200, [], ['groups' => 'guest_read']);
    }

    /**
     * @Route("/api/guest/create", name="api_guest_create", methods="POST")
     */
    public function guestCreate(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        // Retrieve the content of the request, i.e. the JSON
        $jsonContent = $request->getContent();

        // We deserialize this JSON into a Movie entity, thanks to the Serializer
        // We transform the JSON into an object of type App\Entity\Guest
        $guest = $serializer->deserialize($jsonContent, Guest::class, 'json');

        // If linked objects (Users) they will be validated if @Valid annotation
        // present on the $user property of the Guest class
        $errors = $validator->validate($guest);

        if (count($errors) > 0) {

            // The array of errors is returned as JSON with a status of 422
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $this->getUser();
        $guest->setUser($user);

        // We save the guest
        $entityManager->persist($guest);
        $entityManager->flush();

        // We redirect to guest_read
        return $this->json($guest, 200, [], ['groups' => 'guest_read'], Response::HTTP_CREATED);

    }

    /**
     * @Route("/api/guest/update/{id<\d+>}", name="api_guest_update")
     */
    public function guestUpdate(Guest $guest = null, EntityManagerInterface $em, SerializerInterface $serializer, Request $request, ValidatorInterface $validator): Response
    {
        // We want to modify the guest whose id is transmitted via the URL

        // 404 ?
        if ($guest === null) {
            // We return a JSON message + a 404 status
            return $this->json(['error' => 'L\'invité n\'a pas été trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Our JSON which is in the body
        $jsonContent = $request->getContent();

        /* We will have to associate the JSON data received on the existing entity
        We deserialize the data received from the front ($ request-> getContent ()) ...
        ... in the Guest object to modify */
        $serializer->deserialize(
            $jsonContent,
            Guest::class,
            'json',
            // We have this additional argument which tells the serializer which existing entity to modify
            [AbstractNormalizer::OBJECT_TO_POPULATE => $guest]
        );

        // Validation of the deserialized entity
        $errors = $validator->validate($guest);
        // Generating errors
        if (count($errors) > 0) {
            // We return the error table in Json to the front with a status code 422
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // On flush $guest which has been modified by the Serializer
        $em->flush();

        // Condition the return message in case the entity is not modified
        return $this->json(['message' => 'L\'invité a été modifié.'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/guest/delete/{id<\d+>}", name="api_guest_delete")
     */
    public function guestDelete(Guest $guest = null, EntityManagerInterface $entityManager): Response
    {
        // 404
        if ($guest === null) {

            $message = [
                'status' => Response::HTTP_NOT_FOUND,
                'error' => 'Invité non trouvé.',
            ];

            // We define a custom message and an HTTP 404 status code
            return $this->json($message, Response::HTTP_NOT_FOUND);
        }

        // Otherwise we delete in base
        $entityManager->remove($guest);
        $entityManager->flush();

        // The $guest object still exists in PHP memory until the end of the script
        return $this->json(
            ['message' => 'L\'invité ' . $guest->getFirstname() . ' a été supprimé !'],
            Response::HTTP_OK);
    }
}
