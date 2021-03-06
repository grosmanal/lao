<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\Doctor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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
     * @return QueryBuilder
     */
    private function queryBuilderReadByDoctor(string $articleAlias, string $doctorParameter)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        return $qb
            ->from('App\Entity\Article', $articleAlias)
            ->innerJoin(sprintf('%s.readByDoctors', $articleAlias), 'd')
            ->where(sprintf('d = %s', $doctorParameter))
        ;
    }

    /**
     * @return Article[]
     */
    public function findReadByDoctor(Doctor $doctor)
    {
        $qb = $this->queryBuilderReadByDoctor('ad', ':doctor');
        return $qb
            ->select('ad')
            ->setParameter(':doctor', $doctor)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Article[]
     */
    public function findPublishableNotReadByDoctor(Doctor $doctor)
    {
        $subQuery = $this->queryBuilderReadByDoctor('ad', ':doctor')
            ->select('ad.id')
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
