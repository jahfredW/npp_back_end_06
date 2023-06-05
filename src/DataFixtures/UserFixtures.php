<?php 

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher){
        $this->hasher = $hasher;
    }
    
    public function load(ObjectManager $manager): void 
    {
        $faker = Factory::create('fr_FR');
        for( $index = 0; $index < 30; $index ++){
            $user = new User();
            $user->setPseudo($faker->userName());
            $user->setEmail($faker->email());
            $user->setRoles(["ROLE_USER"]);
            $user->setPassword($this->hasher->hashPassword($user, "password"));
            $manager->persist($user);
        }

        $manager->flush();
    }
}