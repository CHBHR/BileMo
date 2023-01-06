<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class GetAllService
{
    public function getAll(string $name, array $groups, $repository, string $cacheName, Request $request, TagAwareCacheInterface $cache,SerializerInterface $serializer)
    {
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 3);

        $idCache = $name ."-" . $page . "-" . $limit;
        $context = SerializationContext::create()->setGroups($groups);

        return $cache->get(
            $idCache, 
            function (ItemInterface $item) use ($repository, $page, $limit, $serializer, $context, $cacheName) {
                $item->tag($cacheName);
                $list = $repository->findAllWithPagination($page, $limit);
                return $serializer->serialize($list, 'json',$context);
            });
    }
}