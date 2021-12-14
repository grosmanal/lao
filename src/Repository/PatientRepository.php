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
class PatientRepository extends ServiceEntityRepository implements ActivityLoggableRepositoryInterface
{
    use ActivityLoggableTrait;
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patient::class);
    }

    
    public function findActiveSince(Office $office, \DateTimeInterface $since): array
    {
        $qb = $this->createQueryBuilder('p');
        return $this->addWhereSince($qb, 'p', $since)
            ->andWhere('p.office = :office')
            ->setParameter(':office', $office)
            ->getQuery()
            ->getResult()
        ;
    }
    
    
    /**
     * @return Patient[] Patients without any care request
     */
    public function findWithoutCareRequest(Office $office): array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.careRequests', 'cr')
            ->andWhere('cr.id is null')
            ->andWhere('p.office = :office')
            ->setParameter(':office', $office)
            ->orderBy('p.creationDate', 'DESC')
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
