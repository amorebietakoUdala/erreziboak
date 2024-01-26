<?php

namespace App\Repository\GTWIN;

use App\Entity\GTWIN\OperacionesRecibo;
use Doctrine\ORM\EntityRepository;

/**
 * @method OperacionesRecibo|null find($id, $lockMode = null, $lockVersion = null)
 * @method OperacionesRecibo|null findOneBy(array $criteria, array $orderBy = null)
 * @method OperacionesRecibo[]    findAll()
 * @method OperacionesRecibo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperacionesReciboRepository extends EntityRepository
{

    // /**
    //  * @return OperacionesRecibo[] Returns an array of OperacionesRecibo objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OperacionesRecibo
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
