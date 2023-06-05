<?php

namespace App\Controller;

use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AddressController extends AbstractController
{
    private $serializer;
    private $em;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $em){
        $this->serializer = $serializer;
        $this->em = $em;
    }

    // #[Route('/api/address', name: 'address_id', methods: ['GET'])]
    // public function getAdressIdByUSerID(Request $request): JsonResponse
    // {
    //     $userId = $request->query->get('userId');

        
    //     $addressID = -1;
        

    //     $userAddress = $this->em->getRepository(Address::class)->findOneByUser($userId);

    //     if($userAddress != null){
            
    //         $addressID = $userAddress->getId();
    //     }
        
        
    //     return new JsonResponse($addressID);
    // }

    #[Route('/api/address', name: 'address_id', methods: ['GET'])]
    public function getWholeAddressByUSerID(Request $request): JsonResponse
    {
        $userId = $request->query->get('userId');
        
        
        $addressID = -1;
        

        $userAddress = $this->em->getRepository(Address::class)->findOneByUser($userId);


        $jsonAddress = $this->serializer->serialize($userAddress, 'json', ['groups' => 'getAdress']);
        

        return new JsonResponse($jsonAddress);
    }

    #[Route('/api/address', name: 'address_update', methods: ['PUT'])]
    public function updateAddressByUSerID(Request $request): JsonResponse
    {
        $userId = $request->query->get('userId');

        $content = $request->toArray();

        
        $addressID = -1;
        

        $userAddress = $this->em->getRepository(Address::class)->findOneByUser($userId);

        if($userAddress){
            
            if(isset($content['name']) && !empty($content['name'])){
                $name = $content['name'];
                $userAddress->setName($name); 
            }

            if(isset($content['firstname']) && !empty($content['firstname'])){
                $firstName = $content['firstname'];
                $userAddress->setFirstName($firstName); 
            }

            if(isset($content['lastname']) && !empty($content['lastname'])){
                $lastName = $content['lastname'];
                $userAddress->setLastName($lastName); 
            }

            if(isset($content['company']) && !empty($content['company'])){
                $company = $content['company'];
                $userAddress->setCompany($company); 
            }

            if(isset($content['address']) && !empty($content['address'])){
                $address = $content['address'];
                $userAddress->setAddress($address); 
            }

            if(isset($content['postal']) && !empty($content['postal'])){
                $postal = $content['postal'];
                $userAddress->setPostal($postal); 
            }

            if(isset($content['city']) && !empty($content['city'])){
                $city = $content['city'];
                $userAddress->setCity($city); 
            }

            if(isset($content['country']) && !empty($content['country'])){
                $country = $content['country'];
                $userAddress->setCountry($country); 
            }

            if(isset($content['phone'])){
                $phone = $content['phone'];
                $userAddress->setPhone($phone); 
            }



        } else {
            throw new HttpException(Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->em->persist($userAddress);
            $this->em->flush();
        } catch (\Exception $e){
            throw new HttpException($e->getMessage());
        }
        
        

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

}
