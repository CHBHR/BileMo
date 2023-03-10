<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Service\DeleteService;
use App\Service\GetAllService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class ClientController extends AbstractController
{
    #[OA\Response(
        response: 200,
        description: 'Renvois la liste des clients',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: ['getClients']))
        )
    )]
    #[OA\Tag(name: 'Client')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour consulter la liste des clients')]
    #[Route('/api/clients', name: 'api_client', methods: ['GET'])]
    public function getClientList(ClientRepository $clientRepository, Request $request, GetAllService $getAll): JsonResponse
    {
        $name = 'getClientList';
        $groups = ['getClients'];
        $tags = ['clientsCache'];

        return $getAll->getAll($name, $groups, $clientRepository, $tags, $request);
    }

    #[OA\Response(
        response: 200,
        description: 'Renvois le detail d\'un clients',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: []))
        )
    )]
    #[OA\Tag(name: 'Client')]
    #[Cache(expires: 'tomorrow', public: true)]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour consulter les informations d\'un clients')]
    #[Route('/api/clients/{id}', name: 'api_detailClient', methods: ['GET'])]
    public function getDetailClient(Client $client, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getClients']);
        $jsonClient = $serializer->serialize($client, 'json', $context);
        return new JsonResponse($jsonClient, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour supprimer un client')]
    #[OA\Response(
        response: 204,
        description: 'Supprime le client',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: []))
        )
    )]
    #[OA\Tag(name: 'Client')]
    #[Route('/api/clients/{id}', name: 'api_deleteClient', methods:['DELETE'])]
    public function deleteClient(Client $client, DeleteService $delete): JsonResponse
    {
        $cacheName = ["clientsCache"];
        $delete->delete($cacheName, $client);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour cr??er un client')]
    #[OA\Response(
        response: 201,
        description: 'Cr??er un Client',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: []))
        )
    )]
    #[OA\Tag(name: 'Client')]
    #[Route('/api/clients', name:'api_createClient', methods: ['POST'])]
    public function createClient(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $client = $serializer->deserialize($request->getContent(), Client::class, 'json');

        // V??rif. des erreurs
        $errors = $validator->validate($client);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($client);
        $em->flush();

        $jsonClient = $serializer->serialize($client, 'json');

        return new JsonResponse($jsonClient, Response::HTTP_CREATED, [],  true);
    }

    #[OA\Response(
        response: 204,
        description: 'Modifier un client',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: []))
            )
        )]
    #[OA\Tag(name: 'Client')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour modifier un client')]
    #[Route('/api/clients/{id}', name:'api_updateClient', methods:['PUT'])]
    public function updateClient(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $updatedClient = $serializer->deserialize($request->getContent(),
            Client::class,
            'json',
        );
            $em->persist($updatedClient);
            $em->flush();
            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
