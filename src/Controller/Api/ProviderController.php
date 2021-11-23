<?php

namespace App\Controller\Api;

use App\Entity\Provider;
use App\Repository\ProviderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProviderController extends AbstractController
{
    /**
     * @Route("/api/provider", name="api_provider")
     */
    public function provider(ProviderRepository $providerRepository): Response
    {
        $providers = $providerRepository->findAll();

        return $this->json($providers, 200, [], ['groups' => 'provider_read']);
    }

    /**
     * @Route("/api/provider/{id<\d+>}", name="api_provider_read", methods="GET")
     */
    public function providerRead(Provider $provider = null): Response
    {
        // 404 error page
        if ($provider === null) {

            // Optional, message for the front
            $message = [
                'status' => Response::HTTP_NOT_FOUND,
                'error' => 'Désolé ce prestataire n\'existe pas.',
            ];

            // We define a custom message and an HTTP 404 status code
            return $this->json($message, Response::HTTP_NOT_FOUND);
        }

        // The 4th argument represents the "context" which will be transmitted to the serializer
        return $this->json($provider, 200, [], ['groups' => 'provider_read']);
    }

    /**
     * @Route("/api/provider/create", name="api_provider_create", methods="POST")
     */
    public function providerCreate(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        // Retrieve the content of the request, i.e. the JSON
        $jsonContent = $request->getContent();

        // We deserialize this JSON into a Movie entity, thanks to the Serializer
        // We transform the JSON into an object of type App\Entity\Provider
        $provider = $serializer->deserialize($jsonContent, Provider::class, 'json');

        // If linked objects (Users) they will be validated if @Valid annotation
        // present on the $user property of the Provider class
        $errors = $validator->validate($provider);

        if (count($errors) > 0) {

            // The array of errors is returned as JSON with a status of 422
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // We save the provider
        $entityManager->persist($provider);
        $entityManager->flush();

        // We redirect to provider_read
        return $this->json($provider, 200, [], ['groups' => 'provider_read'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/provider/update/{id<\d+>}", name="api_provider_update")
     */
    public function providerUpdate(Provider $provider = null, EntityManagerInterface $em, SerializerInterface $serializer, Request $request, ValidatorInterface $validator): Response
    {
        // We want to modify the provider whose id is transmitted via the URL

        // 404 ?
        if ($provider === null) {
            // We return a JSON message + a 404 status
            return $this->json(['error' => 'Le prestataire n\'a pas été trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Our JSON which is in the body
        $jsonContent = $request->getContent();

        /* We will have to associate the JSON data received on the existing entity
        We deserialize the data received from the front ($ request-> getContent ()) ...
        ... in the Provider object to modify */
        $serializer->deserialize(
            $jsonContent,
            Provider::class,
            'json',
            // We have this additional argument which tells the serializer which existing entity to modify
            [AbstractNormalizer::OBJECT_TO_POPULATE => $provider]
        );

        // Validation of the deserialized entity
        $errors = $validator->validate($provider);
        // Generating errors
        if (count($errors) > 0) {
            // We return the error table in Json to the front with a status code 422
            return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // On flush $provider which has been modified by the Serializer
        $em->flush();

        // Condition the return message in case the entity is not modified
        return $this->json(['message' => 'Le prestataire a été modifié.'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/provider/delete/{id<\d+>}", name="api_provider_delete")
     */
    public function providerDelete(Provider $provider = null, EntityManagerInterface $entityManager): Response
    {
        // 404
        if ($provider === null) {

            $message = [
                'status' => Response::HTTP_NOT_FOUND,
                'error' => 'Invité non trouvé.',
            ];

            // We define a custom message and an HTTP 404 status code
            return $this->json($message, Response::HTTP_NOT_FOUND);
        }

        // Otherwise we delete in base
        $entityManager->remove($provider);
        $entityManager->flush();

        // The $provider object still exists in PHP memory until the end of the script
        return $this->json(
            ['message' => 'Le prestataire ' . $provider->getSocietyName() . ' a été supprimé !'],
            Response::HTTP_OK);
    }
}
