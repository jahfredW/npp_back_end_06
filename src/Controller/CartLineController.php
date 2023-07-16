<?php

namespace App\Controller;

use Exception;
use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\CartLine;
use App\Entity\OrderLine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartLineController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    #[Route('api/cartline', name: 'app_cart_line', methods: ['DELETE'])]
    // suupression d'une ligne avec l'itemId
    public function deleteAnCartLine(Request $request): Response
    {

        // récupération du queryParam itemId
        $pictureId = $request->query->get('itemId');
        
        // récupération du cookie cartId
        $cartId = $request->cookies->get('cartId');
        
        // récupération du panier
        $cart = $this->entityManager->getRepository(Cart::class)->findOneByCookie($cartId);
        
        // récupération de la ligne de panier
        $cartLine = $this->entityManager->getRepository(CartLine::class)->findByPictureIdAndCart($pictureId, $cart);
       
        // récupération de l'order correspondant$
        $order = $this->entityManager->getRepository(Order::class)->findOneByCart($cart);

        // récupération de l'oderLine correspondant 
        if($order != null){
            $orderLine = $this->entityManager->getRepository(OrderLine::class)->findOneByPictureAndOrder($pictureId, $order->getId());

            if($orderLine != null){

                $this->entityManager->remove($orderLine);
                $this->entityManager->flush();
            }
        }
        
        

        if($cartLine != null){

            $this->entityManager->remove($cartLine);
            $this->entityManager->flush();
        }

        

        try{
            $this->entityManager->flush();
        } catch (Exception $exception) {
            throw $exception;
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
