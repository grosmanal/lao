<?php

namespace App\Repository;

use App\Entity\Office;
use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Patient|null find($id, $lockMode = null, $lockVersion = null)
 * @method Patient|null findOneBy(array $criteria, array $orderBy = null)
 * @method Patient[]    findAll()
 * @method Patient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PatientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patient::class);
    }

    public function findByLikeLabelAndOffice(string $label, Office $office)
    {
        // TODO faire également la recherche sur le contact
        $qb = $this->createQueryBuilder('p');
        $likeLabel = $qb->expr()->literal('%' . trim(addcslashes(strtolower($label), '%_')) . '%');
        return $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('p.firstname', $likeLabel),
                    $qb->expr()->like('p.lastname', $likeLabel),
                    $qb->expr()->like('p.contact', $likeLabel)
                )
            )
            ->andWhere('p.office = :office')
            ->setParameter('office', $office)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Patient[] Returns an array of Patient objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Patient
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
