<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use App\Entity\CartLine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    #[Route('/api/cart/{id?}', name: 'app_cart', methods: 'GET')]
    public function postAnItem(Request $request, $id): JsonResponse
    {
        // $content = $request->get('idArticle');
        
        $cartId = $request->cookies->get('cartId');
       
        // définir une date d'expiration dans le passé permet d'expirer le cookie à la fermeture
        // du navigateur. Ne rien mettre fonctionne également
        // $cookie = new Cookie('cartId', $cartId, time() - (3600 * 24 * 7), '/', 'localhost',
        //         true, false, false, 'None');

        // $user = $this->getUser();

        // Créez une réponse vide avec le statut HTTP 204
        $response = new JsonResponse();

        // si aucun utilisateur n'est connecté : 
        // if(!$user){
         
        // si aucun cookie est dans le header
        if(!$cartId){
            // génération d'un identifiant unique 
            $cartId = uniqid(); // Générez l'identifiant du panier

            // Créez un objet Cookie avec l'identifiant du panier et une durée de validité de 7 jours
            $cookie = new Cookie('cartId', $cartId, time() + (3600 * 24 * 7), '/', 'localhost',
            true, false, false, 'None');

            if($cookie){
                $response->headers->setCookie($cookie);
                $response->sendHeaders(); 
            }
            // $cookie = new Cookie('cartId', $cartId, time() + (3600 * 24 * 7));
        } else {
            // récupération du cookie dans le header
            $cartId = $request->cookies->get('cartId');
        }
        // attribution du cookie à l'idCart
        // $cartId = $cookie;

        // vérificiation d'un panier correspondant à l'utilisateur 
        $cart = $this->entityManager->getRepository(Cart::class)->findOneByCookie($cartId);

        if($cart == null){
            $cart = new Cart();
            $cart->setCookieId($cartId);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }  

            
        // } else {
        //     // attribution du de l'id de l'utilisateur au cart Id
            
        //     $cartId = $user->getId();

        //     // vérificiation d'un panier correspondant à l'utilisateur 
        //     $cart = $this->entityManager->getRepository(Cart::class)->findOneByClient($cartId);

        //     if($cart == null){
        //         $cart = new Cart();
        //         $cart->setClientId($cartId);
        //         $this->entityManager->persist($cart);
        //     }       
            
        // }

        // récupération de l'id du produit 
        if($id){
            $cartLine = $this->entityManager->getRepository(CartLine::class)->findByPictureIdAndCart($id, $cart);
            if($cartLine){
                $cartLine->setQuantity(1);

            } else {
                $cartLine = new CartLine();
                $cartLine->setCart($cart);
                $cartLine->setPictureId($id);
                $cartLine->setQuantity(1);
            }

            $this->entityManager->persist($cartLine);
        }

        $this->entityManager->flush();
                
        

        // dd($cookie);
        // Ajoutez le cookie à la réponse
        
        
        
        return $response;
    }

    #[Route('/api/cart/{id?}', name: 'app_cart_delete', methods: 'DELETE')]
    public function deleteAnItem(Request $request, $id): JsonResponse
    {
        // récupération de l'utilisateur connecté
        // $user = $this->getUser();

        // récupération de l'idCArt du cookie
        $cartId = $request->cookies->get('cartId');

        // si connexion anonyme, 
        
        // le panier est récupéré via l'id cartId du cookie
        $cart = $this->entityManager->getRepository(Cart::class)->findOneByCookie($cartId);

        // suupression du cookie en envoyanet une date passée 
        // $cookie = new Cookie('cartId', $cartId, time() - (3600 * 24 * 7), '/', 'localhost',
        //     true, false, false, 'None');
        
        
        // $response->headers->setCookie($cookie);
        // $response->sendHeaders(); 
        
        
        // suppresion du panier
        $this->entityManager->remove($cart);

        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);;
    }
}
