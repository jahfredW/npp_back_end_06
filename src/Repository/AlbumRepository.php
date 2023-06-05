<?php

namespace App\Repository;

use App\Entity\Album;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Album>
 *
 * @method Album|null find($id, $lockMode = null, $lockVersion = null)
 * @method Album|null findOneBy(array $criteria, array $orderBy = null)
 * @method Album[]    findAll()
 * @method Album[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    public function save(Album $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Album $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAlbumWithPagination($limit, $offset, $albumCategory, $albumType
    , $beginDate, $endDate, $albumName): ?Array
{
    $results = $this->createQueryBuilder('a')
        ->setMaxResults($limit)
        ->setFirstResult($offset);
        

        if( isset($albumCategory) && $albumCategory != null){
            
            $results->andWhere('a.category = :category')
            ->setParameter('category', $albumCategory);
        }

        if(isset($albumType) && $albumType != null){
            $results->andWhere('a.product = :type')
            ->setParameter('type', $albumType);
        }

        if( isset($beginDate) && $beginDate != null){
            $results->andWhere('a.createdAt >= :beginDate')
            ->setParameter('beginDate', $beginDate);
        }

        if( isset($endDate) && $endDate != null){
            $results->andWhere('a.createdAt <= :endDate')
            ->setParameter('endDate', $endDate);
        }

        if( isset($albumName) && $albumName != null ){
            $results->andWhere('a.name like :named')
            ->setParameter('named', '%' . $albumName . '%');
        }
        
        $results = $results
        ->getQuery()
        ->getResult();

    return $results;
}

//    /**
//     * @return Album[] Returns an array of Album objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Album
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
