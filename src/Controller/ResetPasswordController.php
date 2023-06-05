<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Utils\Utils;
use App\Services\MailerService;
use App\Form\PasswordRecoveryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class ResetPasswordController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }

    #[Route('/api/password', name: 'reset_password', methods: ['POST'])]
    public function index(Request $request, TokenGeneratorInterface $tokenGenerator, MailerInterface $mailer,
    SerializerInterface $serializer): Response
    {
        // récuparation des datas du formdata
        $post_data = $request->request->all();

        // récupération de l'email 
        $email = $post_data['email'];

        // on vérifie si le mail appartient à un utilisateur 
        $user = $this->em->getRepository(User::class)->findOneByEmail($email);

        if($user){
            // génération d'un token de réinitialisation
            $token = $tokenGenerator->generateToken();
            $user->setResetToken($token);

            try {
                $this->em->persist($user);
            } catch (Exception $e) {
                throw new HttpException(500, 'un problème est survenu');
            }

            $this->em->flush();

            // génération d'un lien de réinitialisation de mot de passe
            $url = $this->generateUrl('reset_password_link', compact('token'), UrlGeneratorInterface::ABSOLUTE_URL);
            

            // création du mail 
            // injecter les dépendances évite de faire un new 
            $mailerService = new MailerService($mailer, $serializer);

            try {
                $mailerService->buildHtml("<p>Réinitialisez votre mot de passe en cliquant ici : <a href=" . $url . ">ici</a></p>");
                $mailerService->send(
                    'admin@gmail.com',
                    $user->getEmail(),
                    'Lien de réinitialisation',
                    
                );
            } catch (Exception $e){
                throw new HttpException(500, 'un problème est survenu'); 
            }
            
            

        } else {
            throw new HttpException(500, 'un problème est survenu (user)');
        }

        
        
        return new Response($user->getId());
        
    }

    #[Route('api/password/{token}', name: 'reset_password_link')]
    public function resetLink(
        string $token,
        Request $request,
        UserPasswordHasherInterface $passwordHasher
    ) 
    {
        // vérification si token est dans la dbb
        $user = $this->em->getRepository(User::class)->findOneByResetToken($token);
        
        if($user){
            $form = $this->createForm(PasswordRecoveryType::class);

            $form->handleRequest($request);
            
            if($form->isSubmitted() && $form->isValid()){
                // suppression du token 
                $user->setResetToken('');
                $user->setPassword(
                    $passwordHasher->hashPassword($user, Utils::cleanUp($form->get('password')->getData()))
                );
                $this->em->persist($user);
                $this->em->flush();

                $this->addFlash('success', 'Mot de passe modifié, vous pouvez fermer cette page!');

            }


            return $this->render('reset_password/index.html.twig', [
                'passForm' => $form->createView()
            ]);
        } else {
            throw new HttpException(500, 'un problème est survenu');
        }


    }

        
}
