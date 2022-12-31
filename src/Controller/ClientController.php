<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Customer;
use App\Repository\ClientRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClientController extends AbstractController
{
    #[Route('/api/clients', name: 'api_client', methods: ['GET'])]
    public function getClientList(ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {
        $clientList = $clientRepository->findAll();
        $jsonClientList = $serializer->serialize($clientList, 'json', ['groups' => 'getClients']);

        return new JsonResponse($jsonClientList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{id}', name: 'api_detailClient', methods: ['GET'])]
    public function getDetailClient(Client $client, SerializerInterface $serializer): JsonResponse 
    {
        $jsonClient = $serializer->serialize($client, 'json', [ 'groups' => 'getClients']);
        return new JsonResponse($jsonClient, Response::HTTP_OK, ['accept' => 'json'], true);
    }

   #[Route('/api/clients/{id}', name: 'api_deleteClient', methods:['DELETE'])]
   #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour supprimer un client')]
   public function deleteClient(Client $client, ManagerRegistry $doctrine): JsonResponse
   {
        $em = $doctrine->getManager();
        $em->remove($client);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
   }

   #[Route('/api/clients', name:'api_createClient', methods: ['POST'])]
   #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour créer un client')]
   public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
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

    #[Route('api/clients/{clientId}/customers', name: 'api_clientCustomers', methods: ('GET'))]
    public function getClientCustomersList(int $clientId, SerializerInterface $serializer, ClientRepository $clientRepository, CustomerRepository $customerRepository)
    {
        $data = [];
        $client = $clientRepository->find($clientId);
        if ($client) {
            $customers = $customerRepository->findBy(['client' => $client]);
            $data[] = $client;
            if ($customers){
                $data[] = $customers;
                $jsonClientCustomers = $serializer->serialize($data, 'json', ['groups' => 'getClientCustomers']);
                return new JsonResponse($jsonClientCustomers, Response::HTTP_OK, [], true);
            }
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('api/clients/{clientId}/customers/{customerId}', name: 'api_clientCustomerDetail', methods: ('GET'))]
    public function getClientCustomersDetail(int $clientId, int $customerId, SerializerInterface $serializer, ClientRepository $clientRepository, CustomerRepository $customerRepository)
    {
        $client = $clientRepository->find($clientId);
        if ($client) {
            $customers = $customerRepository->findBy(['client' => $client]);
            if ($customers){
                $customer = $customerRepository->find($customerId);
                if ($customer) {
                    $jsonClientCustomers = $serializer->serialize($customer, 'json', ['groups' => 'getClientCustomerDetail']);
                    return new JsonResponse($jsonClientCustomers, Response::HTTP_OK, [], true);
                }
                return new JsonResponse(null, Response::HTTP_NOT_FOUND);
            }
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('api/clients/{clientId}/customers/{customerId}', name: 'api_delteClientCustomer', methods:['DELETE'])]
    #[IsGranted('ROLE_CLIENT', message: 'Vous n\'avez pas les droits suffisant pour supprimer un utilisateur')]
    public function deleteClientCustomer(int $clientId, int $customerId, ClientRepository $clientRepository, CustomerRepository $customerRepository, ManagerRegistry $doctrine): JsonResponse
    {
        $client = $clientRepository->find($clientId);
        if ($client) {
            $customers = $customerRepository->findBy(['client' => $client]);
            if ($customers){
                $customer = $customerRepository->find($customerId);
                if ($customer) {

                    $em = $doctrine->getManager();
                    $em->remove($customer);
                    $em->flush();

                    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
                }
                return new JsonResponse(null, Response::HTTP_NOT_FOUND);
            }
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('api/clients/{clientId}/customers', name: 'api_createClientCustomer', methods:['POST'])]
    #[IsGranted('ROLE_CLIENT', message: 'Vous n\'avez pas les droits suffisant pour créer un utilisateur')]
    public function createClientCustomer(int $clientId, SerializerInterface $serializer, Request $request, ValidatorInterface $validator, ClientRepository $clientRepository, EntityManagerInterface $em)
    {
        $customer = $serializer->deserialize($request->getContent(), Customer::class, 'json');

        $errors = $validator->validate($customer);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $customer->setClient($clientRepository->find($clientId));
        $customer->setRoles(['ROLE_USER']);

        $em->persist($customer);
        $em->flush();

        $jsonCustomer = $serializer->serialize($customer, 'json', ['groups' => ['customer', 'client']], true);

        //location?

        return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, [], true);
    }
}
