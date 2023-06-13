<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class SecurityControllerTest extends WebTestCase
{
    private $databaseTool;
    private $client;
    private $tokenManager;
    private $entityManager;

    // comme un constructeur mais pour les tests 
    // est exécutée avant chaque méthode de test 
    // héritée de WebTestCase
    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->tokenManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
    //     $this->databaseTool->loadAliceFixture([
    //         dirname(__DIR__) . '/fixtures/UserTestUniqueEmail.yaml'
    //     ]);
    }
    
    // test de l'authentification 
    function testLoginAuthentication(){

        // request(string $method, string $uri, array $parameters = [], array $files = [], array $server = [], 
        // ?string $content = null, bool $changeHistory = true)
        $this->client->request('POST', '/api/login_check', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'fred.gruwe@gmail.com',
            'password' => '0000',
        ]));

        $response = $this->client->getResponse();
       
        $this->assertSame(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    // test d'accès à une route réservée 
    function testAccessToSecureResourceByUser(){
        $this->databaseTool->loadAliceFixture([
            dirname(__DIR__) . '/fixtures/UserTestUniqueEmail.yaml'
        ]);

        // Charger un utilisateur existant depuis la base de données
        $user = $this->loadUserFromDatabase('fred.gruwe@gmail.com');
        
        // Créer le token en utilisant l'utilisateur chargé
        $token = $this->tokenManager->create($user);
        

        // Ajouter le token dans l'en-tête d'autorisation de la requête
        $this->client->request('GET', '/api/users/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $response = $this->client->getResponse();

        $this->assertSame(403, $response->getStatusCode());
    }

        // test d'accès à une route réservée 
        function testAccessToSecureResourceByAdmin(){
            $this->databaseTool->loadAliceFixture([
                dirname(__DIR__) . '/fixtures/AdminTestFixtures.yaml'
            ]);
    
            // Charger un utilisateur existant depuis la base de données
            $user = $this->loadUserFromDatabase('fred.gruwe@gmail.com');
            
            // Créer le token en utilisant l'utilisateur chargé
            $token = $this->tokenManager->create($user);
            
    
            // Ajouter le token dans l'en-tête d'autorisation de la requête
            $this->client->request('GET', '/api/users/1', [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            ]);
    
            $response = $this->client->getResponse();
    
            $this->assertSame(200, $response->getStatusCode());
        }
    

    private function loadUserFromDatabase(string $email): User
    {
        // Charger l'utilisateur depuis la base de données
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);

        // Vérifier si l'utilisateur existe
        if (!$user) {
            throw new \Exception('L\'utilisateur avec l\'email "'.$email.'" n\'a pas été trouvé dans la base de données.');
        }

        return $user;
    }
}