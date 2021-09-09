<?php

namespace App\Repository;

use App\Entity\CareRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CareRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method CareRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method CareRequest[]    findAll()
 * @method CareRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CareRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CareRequest::class);
    }

    // /**
    //  * @return CareRequest[] Returns an array of CareRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CareRequest
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
