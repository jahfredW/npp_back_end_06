<?php

namespace App\Controller;

use App\Entity\User;
use App\Utils\Utils;
use App\Entity\Address;
use App\Services\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{
    private $em;
    private $serializer;
    private $tokenManager;
    private $tokenStorage;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer, JWTTokenManagerInterface $tokenManager, 
    TokenStorageInterface $tokenStorage){

        $this->tokenManager = $tokenManager;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->tokenStorage = $tokenStorage;
    }
    // récupération des utilisateurs
    #[Route('/api/users/{userId?}', name: 'app_user', methods: ['GET'] )]
    // #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants')]
    public function getUsers(Request $request, $userId): JsonResponse
    {
        if(isset($userId) && !empty($userId)) {
            $user = $this->em->getRepository(User::class)->find($userId);
        } else {
            // récupération de la page 
            $page = $request->query->get('page');

            // récupération de la limite 
            $limit = $request->query->get('limit');

            // récupération de la date de début 
            $beginDate = $request->query->get('beginDate');
            $endDate = $request->query->get('endDate');

            // utilisation de la méthode de classe dateDecoder pour convertir les dates 
            $beginDateObject = Utils::dateDecoder($beginDate);
            $endDateObject = Utils::dateDecoder($endDate);

            // gestion de l'erreur si date début > date de fin. 
            if( $beginDateObject > $endDateObject && $endDateObject != null){
                throw new HttpException(404, 'La date de fin ne peut être supérieure');
            }

            // récupération du pseudo 
            $pseudo = $request->query->get('pseudo');

            if(isset($page) && !empty($page) && isset($limit) && !empty($limit)) {
                $offset = ($page - 1) * $limit;
                $user = $this->em->getRepository(User::class)->findAllWithPagination($limit, $offset
            , $pseudo, $beginDateObject, $endDateObject);
            } else {
                $user = $this->em->getRepository(User::class)->findAll();
            }
            
            
        }
        
        $jsonUser = $this->serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    // récupération des informations de l'utilisateur connecté
    #[Route('/api/user', name: 'app_user_current', methods: ['GET'] )]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants')]
    public function getCurrentUser(): JsonResponse
    {
        
        $user = $this->getUser();
        
        $jsonUser = $this->serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    // modification du pseudo 
    #[Route('/api/user/{userId}', name: 'app_user_pseudo_update', methods: ['PUT'])]
    #[IsGranted('ROLE_USER', message: 'Vous n\'avez pas les droits suffisants')]
    public function pseudoUpdate( $userId, Request $request,): JsonResponse
    {

    $currentToken = $this->tokenStorage->getToken();

    // Récupération du payload du token actuel
    $payload = $this->tokenManager->decode($currentToken);

   

        $content = $request->toArray();

        $user = $this->em->getRepository(User::class)->findOneById($userId);
       

        if(isset($content['pseudo'])){
            $user->setPseudo($content['pseudo']);
        }

        if(isset($content['email'])){
            $newUserEmail = $content['email'];
            $user->setEmail($newUserEmail);
            // $userAddress = $this->em->getRepository(Address::class)->findOneByUser($userId);
            // $userAddress->setEmail($newUserEmail);
            // dd($userId);
            // dd($userAddress);

           
            // Mise à jour du champ "email" du payload
            
            // Création d'un nouveau token avec les informations mises à jour
            // $newToken = $this->tokenManager->create($user);
            // dd($newToken);
        }

        
        try {
            $this->em->persist($user);
            $this->em->flush();

        } catch (\HttpEception $e) {

            throw new HttpException ( $e->getMessage());
        }

        // Création d'un nouveau token avec les informations mises à jour
        $newToken = $this->tokenManager->create($user);
        

        
        // Mettre à jour le token dans la réponse
        $response = new JsonResponse(['token' => $newToken]);
        // $response->headers->set('Authorization', 'Bearer '.$newToken);
        // $response->headers->set('Access-Control-Expose-Headers', 'Authorization');
    
        return $response;
    }

    // récupération des utilisateurs
    #[Route('/api/users/{user}', name: 'app_user_cnange_status', methods: ['PUT'] )]
    // #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants')]
    public function putUsers(User $user, MailerService $mail, Request $request): JsonResponse
    {
        $content = $request->toArray();
       
        $role = $content['role'];
     
        if($user){
            $tab = [];
            $tab[] = $role;
            $user->setRoles($tab);
            // if($user->getRoles() == ['ROLE_ADMIN']){
            //     $user->setRoles(['ROLE_ADMIN']);
            // }
            // else if ($user->getRoles() == ['ROLE_USER']){
            //     $user->setRoles(['ROLE_BANNISHED']);

                $mail->buildHtml(`<h1>Votrev rôle a été changé :<h1>`);
                $mail->buildHtml('</br>');
                $mail->buildHtml('<p><strong> Pour toute réclamation, veuillez contacter l\'administrateur</strong></p>');

                $mail->send('NicolasPeltier@gmail.com', $user->getEmail(), 'Avis de Banissement'); 

            // } else {
                // $user->setRoles([]);

                // $mail->buildHtml('<h1>Vous avez été réhabilité<h1>');
                // $mail->buildHtml('</br>');
                // $mail->buildHtml('<p><strong> Bon retour chez nous :) :) </strong></p>');

                // $mail->send('NicolasPeltier@gmail.com', $user->getEmail(), 'Avis de Réhabilitation');
            // }

        } else {
            throw  new HttpException(500, 'Error');
        }

        $this->em->persist($user);

        $this->em->flush();
        
        $jsonUser = $this->serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    // suppression d'un utilisateur
    #[Route('/api/users/{user}', name: 'app_user_delete', methods: ['DELETE'] )]
    // #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants')]
    public function removeUser(User $user): JsonResponse
    {
        try{
            $this->em->remove($user);
        } catch (\Exception $e){
            throw new HttpException($e->getMessage());
        }

        $this->em->flush();
        
        $jsonUser = $this->serializer->serialize($user, 'json', ['groups' => 'getUsers']);
        

        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }
}
