<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    private $em;
    private $serializer;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer){
        $this->em = $em;
        $this->serializer = $serializer;
    }

    #[Route('/api/category', name: 'app_category', methods: ['GET'])]
    public function getAllCategory(): JsonResponse
    {

        $categoryRepository = $this->em->getRepository(Category::class);
        $categoryList = $categoryRepository->findAll();

        $jsonCategoryList = $this->serializer->serialize($categoryList, 'json', ['groups' => 'getCategories']);

        return new JsonResponse($jsonCategoryList, Response::HTTP_OK, [], true);
    }
}
