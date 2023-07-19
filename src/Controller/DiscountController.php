<?php

namespace App\Controller;

use Exception;
use App\Entity\Order;
use App\Entity\Discount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DiscountController extends AbstractController
{
    private $serializer;
    private $em;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $em){
        $this->serializer = $serializer;
        $this->em = $em;
    }
    
    // get a discount by id 
    #[Route('/api/discounts/{id?}', name: 'app_discount', methods: ['GET'])]
    public function getDiscount(?int $id, ?Discount $discount): JsonResponse
    {
        if(isset($discount)){
            $jsonDiscount = $this->serializer->serialize($discount, 'json', ['groups' => 'getDiscount']);
        } else {
            $discountList = $this->em->getRepository(Discount::class)->findAll();
            $jsonDiscount = $this->serializer->serialize($discountList, 'json', ['groups' => 'getDiscount']);
        }

        return new JsonResponse(
                $jsonDiscount, Response::HTTP_OK, [], true
        );
    }

    // get a discount by order id 
    #[Route('/api/discounts/order/{id}', name: 'app_discount_by_order', methods: ['GET'])]
    public function getDiscountByOrderId(Request $request, $id): JsonResponse
    {
        $order = $this->em->getRepository(Order::class)->findDiscountByOrderId($id);
        $discount = $order->getDiscount();
        // $discount = $this->em->getRepository(Discount::class)->find($discountId);

        $jsonDiscount = $this->serializer->serialize($discount, 'json', ['groups' => 'getDiscount']);
        return new JsonResponse(
                $jsonDiscount, Response::HTTP_OK, [], true
        );
    }

    // delete a discount by id
    #[Route('/api/discounts/{id}', name: 'delete_discount_by_id', methods: ['DELETE'])]
    public function deleteDiscountById($id, Discount $discount): JsonResponse
    {
        if($discount){
            try{
                $this->em->remove($discount);
                $this->em->flush();
            } catch (\Exception $e){
                throw new HttpException($e->getMessage());
            }
        } 

        return new JsonResponse(
                'Réduction supprimée', Response::HTTP_OK, [], true
        );
    }

    // update a discount by id
    #[Route('/api/discounts/{id}', name: 'update_discount_by_id', methods: ['PUT'])]
    public function updateDiscountById($id, Discount $discount, Request $request): Response
    {
        if($discount){
            $updatedDiscount = $this->serializer->deserialize($request->getContent(),
            Discount::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $discount]);

            $content = $request->toArray();
            
            if(isset($content['title']) && !empty($content['title'])){
                $updatedDiscount->setTitle($content['title']);
            } 
            if(isset($content['rate']) && !empty($content['rate'])){
                $updatedDiscount->setRate((floatval($content['rate'])));
            }
            if(isset($content['articles']) && !empty($content['articles'])){
                $updatedDiscount->setArticles((int)$content['articles']);
            }

            try{
                $this->em->flush();
            } catch (Exception $e){
                throw new HttpException(500, 'erreur');
            }
        } 

        return new JsonResponse(
                'updated', Response::HTTP_CREATED
        );
    }

    // post a discount
    #[Route('/api/discounts', name: 'post_discount', methods: ['POST'])]
    public function postDiscount(Request $request): JsonResponse
    {
        $content = $request->toArray();
        if($content)
        {
            $newDiscount = new Discount;
            // dd($newDiscount);

            // récupération de la liste des promotions inscrites en base de donnée. 
            $discountList = $this->em->getRepository(Discount::class)->findAll();
         

            foreach($discountList as $discount){
                if($discount->getArticles() == (int)$content['articles'] )
                {
                    throw new HttpException(500, 'erreur');
                    break;
                }
            }
                
            if(isset($content['title']) && !empty($content['title'])){
                $newDiscount->setTitle($content['title']);
            } 
            if(isset($content['rate']) && !empty($content['rate'])){
                if($content['rate'] >= 100){
                    $content['rate'] == 99;

                    return new JsonResponse('La réduction ne peut être supérieure à 100', Response::HTTP_NO_CONTENT);
                } else {
                    $newDiscount->setRate((floatval($content['rate'])));
                }
                
            }
            if(isset($content['articles']) && !empty($content['articles'])){
                $newDiscount->setArticles((int)$content['articles']);
            }

            try{
                
                $this->em->persist($newDiscount);
                $this->em->flush();
            } catch (Exception $e){
                throw new HttpException(500, $e->getMessage());
            }

            return new JsonResponse(
                    'Réduction créée', Response::HTTP_OK, [], true
            );
            }

        
    }

}
