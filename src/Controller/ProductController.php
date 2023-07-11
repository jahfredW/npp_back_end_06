<?php

namespace App\Controller;

use Exception;
use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
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
    #[Route('api/products', name: 'app_products', methods: ['GET'])]
    public function getAllProducts(Request $request): JsonResponse
    {
        $product = $this->em->getRepository(Products::class)->findAll();
        $productJson = $this->serializer->serialize($product, 'json', ['groups' => 'getProduct']);


        return new JsonResponse($productJson, Response::HTTP_OK, [], true);
    }

    // get Product by id 
    #[Route('api/products/{id}', name: 'app_products_get_id', methods: ['GET'])]
    public function getProductById($id, Products $product): JsonResponse
    {
        if($product){
            $productJson = $this->serializer->serialize($product, 'json', ['groups' => 'getProduct']);
        }
       
        return new JsonResponse($productJson, Response::HTTP_OK, [], true);
    }



    // get Product Price to add at front end cart. 
    #[Route('api/products/{id}/price', name: 'app_product_price', methods: ['GET'])]
    public function getProductPrice(Request $request, $id): JsonResponse
    {
        $product = $this->em->getRepository(Products::class)->find($id);
        $productPrice = $product->getPrice();

        return new JsonResponse($productPrice, Response::HTTP_OK);
    }

    // remove a product by id. 
    #[Route('api/products/{id}', name: 'app_product_remove', methods: ['DELETE'])]
    public function removeProduct($id, Products $product): JsonResponse
    {
        if($product)
        {
            try{
                $this->em->remove($product);
                $this->em->flush();
            } catch ( Exception $e){
                throw new HttpException(500, $e->getMessage());
            }
            
        }

        return new JsonResponse('produit supprimé', Response::HTTP_OK);
    }

    // post a product
    #[Route('/api/products', name: 'post_product', methods: ['POST'])]
    public function postProduct(Request $request): JsonResponse
    {
        $content = $request->toArray();

        if($content)
        {
            $newProduct = new Products;
            // dd($newDiscount);

            // récupération de la liste des promotions inscrites en base de donnée. 
            $productList = $this->em->getRepository(Products::class)->findAll();
         

            // foreach($productList as $product){
            //     if($product->getArticles() == (int)$content['articles'] )
            //     {
            //         throw new HttpException(500, 'erreur');
            //         break;
            //     }
            // }
                
            if(isset($content['name']) && !empty($content['name'])){
                $newProduct->setName($content['name']);
            } 
            if(isset($content['price']) && !empty($content['price'])){
                $newProduct->setPrice((floatval($content['price'])));
            }
            if(isset($content['description']) && !empty($content['description'])){
                $newProduct->setDescription($content['description']);
            }

            try{
                
                $this->em->persist($newProduct);
                $this->em->flush();
            } catch (Exception $e){
                throw new HttpException(500, $e->getMessage());
            }

            return new JsonResponse(
                    'Type créé', Response::HTTP_OK
            );
            }
    }

    // update a product by id
    #[Route('/api/products/{id}', name: 'update_product_by_id', methods: ['PUT'])]
    public function updateProductById($id, Products $product, Request $request): JsonResponse
    {
      
        if($product){
            $updatedProduct = $this->serializer->deserialize($request->getContent(),
            Products::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $product]);
         
            $content = $request->toArray();
       
            
            if(isset($content['name']) && !empty($content['name'])){
                $updatedProduct->setName($content['name']);
            } 
            if(isset($content['price']) && !empty($content['price'])){
                $updatedProduct->setPrice((floatval($content['price'])));
            }
            if(isset($content['description']) && !empty($content['description'])){
                $updatedProduct->setDescription($content['description']);
            }

            try{
                // $this->em->persist($updatedProduct);
                $this->em->flush();
            } catch (Exception $e){
                throw new HttpException(500, 'erreur');
            }
        } 

        return new JsonResponse(
                'updated', Response::HTTP_CREATED
        );
    }
}
