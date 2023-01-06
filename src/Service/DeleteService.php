<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class DeleteService
{
    public $em;

    public $cachePool;

    public function __construct(EntityManagerInterface $em, TagAwareCacheInterface $cachePool)
    {
        $this->em = $em;
        $this->cachePool = $cachePool;
    }

    public function delete(array $cacheName, $entity)
    {
        if($cacheName){
            $this->cachePool->invalidateTags($cacheName);
        }
        $this->em->remove($entity);
        $this->em->flush();
    }
}