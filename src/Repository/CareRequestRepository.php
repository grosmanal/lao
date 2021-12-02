<?php

namespace App\Repository;

use App\Entity\CareRequest;
use App\Input\SearchCriteria;
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
    
    public function findBySearchCriteria(SearchCriteria $searchCriteria)
    {
        $qb = $this->createQueryBuilder('cr');

        if ($searchCriteria->getLabel()) {
            // Jointure avec le patient pour recherche sur son nom / prénom / contact
            $qb
                ->join('cr.patient', 'p')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('p.firstname', ':likeLabel'),
                        $qb->expr()->like('p.lastname', ':likeLabel'),
                        $qb->expr()->like('p.contact', ':likeLabel')
                    )
                )
                ->setParameter(':likeLabel', '%' . trim(addcslashes(strtolower($searchCriteria->getLabel()), '%_')) . '%')
            ;
        }
        
        if ($searchCriteria->getCreator()) {
            $qb
                ->andWhere('cr.doctorCreator = :doctorCreator')
                ->setParameter(':doctorCreator', $searchCriteria->getCreator())
            ;
        }
        
        if ($searchCriteria->getCreationFrom()) {
            $qb
                ->andWhere('cr.creationDate >= :creationFrom')
                ->setParameter(':creationFrom', $searchCriteria->getCreationFrom())
            ;
        }
        
        if ($searchCriteria->getCreationTo()) {
            $qb
                ->andWhere('cr.creationDate <= :creationTo')
                ->setParameter(':creationTo', $searchCriteria->getCreationTo())
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
        
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
