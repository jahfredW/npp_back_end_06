<?php

namespace App\Controller;

use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{   
    private $em;
    private $serializer;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer ){
        $this->em = $em;
        $this->serializer = $serializer;
    }

    // get Product Price to add at front end cart. 
    #[Route('api/products', name: 'app_products')]
    public function getAllProducts(Request $request): JsonResponse
    {
        $product = $this->em->getRepository(Products::class)->findAll();
        $productJson = $this->serializer->serialize($product, 'json', ['groups' => 'getProduct']);


        return new JsonResponse($productJson, Response::HTTP_OK);
    }


    // get Product Price to add at front end cart. 
    #[Route('api/products/{id}/price', name: 'app_product_price')]
    public function getProductPrice(Request $request, $id): JsonResponse
    {
        $product = $this->em->getRepository(Products::class)->find($id);
        $productPrice = $product->getPrice();

        return new JsonResponse($productPrice, Response::HTTP_OK);
    }
}
