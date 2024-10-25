<?php

namespace App\Repository;

use App\Entity\TagRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TagRule>
 *
 * @method TagRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method TagRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method TagRule[]    findAll()
 * @method TagRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagRule::class);
    }

    //    /**
    //     * @return TagRule[] Returns an array of TagRule objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TagRule
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
