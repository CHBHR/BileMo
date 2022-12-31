<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class PhoneController extends AbstractController
{
    #[Route('/api/phones', name: 'api_phone', methods: ['GET'])]
    public function getPhoneList(PhoneRepository $phoneRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        // Possibilité d'ajouter des params. dans l'uri (ex ?page=2&limit=2)
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getPhoneList-" . $page . "-" . $limit;

        $jsonPhoneList = $cache->get(
            $idCache, 
            function (ItemInterface $item) use ($phoneRepository, $page, $limit, $serializer) {
                $item->tag("phonesCache");
                $phoneList = $phoneRepository->findAllWithPagination($page, $limit);
                return $serializer->serialize($phoneList, 'json',[ 'groups' => 'getPhones']);
            });

        return new JsonResponse($jsonPhoneList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/phones/{id}', name: 'api_detailphone', methods: ['GET'])]
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer): JsonResponse 
    {
        $jsonPhone = $serializer->serialize($phone, 'json');
        return new JsonResponse($jsonPhone, Response::HTTP_OK, ['accept' => 'json'], true);
   }

   #[Route('/api/phones/{id}', name: 'api_deletePhone', methods:['DELETE'])]
   #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour supprimer un produit')]
   public function deletePhone(Phone $phone, ManagerRegistry $doctrine): JsonResponse
   {
       $em = $doctrine->getManager();
       $em->remove($phone);
       $em->flush();

       return new JsonResponse(null, Response::HTTP_NO_CONTENT);
   }

   #[Route('/api/phones', name:'api_createPhone', methods: ['POST'])]
   #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour créer un produit')]
   public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
   {
       $phone = $serializer->deserialize($request->getContent(), Phone::class, 'json');

       // Vérif. des erreurs
       $errors = $validator->validate($phone);
       if ($errors->count() > 0) {
           return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
       }

       $em->persist($phone);
       $em->flush();

       $jsonPhone = $serializer->serialize($phone, 'json');

       return new JsonResponse($jsonPhone, Response::HTTP_CREATED);
   }
}
