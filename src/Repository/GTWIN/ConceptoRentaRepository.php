<?php

namespace App\Repository\GTWIN;

use App\Entity\GTWIN\ConceptoRenta;
use Doctrine\ORM\EntityRepository;

/**
 * @method ConceptoRenta|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConceptoRenta|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConceptoRenta[]    findAll()
 * @method ConceptoRenta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConceptoRentaRepository extends EntityRepository
{
    /*
    public function findOneBySomeField($value): ?ConceptoRenta
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