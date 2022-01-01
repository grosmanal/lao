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
     * @param Office $office Current office
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
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }


    /**
     * @param Office $office Current office
     * @return Patient[] Patients without availability and with active care request
     */
    public function findWithoutAvailability(Office $office): array
    {
        $subQuery = $this->getEntityManager()->createQueryBuilder();
        $subQuery
            ->select('cr_p.id')
            ->from('App\Entity\CareRequest', 'cr')
            ->innerJoin('cr.patient', 'cr_p')
            ->andWhere($subQuery->expr()->isNull('cr.acceptedAt'))
            ->andWhere($subQuery->expr()->isNull('cr.abandonedAt'))
        ;

        $qb = $this->createQueryBuilder('p');
        return $qb
            ->select('p')
            ->andWhere('p.availability = :availability')
            ->setParameter(':availability', json_encode([]))
            ->andWhere('COALESCE(p.variableSchedule, false) = :variableSchedule')
            ->setParameter(':variableSchedule', false)
            ->andWhere('p.office = :office')
            ->setParameter(':office', $office)
            ->andWhere($qb->expr()->in('p.id', $subQuery->getDQL()))
            ->orderBy('p.createdAt', 'DESC')
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
