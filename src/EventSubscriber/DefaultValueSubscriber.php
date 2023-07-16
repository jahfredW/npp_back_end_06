<?php 

// src/EventListener/DatabaseActivitySubscriber.php
namespace App\EventSubscriber;

use App\Entity\Cart;
use App\Entity\User;
use App\Entity\Album;
use App\Entity\Order;
use App\Entity\Address;
use App\Entity\Invoice;
use App\Entity\Picture;
use App\Entity\CartLine;
use App\Entity\Category;
use App\Entity\Discount;
use App\Entity\Products;
use Doctrine\ORM\Events;
use App\Entity\OrderLine;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;

class DefaultValueSubscriber implements EventSubscriberInterface
{
    // this method can only return the event names; you cannot define a
    // custom method name to execute when each event triggers
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // if this subscriber only applies to certain entity types,
        // add some code to check the entity type as early as possible
        if (!$entity instanceof Picture && !$entity instanceof Album  && !$entity instanceof Category
        && !$entity instanceof Order && !$entity instanceof Invoice && !$entity instanceof Address 
        && !$entity instanceof Discount && !$entity instanceof Products && !$entity instanceof Cart 
        && !$entity instanceof CartLine   ) {
            return;
        } 

        if ( $entity instanceof Order){
            $entity->setStatus('holding');
            $entity->setIsActive(false);
        }

        if ( $entity instanceof Address){
            $entity->setisSelected(false);
            $entity->setUpdatedAt(new \DateTimeImmutable());
        }

        if ( $entity instanceof Picture){
            $entity->setIsActive(true);
            $entity->setIsCover(false);
        }

        if ( $entity instanceof Album){
            $entity->setIsActive(true);
            $date = new \DateTimeImmutable();
            $entity->setExpireAt($date->add(new \DateInterval('P30D')));
        }

        if( $entity instanceof Products){
            $entity->setIsActive(true);
        }

        if( $entity instanceof Cart){
            $entity->setStatus('pending');
        }
       
        $entity->setCreatedAt(new \DateTimeImmutable);
       
        

    }
}
