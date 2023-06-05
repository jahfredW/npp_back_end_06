<?php

namespace App\Controller;

use App\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InvoiceController extends AbstractController
{
    private $em;
    private $serializer;

    public function __construct(EntityManagerInterface $em, SerializerInterface $serializer){
        $this->em = $em;
        $this->serializer = $serializer;
    }
    #[Route('/api/invoice/{orderId}', name: 'app_invoice')]
    public function index(Request $request, $orderId): JsonResponse
    {
        $invoice = $this->em->getRepository(Invoice::class)->findInvoiceByOrderId($orderId);
       

        $invoiceJson = $this->serializer->serialize($invoice, 'json', ['groups' => 'getInvoice']);

        
        return new JsonResponse($invoiceJson, Response::HTTP_OK, [], true);
    }
}
