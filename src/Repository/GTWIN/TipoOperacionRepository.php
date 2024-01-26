<?php

namespace App\Repository\GTWIN;

use App\Entity\GTWIN\TipoOperacion;
use Doctrine\ORM\EntityRepository;

/**
 * @method TipoOperacion|null find($id, $lockMode = null, $lockVersion = null)
 * @method TipoOperacion|null findOneBy(array $criteria, array $orderBy = null)
 * @method TipoOperacion[]    findAll()
 * @method TipoOperacion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TipoOperacionRepository extends EntityRepository
{
    // /**
    //  * @return TipoOperacion[] Returns an array of TipoOperacion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TipoOperacion
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
