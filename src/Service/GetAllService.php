<?php

namespace App\Service;

use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class GetAllService
{
    public $cache;

    public $serializer;

    public function __construct(TagAwareCacheInterface $cache, SerializerInterface $serializer)
    {
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    /**
     * @param string $name  Name of the method, used here to create custom cache name for method to check before callback
     * @param array $groups List of the groups, used with the Hateoas annotations on the entity
     * @param array $tags   List of the tags used to invalisate cache in case of delete
     */
    public function getAll(string $name, array $groups, $repository, array $tags, $request)
    {
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 3);

        $maxNbrOfResults = count($repository->findAll());
        $maxNbrOfPages = ceil($maxNbrOfResults/$limit);

        if ($page <= 0 || $limit <= 0) {
            return new JsonResponse('Invalid parameters', Response::HTTP_BAD_REQUEST);
        } else if($page > $maxNbrOfPages){
            return new JsonResponse('This page doesn\'t exist', Response::HTTP_NOT_FOUND);
        }

        $context = SerializationContext::create()->setGroups($groups);

        $idCache = $name . "-" . $page . "-" . $limit;
        
        $seri = $this->serializer;

        return $this->cache->get(
            $idCache,
            function (ItemInterface $item) use ($repository, $page, $limit, $seri, $tags, $context) 
            {
                $data = [];

                $list = $repository->findAllWithPagination($page, $limit);
                $data[] = $list;

                $maxNbrOfResults = count($repository->findAll());
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
