<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use OpenApi\Attributes as OA;

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

        $jsonCustomerList = $cache->get(
            $idCache,
            function (ItemInterface $item) use ($customerRepository, $page, $limit, $serializer) {
                $item->tag("customerCache");
                $customerList = $customerRepository->findAllWithPagination($page, $limit);
                return $serializer->serialize($customerList, 'json', ['groups' => 'getCustomer']);
            });

        return new JsonResponse($jsonCustomerList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/customers/{id}', name: 'api_detailcustomer', methods: ['GET'])]
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

}
