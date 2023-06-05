<?php

namespace App\Controller;

use App\Entity\OrderLine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderLineController extends AbstractController
{
    private $em;
    private $serializer;

    public function __construct(EntityManagerInterface $em , SerializerInterface $serializer){
        $this->em = $em;
        $this->serializer = $serializer;

    }
    // get OrderLines with orderId
    #[Route('/api/orderlines/{orderId}', name: 'app_orderLines_get', methods : ['GET'])]
    public function getOrderLines(Request $request, $orderId): JsonResponse
    {
        
        // récupération nom des images, prix pour un idOrder en param
        $pictureNameList = $this->em->getRepository(OrderLine::class)->findPictureByOrderLineId($orderId);

        // récupération du total calculé directement en base de donnée 
        // $total = $this->em->getRepository(OrderLine::class)->sumTotal($orderId);
        
        // association du total dans le 
        // $pictureNameList['total'] = $total;

        // $orderLines['pictures'] = $pictureNameList;
        // dd($pictureNameList);
        $jsonPictureNameList = $this->serializer->serialize($pictureNameList, 'json', ['groups' => 'getOrderLines']);

        // dd($jsonPictureNameList);
        return new JsonResponse($jsonPictureNameList, Response::HTTP_OK, [], true);
        // return new JsonResponse($pictureNameList);
    }
}
