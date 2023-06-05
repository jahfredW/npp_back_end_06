<?php 

namespace App\Services;

use RuntimeException;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Serializer\SerializerInterface;



interface HTMLBuilder
{
    public function buildHtml($text);
} 



class MailerService implements HTMLBuilder
{
    private $mailer;
    private $serializer;

    public function __construct(MailerInterface $mailer, SerializerInterface $serializer){
        // non obligatoire dans php 8
        $this->mailer = $mailer;
        $this->html = "";
        $this->serializer = $serializer;
        $this->email = new Email();
    }

    public function send( string $from, string $to, string $subject) : void
    {
        // création du mail 
        $this->email
        ->from($from)
        ->to($to)
        ->subject($subject)
        ->html($this->html);

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
}