<?php 

namespace App\Tests\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    // initialisation du client et du crawler
    public function init( string $method, string $url){
        $client = static::createClient();
        $crawler = $client->request($method, $url);
        $response = $client->getResponse();

        // return [$crawler, $response];
        return [
            'crawler' => $crawler,
            'response' => $response
        ];
    }

    // // test utlisateur non existant -> null 
    // public function testGetOneUser(){
    //     $init = $this->init('GET', '/api/users/230');
    //     $response = $init['response'];
    //     // $client = static::createClient();
    //     // $crawler = $client->request('GET', '/api/users/230');
    //     // $response = $client->getResponse();

    //     $this->assertResponseStatusCodeSame(Response::HTTP_OK);

    //     $this->assertNull(json_decode($response->getContent()));
    // }

    // // test de récupération de tous les utlisateurs 
    // public function testGetAllUsers(){
    //     $client = static::createClient();
    //     $crawler = $client->request('GET', '/api/users');
    //     $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    // }

    // // Récupération de l'utilisateur courant ( connecté )
    // public function testGetCurrentUser(){
    //     $client = static::createClient();
    //     $crawler = $client->request('GET', '/api/user');
    //     $this->assertResponseStatusCodeSame(Response::HTTP_OK);

    // }

    // test de restriction de l'url 
    public function testAuthPageIsRestricted(){
        $init = $this->init('GET', '/api/user');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}

