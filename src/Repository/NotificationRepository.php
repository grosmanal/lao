<?php

namespace App\Repository;

use App\Entity\Doctor;
use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function readUnreadForDoctorQuery(Doctor $doctor, bool $read)
    {
        $qb = $this->createQueryBuilder('n');

        return $qb
            ->andWhere('n.doctor = :doctor')
            ->setParameter(':doctor', $doctor)
            ->andWhere($read ? $qb->expr()->isNotNull('n.readAt') : $qb->expr()->isNull('n.readAt'))
            ->orderBy('n.createdAt', 'ASC')
            ->getQuery()
        ;
    }

    public function readForDoctorQuery(Doctor $doctor)
    {
        return $this->readUnreadForDoctorQuery($doctor, true);
    }

    public function findUnreadForDoctor(Doctor $doctor)
    {
        return $this->readUnreadForDoctorQuery($doctor, false)
            ->getResult()
        ;
    }

    public function markAllForDoctor(Doctor $doctor): void
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->update('\App\Entity\Notification', 'n')
            ->set('n.readAt', ':now')
            ->setParameter(':now', new \DateTimeImmutable())
            ->andWhere('n.doctor = :doctor')
            ->setParameter(':doctor', $doctor)
            ->andWhere($qb->expr()->isNull('n.readAt'))
        ;
        $qb->getQuery()->execute();
    }

    // /**
    //  * @return Notification[] Returns an array of Notification objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Notification
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
