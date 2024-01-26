<?php

namespace App\Repository;

use App\Entity\AccountTitularityCheck;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccountTitularityCheck|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountTitularityCheck|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountTitularityCheck[]    findAll()
 * @method AccountTitularityCheck[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountTitularityCheckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccountTitularityCheck::class);
    }

    // /**
    //  * @return AccountTitularityCheck[] Returns an array of AccountTitularityCheck objects
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
    public function findOneBySomeField($value): ?AccountTitularityCheck
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
