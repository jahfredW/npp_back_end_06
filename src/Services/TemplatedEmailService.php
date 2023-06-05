<?php 

namespace App\Services;

use RuntimeException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\SerializerInterface;





class TemplatedEmailService 
{
    private $mailer;
    private $serializer;
    private $total;
    private $orderLines;

    public function __construct(MailerInterface $mailer, SerializerInterface $serializer){
        // non obligatoire dans php 8
        $this->mailer = $mailer;
        $this->html = "invoice/index.html.twig";
        $this->serializer = $serializer;
        $this->total = 0;
        $this->orderLines = [];
        $this->email = new TemplatedEmail();
    }

    public function send( string $from, string $to, string $subject, array $fileNameList) : void
    {
        // création du mail 
        $this->email
        ->from($from)
        ->to($to)
        ->subject($subject)
        ->htmlTemplate($this->html)
        ->context([
            'fileNameList' => $fileNameList,
            'total' => $this->total,
            'orderLines' => $this->orderLines
        ]);

        $this->mailer->send($this->email);
        
    }

    public function buildHtml($text){
        $this->html .= $text;
    }

    public function buildAttachment($invoice, $filename ){

        // sérialisation de la facture en JSON STRING 
        // 
        $jsonString = $this->serializer->serialize($invoice, 'json', ['groups' => 'getInvoice']);
       
        // écriture du JSON dans le fichier : 
        if (file_put_contents('../public/' . $filename .'.txt', $jsonString) === false) {
            throw new RuntimeException("Failed to write JSON data to file $filename");
        }


    }

    public function addAttachment($fileName){
        $this->email->addPart(new DataPart(new File('../public/' . $fileName . '.txt'),));
    }

    public function getTotal(){
        return $this->total;
    }

    public function setTotal($value){
        $this->total = $value;
        return $this;
    }

    public function getOrderLines(){
        return $this->orderLines;
    }

    public function setOrderLines($value){
        foreach ($value as $orderLine){
            $newOrderLine = [ 
            'quantity' => $orderLine->getQuantity(),
            'price' => $orderLine->getPrice(),
            ];
            
            $this->orderLines[] = $newOrderLine;
        }
        return $this;
    }
}