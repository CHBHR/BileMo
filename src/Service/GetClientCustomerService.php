<?php

namespace App\Service;

use App\Repository\CustomerRepository;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class GetClientCustomerService
{
    public $cache;

    public $serializer;

    public $customerRepository;

    public function __construct(TagAwareCacheInterface $cache, SerializerInterface $serializer, CustomerRepository $customerRepository)
    {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->customerRepository = $customerRepository;
    }

    public function getClientCustomerList(string $name, array $groups, array $tags, $client, $request)
    {
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 3);

        $maxNbrOfResults = count($this->customerRepository->findBy(array('client' => $client)));
        $maxNbrOfPages = ceil($maxNbrOfResults/$limit);

        if ($page <= 0 || $limit <= 0) {
            return new JsonResponse('Invalid parameters', Response::HTTP_BAD_REQUEST);
        } else if($page > $maxNbrOfPages){
            return new JsonResponse('This page doesn\'t exist', Response::HTTP_NOT_FOUND);
        }

        $context = SerializationContext::create()->setGroups($groups);

        $idCache = $name . "-" . $page . "-" . $limit;
        
        $seri = $this->serializer;
        $repository = $this->customerRepository;

        return $this->cache->get(
            $idCache,
            function (ItemInterface $item) use ($repository, $page, $limit, $seri, $tags, $context, $client) 
            {
                $data = [];

                $list = $repository->findAllFromClientWithPagination($page, $limit, $client);
                $data[] = $list;

                $maxNbrOfResults = count($repository->findBy(array('client' => $client)));
                $maxNbrOfPages = ceil($maxNbrOfResults/$limit);
                $paginationData = [
                    "totalItems" => $maxNbrOfResults,
                    "totalPages" => $maxNbrOfPages,
                    "page" => $page,
                    "limit" => $limit
                ];
                $data[] = $paginationData;

                $jsonList = $seri->serialize($data, 'json', $context);

                $results = new JsonResponse($jsonList, Response::HTTP_OK, [], true);

                // Cache expires after 10 minutes
                $item->expiresAfter(600);
                $item->tag($tags);

                return $results;
            }
        );
    }
    
}