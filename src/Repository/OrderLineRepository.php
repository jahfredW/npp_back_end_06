<?php

namespace App\Repository;

use App\Entity\OrderLine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderLine>
 *
 * @method OrderLine|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderLine|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderLine[]    findAll()
 * @method OrderLine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderLine::class);
    }

    public function save(OrderLine $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OrderLine $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return OrderLine[] Returns an array of OrderLine objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

// retourne la liste des ORderLines pour un OrderId donné 
   public function findOrderLineByOrderId($orderId): ?Array
   {
       return $this->createQueryBuilder('o')
           ->join('o.ordered', 'k')
           ->andWhere('o.ordered = :val')
           ->setParameter('val', $orderId)
           ->getQuery()
           ->getResult()
       ;
   }

   public function sumTotal($orderId): ?Int
   {
       return $this->createQueryBuilder('o')
            ->select('SUM(o.total)')
           ->join('o.ordered', 'k')
           ->andWhere('o.ordered = :val')
           ->setParameter('val', $orderId)
           ->getQuery()
           ->getSingleScalarResult()
       ;
   }

   public function findPictureByOrderLineId($orderLineId): ?Array
   {
       $conn = $this->getEntityManager()->getConnection();

       $sql = '
            SELECT p.name, p.file_name, o.price FROM order_line o
            INNER JOIN picture p 
            ON o.picture_id = p.id
            WHERE o.ordered_id = :orderLineId

       ';

       $stmt = $conn->prepare($sql);
       $resultSet = $stmt->executeQuery(['orderLineId' => $orderLineId]);

       return $resultSet->fetchAll();
   }

   public function getQuantityByOrderId($orderId) : int
   {
        return $this->createQueryBuilder('o')
        ->select('SUM(o.quantity)')
        ->andWhere('o.ordered = :val')
        ->setParameter('val', $orderId)
        ->getQuery()
        ->getSingleScalarResult();
   }

   public function findOrderLinesByOrderId($orderId) : ?Array
   {
    $conn = $this->getEntityManager()->getConnection();

    $sql = '
         SELECT p.file_name FROM order_line o
         INNER JOIN picture p 
         ON o.picture_id = p.id
         WHERE o.ordered_id = :orderId

    ';

    $stmt = $conn->prepare($sql);
    $resultSet = $stmt->executeQuery(['orderId' => $orderId]);

    return $resultSet->fetchAll();
   }
}
