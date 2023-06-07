<?php 

namespace App\Classe;

use DateTime;
use Exception;
use Stripe\Coupon;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Customer;
use App\Entity\Order;
use DateTimeImmutable;
use App\Entity\Address;
use App\Entity\Invoice;
use App\Entity\Picture;
use Stripe\InvoiceItem;
use App\Entity\OrderLine;
use Stripe\Checkout\Session;
use App\Services\MailerService;
use Symfony\Component\Uid\Uuid;
use App\Services\TemplatedEmail;
use App\Controller\AlbumController;
use App\Services\TemplatedEmailService;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Storage\GoogleCloudStorage;
use Stripe\Exception\InvalidRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Stripe\Exception\UnexpectedValueException;
use Symfony\Component\HttpFoundation\Response;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;




class StripeInit{

    private string $clientSecret;

    public function __construct($clientSecret){
        $this->clientSecret = $clientSecret;
        Stripe::SetApiKey($this->clientSecret);
    }


    /**
     * startPayment
     *
     * initalisation d'une transaction
     *
     * @param type $cartArray : panier 
     * @param type $orderId : numéro de commande
     * @param type $request Request
     * @return type $checkout session Session stripe en cours 
     */

    public function startPayment($cartArray, $orderId, Request $request, $userId, $totalQuantity, $urlList, $currentDiscountRate = null){

        // initialisation d'une réduction = array vide
        $discounts = [];

        // création d'une coupon de réduction, si la rate != null : 
        if($currentDiscountRate != null){

           $coupon = Coupon::create([
            'percent_off' => $currentDiscountRate,
            'duration' => 'once',
           ]);
           $discounts = [
            [
                'coupon' => $coupon->id,
            ],
        ];
        }

        // création des metadatas ( facultatif)
        $metadata = [
            'user_id' => $userId, 
        ];

        // instanciation de la session de paiment 
        $checkout_session = Session::create([
            'line_items' => [
                    array_map( fn(array $product) => [
                        'quantity' => $product['picture_quantity'],
                        // 'price' => $product['price'] * 100
                        'price_data' => [
                            'currency' => 'EUR', 
                            'product_data' => [
                                'name' => 'photo'
                            ],
                            'unit_amount' => $product['picture_price'] * 100,          
            ]], $cartArray )
                ],
            'mode' => 'payment',
            'success_url' => "http://localhost:5173/success/" .  $orderId ,
            'cancel_url' => "http://localhost:5173/failure",
            'discounts' => $discounts,
            'expires_at' => time() + (12 * 60 * 60 ),
            'metadata' => $metadata,
        ]);

        return $checkout_session;
    }


    // public function sendInvoice($sessionId){

    //     $checkoutSession = Session::retrieve($sessionId);
       
    //     $discount = $checkoutSession->discounts;
       
      
        
    //     $customerEmail = $checkoutSession->customer_details->email;
    //     $customerId = $checkoutSession->metadata->user_id;
        
        
       

    //     // Vérifier si le client existe déjà dans Stripe
        
    //     try {
    //         $customer = Customer::retrieve([
    //             'id' => $customerId,
    //         ]);
    //     } catch (\Stripe\Exception\InvalidRequestException $e) {
    //         // Si le client n'existe pas, créer un nouveau client dans Stripe
    //         $customer = Customer::create([
    //             'id' => $customerId,
    //             'email' => $checkoutSession->customer_details->email,
    //         ]);
    //     }
        
        
    //     $invoiceCreateParams = [
    //         'customer' => $customer->id,
    //         'collection_method' => 'send_invoice',
    //         'days_until_due' => 30,
    //         'metadata' => [
    //           'checkout_session_id' => $sessionId,
    //           'line_items' => $checkoutSession->line_items,
    //           'amount_total' => $checkoutSession->amount_total,
           
              
    //           // Ajoutez ici des métadonnées supplémentaires pour la facture
    //         ],
            
            
    //       ];

    //     $invoice = \Stripe\Invoice::create($invoiceCreateParams);

        
    //     // dd($checkoutSession->allLineItems($sessionId));
    //     // dd($checkoutSession->line_items);
    //       // Loop through each item in the checkout session and create an Invoice Item for each
    //     foreach ($checkoutSession->allLineItems($sessionId) as $item) {
    //     // Create an Invoice Item with the Price, and Customer you want to charge
    //     $invoiceItem = InvoiceItem::create([
    //         'customer' => $customerId,
    //         'price' => $item['price'],
    //         'quantity' => $item['quantity'],
    //         'invoice' => $invoice->id
    //     ]);
    //     }
      
    //     $invoice->sendInvoice();
    // }

  
    /**
     * handle
     *
     * webhook sécurisé ( avec signature )
     *
     * @param type $request Request, $webhookSecret
     * @return type void
     */

    public function handle(Request $request, $webHookSecret, $em, $serializer, $mailer){ 
        $sigHeader = $request->headers->get('stripe-signature');
        $payload = @file_get_contents('php://input');

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $webHookSecret
            );
         
          } catch (UnexpectedValueException $e) {
            // Invalid payload
            return new HttpException(400, $e->getMessage());
          }
          catch(SignatureVerificationException $e) {
            // Invalid signature
            return new HttpException(400, $e->getMessage());
        }

        // récupérer le type d'événement
        $eventType = $event->type;

        
      
         
        //  $user = $em->getUser();
        //  dd($user->getId());
        
        // Traiter l'événement en fonction de son type
        switch ($eventType) {
            case 'checkout.session.completed':
                
                // on récupére la commande correspondante 
                // $this->em->getRepository(Order::class)

                // récupération de l'id de session via l'event
                $sessionId = $event->data->object->id;

                // $customerEmail = $checkoutSession->data->object->customer_details->email;

                // récupération de l'userId 
                $userId = $event->data->object->metadata->user_id;

                // récupération de l'email 
                $userEmail = $event->data->object->customer_details->email;

                // récupération du total TTc : 
                $totalTTC =$event->data->object->amount_total / 100;

                // récupération de la commande correspondante
                $order = $em->getRepository(Order::class)->findOneBy(['stripe_id' => $sessionId]);

                // récupération des urls 
                $urls = $em->getRepository(OrderLine::class)->findOrderLinesByOrderId($order->getId());

                // récupération des orlderlines 
                $orderLines = $em->getRepository(OrderLine::class)->findOrderLineByOrderId($order->getId());

                
                // Si la commande est récupérée : 
                if($order){
                    // on passe le sattus de la commande à 'done'
                    $order->setStatus('done');

                        // instanciation d'une nouvelle facture
                        $invoice = new Invoice();
    
                        // génération du numéro de facture à partir du nom + date
                        $uniqId = Uuid::v4();
                        $dateNow = new DateTimeImmutable();
                        $formatDate = $dateNow->format('YmdHis');
    
                        // récupération du nom de l'utilsateur 
                        $userName = $event->data->object->customer_details->name;
                     
                        // edition du name de facture
                        $invoiceNumber = $userName . $formatDate;
    
                        // récupération de l'adresse de facturation correspondante. 
                       
                        // if($userId){
                        //     $address = $em->getRepository(Address::class)->findOneBy(['user' => $userId]);
                        // } else {
                        //     $address = null;
                        // }
                        
                        $order = $em->getRepository(Order::class)->findOneByStripeId($sessionId);
                        $address = $order->getAddress();
    
                        $invoice->setAddress($address);
                        $invoice->setOrdered($order);
                        $invoice->setNumber($invoiceNumber);
                        $invoice->setTotal($totalTTC);


                        try {
                            $em->persist($invoice);
                        } catch (Exception $e){
                            throw  new HttpException(500, $e->getMessage());
                        }
                    

                    try {
                        $em->flush();
                    } catch (Exception $e){
                        throw  new HttpException(500, $e->getMessage());
                    }

                // initialisation du mail : 

                $html = "<h2>Veuillez trouver ci-dessous les liens de téléchargement pour vos photos</h2>";
                $html .= "<br />";
                $html .= "<p>Pour télécharger vos photos cliquez sur les liens, puis cliquez droit et ' enregistrer l'image sous' </p>";
                $html .= "<br />";

                $fileNameList = [];
                $signedUrlList = [];
                // récuparation des urls./  
                $storage = GoogleCloudStorage::getInstance(AlbumController::BUCKET_NAME);
                foreach($urls as $key => $url){
                    $storage->setObjectName($url['file_name']);
                    $urlSigned = $storage->getObject()
                            ->signedUrl(new DateTime( '+ 7 days '), [
                                'version' => 'v4',
                                'private' => true,
                                
                            ]);
                $fileNameList[$url['file_name']] = $urlSigned;         
                



                $html .= "<p>" . "<a href=\"" . $urlSigned . "\" download,>" . $url['file_name'] . "</a>" . "</p><br />";
                }

                $html .= "<br/>";
                $html .= "<p>Notez que vos liens sont disponibles une semaine</p>";
                $html .= "<hr>";
                $html .= "<h1>Votre facture</h1>";
                $html .= "<table>";
                $html .= "<tr>";
                $html .= "<th>Produit</th>"; 
                $html .= "<th>Quantité</th>"; 
                $html .= "<th>Prix unitaire</th>"; 
                



                // détail de la facture : 
                foreach($orderLines as $orderline){
                    $pictureId = $orderline->getPictureId();;
                    $picture = $em->getRepository(Picture::class)->find($pictureId);

                    $html .= "</tr>";
                    $html .= "<td>" . $picture->getName() . "</td>";
                    $html .= "<td>" . $orderline->getQuantity() . "</td>";
                    $html .= "<td>" . $orderline->getPrice() . "</td>";
                    $html .= "</tr>";
                    

                }

                $html .= "<hr>";
                $html .= "<p>Total" . $totalTTC . " € </p>";
                $html .= '<hr>';
                $html .= '<Merci pour votre commande';

                $mailerService = new TemplatedEmailService($mailer, $serializer);
                $mailerService->setTotal($totalTTC);
                $mailerService->setOrderLines($orderLines);
                $mailerService->send('Nico@example.com', $userEmail, 'Merci :)', $fileNameList);

                    
                }

                break;

            case 'invoice.paid':

                if($order){
                    // on passe le sattus de la commande à 'done'
                    $order->setStatus('done');
                    try {
                        $em->flush();
                    } catch (Exception $e){
                        throw  new HttpException(500, "Une erreur est survenue");
                    }
                }
                
                
                // Gérer l'événement payment_intent.succeeded
                // par exemple, marquer la commande comme payée ou envoyer un email de confirmation de commande
                break;
            // Ajouter des cases pour gérer d'autres types d'événements
            default:
                // Ne rien faire pour les événements non pris en charge
                break;
        }
        
        
    }

    /**
     * handleWebHook
     *
     * vérifie méthode POST 
     *
     * @param type $request Request, $webhookSecret
     * @return type void
     */

    public function handleWebhook(Request $request, $webHookSecret, $em, $serializer, $mailer){
        // Vérifiez que la méthode HTTP est POST
        if ($request->getMethod() == 'POST') {
            // Traitez le webhook Stripe
            $this->handle($request, $webHookSecret, $em, $serializer, $mailer);
        } else {
            // Renvoyer une erreur si la méthode HTTP n'est pas POST
            return new Response('Méthode HTTP non autorisée', 405);
        }
    }


    /**
     * retriveSession
     *
     * récupération d'une session existante 
     *
     * @param type $session_id Identificateur de session 
     * @return type $session La session récupérér 
     */
    
    public function retrieveSession($session_id){

        $session = Session::retrieve($session_id);

        return $session;
    }
}


