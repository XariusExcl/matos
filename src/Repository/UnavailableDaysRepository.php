<?php

namespace App\Repository;

use App\Entity\UnavailableDays;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UnavailableDays>
 *
 * @method UnavailableDays|null find($id, $lockMode = null, $lockVersion = null)
 * @method UnavailableDays|null findOneBy(array $criteria, array $orderBy = null)
 * @method UnavailableDays[]    findAll()
 * @method UnavailableDays[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnavailableDaysRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UnavailableDays::class);
    }

    /**
     * @return UnavailableDays[] Returns an array of UnavailableDays objects
     */
    public function findInNextTwoWeeks(\DateTime $value, int $categoryId): array
    {
        $ud = $this->createQueryBuilder('u')
            ->where('u.dateStart BETWEEN :start AND :end')
            ->setParameter('start', $value->format('Y-m-d'))
            ->setParameter(':end', $value->modify("+14 day")->format('Y-m-d'))
            ->orWhere('u.dateEnd BETWEEN :start AND :end')
            ->orderBy('u.dateStart', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return array_filter($ud, function($uday) use ($categoryId) {
            return ($uday->getRestrictedCategory() === null || $uday->getRestrictedCategory()?->getId() === $categoryId);
        });
    }

    //    /**
    //     * @return UnavailableDays[] Returns an array of UnavailableDays objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?UnavailableDays
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
