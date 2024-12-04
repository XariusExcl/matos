<?php

namespace App\Repository;

use App\Entity\Loan;
use App\Entity\LoanStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Loan>
 *
 * @method Loan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Loan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Loan[]    findAll()
 * @method Loan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    /**
     * @return Loan[] Returns an array of Loan objects
     */
    public function findInNextTwoWeeks(\DateTime $value): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.departure_date BETWEEN :start AND :end')
            ->setParameter('start', $value->format('Y-m-d'))
            ->setParameter(':end', $value->modify("+14 day")->format('Y-m-d'))
            ->orWhere('l.return_date BETWEEN :start AND :end')
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Loan[] Returns an array of Loan objects
     */
    public function findUnavailableBetweenDates(\DateTime $start, \DateTime $end): array
    {
        return $this->createQueryBuilder('l')
        ->where('l.departure_date > :start AND l.departure_date < :end')
        ->setParameter('start', $start->format('Y-m-d H:i'))
        ->setParameter('end', $end->format('Y-m-d H:i'))
        ->where('l.return_date > :start AND l.return_date < :end')
        ->orWhere('l.departure_date <= :start AND l.return_date >= :end')
        ->andWhere('l.status < 2') // PENDING or ACCEPTED
        ->orderBy('l.id', 'ASC')
        ->getQuery()
        ->getResult()
    ;
    }

    public function findPending(): array
    {
        $date = new \DateTime();

        return $this->createQueryBuilder('l')
            ->where('l.departure_date > :date')
            ->andWhere('l.status = :status')
            ->setParameter('date', $date->format('Y-m-d H:i'))
            ->setParameter('status', LoanStatus::PENDING)
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findPendingReturn(): array
    {
        /*
        $date = new \DateTime();
        $date->modify("-1 day");
        */

        return $this->createQueryBuilder('l')
            // ->where('l.return_date < :date')
            ->andWhere('l.status = :status')
            // ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('status', LoanStatus::ACCEPTED)
            ->orderBy('l.return_date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
