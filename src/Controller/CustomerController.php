<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\ClientRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CustomerController extends AbstractController
{
    #[Route('/api/customers', name: 'api_customer', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Renvois la liste des customers (utilisateurs)',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Customer::class, groups: ['getCustomer']))
        )
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'La page que l\'on veux récupérer',
        schema: new OA\Schema(type:'int')
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Le nombre d\'éléments que l\'on veux récupérer',
        schema: new OA\Schema(type:'int')
    )]
    #[OA\Tag(name: 'Customer')]
    public function getCustomerList(CustomerRepository $customerRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getCustomerList-" . $page . "-" . $limit;
        $context = SerializationContext::create()->setGroups(['getCustomer']);

        $jsonCustomerList = $cache->get(
            $idCache,
            function (ItemInterface $item) use ($customerRepository, $page, $limit, $serializer, $context) {
                $item->tag("customerCache");
                $customerList = $customerRepository->findAllWithPagination($page, $limit);
                return $serializer->serialize($customerList, 'json', $context);
            });

        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/customers/{id}', name: 'api_detailCustomer', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Renvois le détail d\'un customers (utilisateurs)',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Customer::class, groups: ['getCustomer']))
        )
    )]
    #[OA\Tag(name: 'Customer')]
    public function getDetailCustomer(Customer $customer, SerializerInterface $serializer): JsonResponse 
    {
        $jsonCustomer = $serializer->serialize($customer, 'json', ['groups' => 'getCustomer']);
        return new JsonResponse($jsonCustomer, Response::HTTP_OK, ['accept' => 'json'], true);
   }

   #[Route('api/clients/{clientId}/customers', name: 'api_clientCustomers', methods: ('GET'))]
   #[OA\Response(
       response: 200,
       description: 'Renvois la liste des customers (utilisateurs) lié à un client',
       content: new OA\JsonContent(
           type: 'array',
           items: new OA\Items(ref: new Model(type: Customer::class, groups: ['getClientCustomers']))
       )
   )]
   #[OA\Parameter(
       name: 'page',
       in: 'query',
       description: 'La page que l\'on veux récupérer',
       schema: new OA\Schema(type:'int')
   )]
   #[OA\Parameter(
       name: 'limit',
       in: 'query',
       description: 'Le nombre d\'éléments que l\'on veux récupérer',
       schema: new OA\Schema(type:'int')
   )]
   #[OA\Tag(name: 'ClientCustomer')]
   public function getClientCustomersList(SerializerInterface $serializer, CustomerRepository $customerRepository, Request $request, TagAwareCacheInterface $cache)
   {
       $page = (int) $request->get('page', 1);
       $limit = (int) $request->get('limit', 3);

       $idCache = "getClientCustomersList-" . $page . "-" . $limit;
       $context = SerializationContext::create()->setGroups(['getClientCustomers']);
       
       $jsonClientCustomers = $cache->get(
           $idCache,
           function (ItemInterface $item) use ($customerRepository, $page, $limit, $serializer, $context) {
               $item->tag("clientCustomersCache");
               $clientCustomers = $customerRepository->findAllWithPagination($page, $limit);
               return $serializer->serialize($clientCustomers, 'json', $context);
           });
       
           return new JsonResponse($jsonClientCustomers, Response::HTTP_OK, [], true);
   }

   #[Route('api/clients/{clientId}/customers/{customerId}', name: 'api_clientCustomerDetail', methods: ('GET'))]
   #[OA\Response(
       response: 200,
       description: 'Renvois le détail d\'un customer (utilisateurs) lié à un client',
       content: new OA\JsonContent(
           type: 'array',
           items: new OA\Items(ref: new Model(type: Customer::class, groups: ['getClientCustomerDetail']))
       )
   )]
   #[OA\Tag(name: 'ClientCustomer')]
   #[Entity('customer', options: ['id' => 'customerId'])]
   public function getClientCustomersDetail(Customer $customer, SerializerInterface $serializer)
   {

       $jsonClientCustomers = $serializer->serialize($customer, 'json', ['groups' => 'getClientCustomerDetail']);
       return new JsonResponse($jsonClientCustomers, Response::HTTP_OK, [], true);
   }

   #[Route('api/clients/{clientId}/customers/{customerId}', name: 'api_deleteClientCustomer', methods:['DELETE'])]
   #[IsGranted('ROLE_CLIENT', message: 'Vous n\'avez pas les droits suffisant pour supprimer un utilisateur')]
   #[OA\Response(
       response: 204,
       description: 'Supprime le customer (utilisateur) lié à un client',
       content: new OA\JsonContent(
           type: 'array',
           items: new OA\Items(ref: new Model(type: Customer::class, groups: []))
       )
   )]
   #[OA\Tag(name: 'ClientCustomer')]
   #[Entity('customer', options: ['id' => 'customerId'])]
   public function deleteClientCustomer(ManagerRegistry $doctrine, Customer $customer, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
   {
       $cachePool->invalidateTags(["clientCustomersCache"]);
       $em = $doctrine->getManager();
       $em->remove($customer);
       $em->flush();

       return new JsonResponse(null, Response::HTTP_NO_CONTENT);
   }

   #[Route('api/clients/{clientId}/customers', name: 'api_createClientCustomer', methods:['POST'])]
   #[IsGranted('ROLE_CLIENT', message: 'Vous n\'avez pas les droits suffisant pour créer un utilisateur')]
   #[OA\Response(
       response: 201,
       description: 'Créer un customer (utilisateur) lié à un client',
       content: new OA\JsonContent(
           type: 'array',
           items: new OA\Items(ref: new Model(type: Customer::class, groups: []))
       )
   )]
   #[OA\Tag(name: 'ClientCustomer')]
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

       return new JsonResponse($jsonCustomer, Response::HTTP_CREATED, [], true);
   }

}
