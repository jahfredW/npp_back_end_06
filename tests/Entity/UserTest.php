<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\DataFixtures\UserFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

class UserTest extends KernelTestCase
{

    /**
     * @var AbstractDatabaseTool
     */
    protected $databaseTool;
    protected $entityManager;
    protected $validator;


    public function setUp(): void
    {
        parent::setUp();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();

    }

    // fonction de getter de l'entité User 
    public function getEntity() : User
    {
        return (new User())
        ->setEmail('iuihiuhp@test.com')
        ->setRoles(["ROLE_USER"])
        ->setPassword('0000')
        ->setPseudo('caca');
        
        
    }

    public function assertHasErrors(User $user, int $number = 0): void
    {
        // boot du kernel
        self::bootKernel();
        // récupération du container 
        $container = static::getContainer();
        // validation
        // $errors = $this->validator->validate($user);
        $errors = $container->get(ValidatorInterface::class)->validate($user);

        // récupération des erreurs de validation
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach($errors as $error) {
            $messages[] = $error->getPropertyPath() . ' => ' . $error->getMessage();
        }
        $this->assertCount($number, $errors, implode(', ', $messages));
    }

    public function testEmailIsValid(): void
    {
        
        // instanciation d'une nouvelle entité User 
        $user = $this->getEntity();

        $this->assertHasErrors($user, 0);
        
    }


    public function testUserIdIsInteger(): void
    {
        // self::bootKernel();
        // $container = static::getContainer();

        // appel du service EntityManager de doctrine poour pouvoir 
        // utiliser doctrine EntityManagerInterface 
        // $user = $container->get('doctrine.orm.entity_manager')->find(User::class, 48);

        $this->databaseTool->loadFixtures([
            'App\DataFixtures\UserFixtures'
        ]);

        // $this->databaseTool->loadAliceFixture([
        //     dirname(__DIR__) . '/fixtures/UserTestFixtures.yaml'
        // ]);

        $users = $this->entityManager->getRepository(User::class)->count([]);

        $this->assertEquals(30, $users);
    }

    public function testEmailUnique() : void 
    {
        $this->databaseTool->loadAliceFixture([
            dirname(__DIR__) . '/fixtures/UserTestUniqueEmail.yaml'
        ]);

        // $this->databaseTool->loadFixtures([
        //     'App\DataFixtures\UserFixtures'
        // ]);

        $user = $this->getEntity();
        $user->setEmail('fred.gruwe@gmail.com');
        $this->assertHasErrors($user, 1);

    }
}
