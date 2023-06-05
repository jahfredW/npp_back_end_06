<?php


namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTDecodedEvent;


class JWTDecodedListener{
    /**
 * @var RequestStack
 * @var EntityManagerInterface
 */
private $requestStack;
private $entityManager;


/**
 * 
 * @param RequestStack $requestStack
 */
public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack )
{
    $this->requestStack = $requestStack;
    $this->entityManager = $entityManager;
}

/**
 * @param JWTDecodedEvent $event
 * @param EntityManagerInterface $entityManager
 *
 * @return void
 */


public function onJWTDecoded(JWTDecodedEvent $event){

    // $request = $this->requestStack->getCurrentRequest();
    // dd($request);

    $payload = $event->getPayload();
    // dd($payload['username']);
    // $user = $this->entityManager->getRepository(User::class)->findOneByEmail($payload['username']); 
    // dd($user);
    // $payload['id'] = $user->getId();
    


    $event->setPayload($payload);

}
}