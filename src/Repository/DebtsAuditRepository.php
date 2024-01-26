<?php

namespace App\Repository;

use App\Entity\DebtsAudit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DebtsAudit|null find($id, $lockMode = null, $lockVersion = null)
 * @method DebtsAudit|null findOneBy(array $criteria, array $orderBy = null)
 * @method DebtsAudit[]    findAll()
 * @method DebtsAudit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DebtsAuditRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DebtsAudit::class);
    }

    // /**
    //  * @return DebtsAudit[] Returns an array of DebtsAudit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DebtsAudit
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
