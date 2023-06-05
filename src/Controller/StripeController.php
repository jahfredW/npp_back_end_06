<?php

namespace App\Controller;

use Stripe\Event;
use Stripe\Webhook;
use App\Classe\StripeInit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Stripe\Exception\UnexpectedValueException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class StripeController extends AbstractController
{
    

    // route du webHook appelé par stripe
    #[Route("/api/stripe/webhook", name: "stripe_webhook", methods: ['POST'])] 
    public function stripeWebhook(Request $request, EntityManagerInterface $em, MailerInterface $mailer, SerializerInterface $serializer
    ): Response
    {
        $user = $this->getUser(); 

        $secretEndPoint = $this->getParameter('app.secretHook');
        $privateSecretStripeKey = $this->getParameter('app.secretStripe');

        $stripeInit = new StripeInit($privateSecretStripeKey);
        
        $stripeInit->handleWebhook($request, $secretEndPoint, $em, $serializer, $mailer);

        return new Response('Webhook received');
    }

    // route de récupération de session de paiement 
    #[Route("/api/stripe/retrieve/{stripe_session}", name: "stripe_session_retrieve", methods: ['GET'])] 
    public function stripeSessionRetrieve(Request $request, MailerInterface $mailer, SerializerInterface $serializer): Response
    {   
        // récupération de l'utilisateur connecté 
        $user = $this->getUser();

        // Récupération de la session stripe ( avc attribut passé en param)
        $stripe_session = $request->attributes->get('stripe_session');
       

        // Si l'utilsiateur est connecté 
        if($user){

            $privateSecretStripeKey = $this->getParameter('app.secretStripe');
            $stripeInit = new StripeInit($privateSecretStripeKey);

            // utilisation de la émthode retrieve session
            $session = $stripeInit->retrieveSession($stripe_session);
            $url = $session->url;

            // renvoie de l'url vers le front 
            return new JsonResponse($url);

        }

        throw new NotFoundHttpException('Aucun utilisateur connecté');
    }
}
