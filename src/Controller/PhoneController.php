<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use App\Service\DeleteService;
use App\Service\GetAllService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use JMS\Serializer\SerializerInterface;

class PhoneController extends AbstractController
{
    #[OA\Response(
        response: 200,
        description: 'Renvois la liste des phones (produit)',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Phone::class, groups: ['getPhones']))
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
    #[OA\Tag(name: 'Produits')]
    #[Route('/api/phones', name: 'api_phones', methods: ['GET'])]
    public function getPhoneList(PhoneRepository $phoneRepository, Request $request, GetAllService $getAll): JsonResponse
    {
        $name = 'getPhoneList';
        $groups = ['getPhones'];
        $tags = ['phonesCache'];

        return $getAll->getAll($name, $groups, $phoneRepository, $tags, $request);

    }

    #[Route('/api/phones/{id}', name: 'api_detailPhone', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Renvois le détail d\'un téléphone (produit)',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Phone::class, groups: []))
        )
    )]
    #[OA\Tag(name: 'Produits')]
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer): JsonResponse
    {
        $jsonPhone = $serializer->serialize($phone, 'json');
        return new JsonResponse($jsonPhone, Response::HTTP_OK, ['accept' => 'json'], true);
    }

    #[Route('/api/phones/{id}', name: 'api_deletePhone', methods:['DELETE'])]
    #[OA\Response(
        response: 204,
        description: 'Supprime le téléphone',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Phone::class, groups: []))
        )
    )]
    #[OA\Tag(name: 'Produits')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour supprimer un produit')]
    public function deletePhone(Phone $phone, DeleteService $delete): JsonResponse
    {
        $tags = ["phonesCache"];
        $delete->delete($tags, $phone);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[OA\Response(
        response: 201,
        description: 'Créer un téléphone',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Phone::class, groups: []))
            )
        )]
    #[OA\Tag(name: 'Produits')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour créer un produit')]
    #[Route('/api/phones', name:'api_createPhone', methods: ['POST'])]
    public function createPhone(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
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

        return new JsonResponse($jsonPhone, Response::HTTP_CREATED, [], true);
    }

    #[OA\Response(
        response: 204,
        description: 'Modifier un téléphone',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Phone::class, groups: []))
            )
        )]
    #[OA\Tag(name: 'Produits')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisant pour modifier un produit')]
    #[Route('/api/phones/{id}', name:'api_updatePhone', methods:['PUT'])]
    public function updatePhone(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $updatedPhone = $serializer->deserialize($request->getContent(),
            Phone::class,
            'json',
        );
            $em->persist($updatedPhone);
            $em->flush();
            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
