<?php

namespace App\Repository;

use App\Entity\CartLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CartLine>
 *
 * @method CartLine|null find($id, $lockMode = null, $lockVersion = null)
 * @method CartLine|null findOneBy(array $criteria, array $orderBy = null)
 * @method CartLine[]    findAll()
 * @method CartLine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartLine::class);
    }

    public function save(CartLine $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CartLine $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CartLine[] Returns an array of CartLine objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

   public function findOneByPictureId($pictureId): ?CartLine
   {
       return $this->createQueryBuilder('c')
           ->andWhere('c.picture_id = :val')
           ->setParameter('val', $pictureId)
           ->getQuery()
           ->getOneOrNullResult()
       ;
   }

      public function findByCartId($cartId): array
   {
       return $this->createQueryBuilder('c')
           ->andWhere('c. = :val')
           ->setParameter('val', $value)
           ->orderBy('c.id', 'ASC')
           ->setMaxResults(10)
           ->getQuery()
           ->getResult()
       ;
   }

   // retourne la sum des articles
   public function findBySumOfArticles($cart): ?int
   {
    return $this->createQueryBuilder('c')
        ->select('SUM(c.quantity)')
        ->andWhere('c.cart = :val')
        ->setParameter('val', $cart)
        ->getQuery()
        ->getSingleScalarResult();
   }

   // returne la liste des pictures 
   public function findPictureId($cart): ?array
   {
    return $this->createQueryBuilder('c')
        ->select('c.picture_id')
        ->andWhere('c.cart = :val')
        ->setParameter('val', $cart)
        ->getQuery()
        ->getResult();
   }

   // returne la liste des pictures 
   public function findByPictureIdAndCart($pictureId, $cart): ?CartLine
   {
    return $this->createQueryBuilder('c')
        ->andWhere('c.cart = :val')
        ->andWhere('c.picture_id = :pict')
        ->setParameter('val', $cart)
        ->setParameter('pict', $pictureId)
        ->getQuery()
        ->getOneOrNullResult();
   }
}
