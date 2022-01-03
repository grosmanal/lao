<?php

namespace App\Repository;

use App\Entity\CareRequest;
use App\Entity\Office;
use App\Input\SearchCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CareRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method CareRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method CareRequest[]    findAll()
 * @method CareRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CareRequestRepository extends ServiceEntityRepository implements ActivityLoggableRepositoryInterface
{
    use ActivityLoggableTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CareRequest::class);
    }

    public function findBySearchCriteria(SearchCriteria $searchCriteria, Office $office)
    {
        $qb = $this->createQueryBuilder('cr');

        // Jointure avec le patient pour la sélection de l'office
        $qb
            ->join('cr.patient', 'p')
            ->andWhere('p.office = :office')
            ->setParameter(':office', $office)
        ;

        if ($searchCriteria->getLabel()) {
            // Jointure avec le patient pour recherche sur son nom / prénom / contact
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('p.firstname', ':likeLabel'),
                        $qb->expr()->like('p.lastname', ':likeLabel'),
                        $qb->expr()->like('p.contact', ':likeLabel')
                    )
                )
                ->setParameter(':likeLabel', '%' . trim(strtolower($searchCriteria->getLabel())) . '%')
            ;
        }

        if ($searchCriteria->getContactedBy()) {
            $qb
                ->andWhere('cr.contactedBy = :contactedBy')
                ->setParameter(':contactedBy', $searchCriteria->getContactedBy())
            ;
        }

        if ($searchCriteria->getContactedFrom()) {
            $qb
                ->andWhere('cr.contactedAt >= :contactedFrom')
                ->setParameter(':contactedFrom', $searchCriteria->getContactedFrom())
            ;
        }

        if ($searchCriteria->getContactedTo()) {
            $qb
                ->andWhere('cr.contactedAt <= :contactedTo')
                ->setParameter(':contactedTo', $searchCriteria->getContactedTo())
            ;
        }

        if ($searchCriteria->getComplaint()) {
            $qb
                ->andWhere('cr.complaint = :complaint')
                ->setParameter(':complaint', $searchCriteria->getComplaint())
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    public function findActiveSince(Office $office, \DateTimeInterface $since): array
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->innerJoin('c.patient', 'p')
            ->andWhere('p.office = :office')
            ->setParameter(':office', $office)
        ;

        return $this->addWhereSince($qb, 'c', $since)
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
