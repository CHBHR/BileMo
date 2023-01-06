<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class GetAllService
{
    public $request;

    public $cache;

    public $serializer;

    public function __construct(TagAwareCacheInterface $cache,SerializerInterface $serializer)
    {
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    public function getAll(string $name, array $groups, $repository, string $cacheName, $request)
    {
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 3);

        $idCache = $name ."-" . $page . "-" . $limit;
        $context = SerializationContext::create()->setGroups($groups);

        $seri = $this->serializer;
        return $this->cache->get(
            $idCache, 
            function (ItemInterface $item) use ($repository, $page, $limit, $seri, $context, $cacheName) {
                $item->tag($cacheName);
                $list = $repository->findAllWithPagination($page, $limit);
                return $seri->serialize($list, 'json',$context);
            });
    }
}