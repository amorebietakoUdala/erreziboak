<?php

namespace App\Repository\GTWIN;

/**
 * @method ConceptoContable|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConceptoContable|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConceptoContable[]    findAll()
 * @method ConceptoContable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstitucionRepository extends \Doctrine\ORM\EntityRepository
{
    // /**
    //  * @return ConceptoContable[] Returns an array of ConceptoContable objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ConceptoContable
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
