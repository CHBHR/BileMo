<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class DeleteService
{
    public function delete(array $cacheName, $entity,TagAwareCacheInterface $cachePool, EntityManagerInterface $em)
    {
        $cachePool->invalidateTags($cacheName);
        $em->remove($entity);
        $em->flush();
    }
}