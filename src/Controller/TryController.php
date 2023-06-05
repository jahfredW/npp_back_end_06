<?php

namespace App\Controller;

use DateTimeImmutable;
use App\Entity\Address;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TryController extends AbstractController
{
    #[Route('/api/try', name: 'app_try', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $address = $em->getRepository(Address::class)->findOneBy(['user' => 2]);
        dd($address);
    //     $uniqId = Uuid::v4();
    //     $date = new DateTimeImmutable();
    //     $formatDate = $date->format('YmdHis');
    //     dd($formatDate);
    }
}
