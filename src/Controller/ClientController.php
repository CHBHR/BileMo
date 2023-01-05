<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Attributes as OA;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;

class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'api_client', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Renvois la liste des clients',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: ['getClients']))
        )
    )]
    #[OA\Tag(name: 'Client')]
    #[Cache(expires: 'tomorrow', public: true)]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour consulter la liste des clients')]
    public function getClientList(ClientRepository $clientRepository, SerializerInterface $serializer,TagAwareCacheInterface $cache, Request $request): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 3);

        $idCache = "getClientsList-" . $page . "-" . $limit;
        $context = SerializationContext::create()->setGroups(['getClients']);

        $jsonPhoneList = $cache->get(
            $idCache, 
            function (ItemInterface $item) use ($clientRepository, $page, $limit, $serializer, $context) {
                $item->tag("clientsCache");
                $phoneList = $clientRepository->findAllWithPagination($page, $limit);
                return $serializer->serialize($phoneList, 'json',$context);
            });

        return new JsonResponse($jsonPhoneList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{id}', name: 'api_detailClient', methods: ['GET'])]
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
    public function getDetailClient(Client $client, SerializerInterface $serializer): JsonResponse 
    {
        $jsonClient = $serializer->serialize($client, 'json', [ 'groups' => 'getClients']);
        return new JsonResponse($jsonClient, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/clients/{id}', name: 'api_deleteClient', methods:['DELETE'])]
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
    public function deleteClient(Client $client, ManagerRegistry $doctrine): JsonResponse
    {
            $em = $doctrine->getManager();
            $em->remove($client);
            $em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/clients', name:'api_createClient', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour créer un client')]
    #[OA\Response(
        response: 201,
        description: 'Créer un Client',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Client::class, groups: []))
        )
    )]
    #[OA\Tag(name: 'Client')]
    public function createClient(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $client = $serializer->deserialize($request->getContent(), Client::class, 'json');

        // Vérif. des erreurs
        $errors = $validator->validate($client);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($client);
        $em->flush();

        $jsonClient = $serializer->serialize($client, 'json');

        return new JsonResponse($jsonClient, Response::HTTP_CREATED);
    }

}
