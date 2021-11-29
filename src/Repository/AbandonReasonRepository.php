<?php

namespace App\Repository;

use App\Entity\AbandonReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AbandonReason|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbandonReason|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbandonReason[]    findAll()
 * @method AbandonReason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbandonReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbandonReason::class);
    }

    // /**
    //  * @return AbandonReason[] Returns an array of AbandonReason objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AbandonReason
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
