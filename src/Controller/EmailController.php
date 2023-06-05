<?php

namespace App\Controller;

use DateTime;
use Stripe\Stripe;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\Picture;
use Stripe\StripeClient;
use App\Entity\OrderLine;
use Stripe\Checkout\Session;
use Symfony\Component\Mime\Part;
use Symfony\Component\Mime\Email;
use App\Controller\AlbumController;
use Symfony\Component\Mime\Part\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Part\DataPart;
use App\Services\Storage\GoogleCloudStorage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class EmailController extends AbstractController
{
    private $em;
    private $serializer;
    private $cs;
    const BUCKET_NAME = 'npp_photos';

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer){
        $this->em = $em;
        $this->serializer = $serializer;
        $this->cs = GoogleCloudStorage::getInstance(PictureController::BUCKET_NAME);
    }

    #[Route('/api/email', name: 'app_email')]
    public function index(MailerInterface $mailer): Response
    {

        $html = "<h2>Veuillez trouver ci-dessous les liens de téléchargement pour vos photos</h2>";
        $html .= "<br />";
        $html .= "<p>Pour télécharger vos photos cliquez sur les liens, puis cliquez droit et ' enregistrer l'image sous' </p>";
        $html .= "<br />";

        $picture = $this->em->getRepository(Picture::class)->find(473);
        $pictureName = $picture->getFileName();
        $storage = GoogleCloudStorage::getInstance(AlbumController::BUCKET_NAME);
        $storage->setObjectName($pictureName);

        $object = $storage->getObject();
        $url = $storage->getObject()
                ->signedUrl(new DateTime( '+ 7 days '), [
                    'version' => 'v4',
                    'private' => true,
                    
                ]);
                $html .= "<p>" . $pictureName . "</p>" . "<a href=\"" . $url . "\" download,>Télécharger l'image</a>" . "</p><br />";
        
    

        $email = (new Email())
            ->from('Nico@example.com')
            ->to('test@test.com')
            ->subject('Merci pour votre commande!')
            // ->html('')
            ->html('merci')
            ->attach($html);
       
        $mailer->send($email);

        Stripe::setApiKey('sk_test_51Mtp9TF4u59n6MoMmRgvAcjiSg55Cb2gzPafYqsKnaSvmyyhZyMKZvrIUll6jBRjHL7kf1ahQhuqoXH32Hvip0sF00rclR39VW');

        $YOUR_DOMAIN = 'http://127.0.0.1:8000';
    
    
    
        $checkout_session = Session::create([
            'line_items' => [[
            # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
            'price' => 'price_1MtpVbF4u59n6MoMJipDpFrP',
            'quantity' => 1,
            ]],
        'mode' => 'payment',
        'success_url' => $YOUR_DOMAIN . '/success.html',
        'cancel_url' => $YOUR_DOMAIN . '/cancel.html',
    ]);

    dump($checkout_session->id);
    dd($checkout_session);
        
    }
}
