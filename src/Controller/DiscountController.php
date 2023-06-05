<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Discount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DiscountController extends AbstractController
{
    private $serializer;
    private $em;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $em){
        $this->serializer = $serializer;
        $this->em = $em;
    }
    
    #[Route('/api/discounts/{id?}', name: 'app_discount')]
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

    #[Route('/api/discounts/order/{id}', name: 'app_discount_by_order')]
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
}
