<?php

namespace App\Controller;


use DateTime;
use Stripe\Stripe;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\Address;
use App\Entity\Picture;
use App\Entity\Discount;
use App\Entity\OrderLine;
use App\Classe\StripeInit;
use Stripe\Checkout\Session;
// use Symfony\Component\Mime\Email;
use App\Services\MailerService;
use Symfony\Component\Mime\Part;
use App\Controller\AlbumController;
use Symfony\Component\Mime\Part\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mime\Part\DataPart;
use App\Services\Storage\GoogleCloudStorage;
// use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;




// Dans cet exemple, nous avons injecté le service lexik_jwt_authentication.encoder qui implémente 
// l'interface JWTEncoderInterface. Nous avons ensuite utilisé la méthode decode() du 
// service pour décoder le token JWT.
class OrderController extends AbstractController
{
    private $em;
    private $serializer;
    private $cs;
    const BUCKET_NAME = 'npp_photos_prod';
 

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer){
        $this->em = $em;
        $this->serializer = $serializer;
        $this->cs = GoogleCloudStorage::getInstance(PictureController::BUCKET_NAME);
       
    }

    #[Route('/api/order', name: 'app_order', methods : ['POST'])]
    public function index(Request $request, JWTEncoderInterface $jwt, MailerInterface $mailer)
    {
        $currentDiscountRate = null;
    // ensuite il faut créer une nouvelle commande et asocier les lignes correspondantes
    $order = new Order();
    // $token = $request->get('token');
  
    // $data = $request->request->all();

    // récupération du contenu du panier 
    $cart = $request->get('cart');
    $id = $request->get('userId');
    $total = $request->get('total');

    // récupération de la réduction, il elle est définie 
    $currentDiscountJson = $request->get('currentDiscount');
    $currentDiscount = json_decode($currentDiscountJson, true);  


    // Ici il faut récupérer le nombre d'articles et comparer avec les 
    // réductions en base de données pour appliquer la bonne. 

    if(isset($currentDiscountJson) && count($currentDiscount) > 0){
        $currentDiscount = json_decode($currentDiscountJson, true);    
        $currentDiscountId = $currentDiscount[0]['id'];
        $currentDiscountRate = $currentDiscount[0]['rate'];

        $discountBdd  = $this->em->getRepository(Discount::class)->find($currentDiscountId);
        // on attribue le discount à la commande
        $order->setDiscount($discountBdd);
    }
    
    
    // récupération de la réduction en bdd : 
    // $discountBdd = $this->em->getRepository(Discount::class)->find($currentDiscount['id']);

    
    // récupération de l'adresse utilisateur 
    $address = $request->get('address');

    // transformation de l'adresse en tableau associatif 
    $jsonAddress = json_decode($address, true);

    // transformation du panier en tableau associatif
    $cartData = json_decode($cart, true);
   
    // déclaration d'une var userId à null
    $userId = null;

    $token = null;
    
    // récupération du contenu du header d'authentification
    $authorizationHeader = $request->headers->get('Authorization');
    
    // Si ce header existe, on va récupérer le token.
    // if ($authorizationHeader) {
    //     $headerToArray = explode(",", $authorizationHeader);
    //     $token = trim($headerToArray[1]);

    //     // $headerToArray = explode(" ", $authorizationHeader);
    //     // $token = trim($headerToArray[1]);
    //     // si le token est different de la chaine de caractère nulle, alors 
    //     // on le décode et on récupère l'id de l'utilisateur 
    //     if($token != 'null'){
    //         $data = $jwt->decode($token);
    //         $userId = $data['id'];
    //         // comparaison de l'id du token et de l'id récupérer dans le localStorage : 
    //         // en valeur et non en type pour vérifier que l'id n'a pas été modifiée
    //         // evite les usurpations 
    //         // if($userId != $id ){
    //         //     throw new HttpException(404, "Erreur"); 
    //         // }
    //     }
    
    // } 

    
    // si un utilisateur est connecté, on l'associe à la commande 
    if($user = $this->getUser()){
        
        // on récupère l'utilisateur connecté ( et non l'id )
        $userId = $this->em->getRepository(User::class)->find($this->getUser()->getId());


        $order->setUser($userId);
    
        // on vérifie que l'utilisateur a bien une adresse  :
        $userAddress = $this->em->getRepository(Address::class)->findOneByUser($userId);
    
        // SI il n'a pas d'addresse, on lui attribue celle envoyée 
        if(!$userAddress){
            $userAddress = new Address();
            $userAddress->setName($jsonAddress['name']);
            $userAddress->setFirstname($jsonAddress['firstname']);
            $userAddress->setLastName($jsonAddress['lastname']);
            $userAddress->setCompany($jsonAddress['company']);
            $userAddress->setAddress($jsonAddress['address']);
            $userAddress->setPostal($jsonAddress['postal']);
            $userAddress->setCity($jsonAddress['city']);
            $userAddress->setCountry($jsonAddress['country']);
            $userAddress->setPhone($jsonAddress['phone']);
        }

        // on attribue l'adresse à l'utilisateur. 
        $userAddress->setUser($user);
        
    } else {
        // Sinon la commande n'aura pas d'utilsateur, elle sera publique 
        $userAddress = new Address();
        $userAddress->setName($jsonAddress['name']);
        $userAddress->setFirstname($jsonAddress['firstname']);
        $userAddress->setLastName($jsonAddress['lastname']);
        $userAddress->setCompany($jsonAddress['company']);
        $userAddress->setAddress($jsonAddress['address']);
        $userAddress->setPostal($jsonAddress['postal']);
        $userAddress->setCity($jsonAddress['city']);
        $userAddress->setCountry($jsonAddress['country']);
        $userAddress->setPhone($jsonAddress['phone']);

    
    
    }
    
    $order->setAddress($userAddress);


    $this->em->persist($order);
    $this->em->persist($userAddress);

   
    
    // déclaration du conteneur de lignes de commande 
    $orderLineList = [];
    // Puis il faut créer des lignes de commande pour chaque articles

    // tableau contenant la liste des url : 
    $urlList = [];
    
    foreach($cartData as $cart){
        
        $orderLine = new OrderLine();
        $orderLine->setQuantity($cart['quantity']);
        $orderLine->setPrice($cart['price']);
        $orderLine->setTotal($cart['quantity'] * $cart['price'] ); 
        $orderLine->setOrdered($order);
        $orderLine->setPictureId($cart['idPicture']);
        $orderLineList[] = $orderLine;
    }

        
    
    // recherche des urls : 
    foreach($orderLineList as $orderLine){
        $picture = $this->em->getRepository(Picture::class)->find($orderLine->getPictureId());
        $pictureName = $picture->getFileName();
        $storage = GoogleCloudStorage::getInstance(AlbumController::BUCKET_NAME);
        $storage->setObjectName($pictureName);
        $url = $storage->getObject()
                ->signedUrl(new DateTime( '+ 7 days '), [
                    'version' => 'v4',
                    'private' => true,
                    
                ]);
        $urlList[$pictureName] = $url;
        

    //  persist des orderLines : 

    
    $this->em->persist($orderLine);
    
    $this->em->flush();

    $orderId = $order->getId();

    
    // attendre la confirmation de l'api de paiement afin de valider la commande 

        // $user = $this->getUser()->getId();
      
        // preg_match() est une fonction de PHP qui permet de chercher une expression régulière dans une chaîne de caractères.

        // L'expression régulière /Bearer\s(\S+)/ signifie : chercher une chaîne de caractères qui commence par "Bearer ", suivie d'un ou plusieurs caractères non-blancs, et enregistrer cette sous-chaîne dans la variable $matches.
        
        // Plus précisément :
        
        // Bearer est une chaîne de caractères littérale, qui doit apparaître exactement telle quelle dans la chaîne à chercher.
        // \s est un caractère d'échappement qui représente un espace blanc (espace, tabulation, retour à la ligne, etc.).
        // (\S+) est un groupe de capture qui correspond à une ou plusieurs occurrences de caractères non-blancs. Les caractères capturés sont enregistrés dans la variable $matches sous forme d'un tableau, où $matches[0] contient la chaîne correspondant à l'expression régulière entière, et $matches[1] contient la sous-chaîne capturée par le premier groupe de capture (ici, (\S+)).
        // Ainsi, dans le code que j'ai proposé précédemment, nous utilisons preg_match() pour chercher l'expression régulière /Bearer\s(\S+)/ dans la chaîne $authorizationHeader, qui contient la valeur de l'en-tête Authorization. Si l'expression régulière est trouvée dans la chaîne, la sous-chaîne correspondant au groupe de capture (\S+) (c'est-à-dire le token) est enregistrée dans la variable $matches[1].
      

        
    }

    $totalQuantity = $this->em->getRepository(OrderLine::Class)->getQuantityByOrderID($orderId);
    

    
  
    // récupération de la globale secretStripe
    $privateSecretStripeKey = $this->getParameter('app.secretStripe');

    // initialisation d'une nouvelle session de paiment 
    $payment = new StripeInit($privateSecretStripeKey);

    // si utilisateur, on récupère son Id pour le passer à la session de paiement 
    if($user){
        $userId = $user->getId();
    } else {
        $userId = null;
    }
    $checkout_session = $payment->startPayment($cartData, $orderId, $request, $userId, $totalQuantity, $urlList, $currentDiscountRate);
    

    // Récupérez l'ID de la session de paiement
 
    $checkout_session_id = $checkout_session->id;
  


    // mise à jour de la commande en bdd avec le checkout id ( stripe Id)
    $order->setStripeId($checkout_session_id);
    $this->em->persist($order);
    $this->em->persist($userAddress);
    $this->em->flush();

    

    // renvoie de l'url vers le front-end. 
    return new JsonResponse($checkout_session->id);

   
    }   



    // get all orders or with user param
    #[Route('/api/order', name: 'app_order_get', methods : ['GET'])]
    public function getOrder(Request $request, JWTEncoderInterface $jwt): JsonResponse
    {
        $userId = $request->query->get('userId');

        if($userId){
            $order = $this->em->getRepository(Order::class)->findOrderByUserId($userId);
        } 
        
        else {
            $order = $this->em->getRepository(Order::class)->findAll();
        }
       

        $jsonOrder = $this->serializer->serialize($order, 'json', ['groups' => 'getOrders']);

        
    

        return new JsonResponse($jsonOrder, Response::HTTP_OK, [], true);

    }


    #[Route('/api/order/{id}', name: 'app_order_get_id', methods : ['GET'])]
    public function getOrderById($id, Order $order, Request $request): JsonResponse
    {


        $jsonOrder = $this->serializer->serialize($order, 'json', ['groups' => 'getOrders']);
    

        return new JsonResponse($jsonOrder, Response::HTTP_OK, [], true);

    }
 
    //  update an order ( status update after payment)
    #[Route('/api/order/{id}', name: 'app_order_update', methods : ['PUT'])]
    public function updateOrderById($id, Order $order, Request $request): JsonResponse
    {
        $checkout_session_id = $order->getStripeId();
       
        $order->setStatus('done');
        $this->em->persist($order);
        $this->em->flush();


        return new JsonResponse('Updated', Response::HTTP_OK, [], true);

    }


}
