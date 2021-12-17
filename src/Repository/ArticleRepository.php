<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Doctor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }
    
    /**
     * @return Article[]
     */
    public function findPublishableNotReadByDoctor(Doctor $doctor)
    {
        $subQueryBuilder = $this->getEntityManager()->createQueryBuilder();
        $subQuery = $subQueryBuilder
            ->select('ad.id')
            ->from('App\Entity\Article', 'ad')
            ->innerJoin('ad.readByDoctors', 'd')
            ->where('d = :doctor')
        ;

        $qb = $this->createQueryBuilder('a');
        return $qb
            ->andWhere($qb->expr()->notIn('a.id', $subQuery->getDQL()))
            ->setParameter(':doctor', $doctor)
            ->andWhere($qb->expr()->lte("COALESCE(a.publishFrom, '0001-01-01')", ':readingDate'))
            ->andWhere($qb->expr()->gte("COALESCE(a.publishTo, '9999-12-31')", ':readingDate'))
            ->setParameter(':readingDate', new \DateTimeImmutable())
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Article[] Returns an array of Article objects
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
    public function findOneBySomeField($value): ?Article
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
