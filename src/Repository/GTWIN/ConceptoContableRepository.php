<?php

namespace App\Repository\GTWIN;

use App\Entity\GTWIN\ConceptoContable;
use Doctrine\ORM\EntityRepository;

/**
 * @method ConceptoContable|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConceptoContable|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConceptoContable[]    findAll()
 * @method ConceptoContable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConceptoContableRepository extends EntityRepository
{
    // /**
    //  * @return ConceptoContable[] Returns an array of ConceptoContable objects
    //  */
    public function findByTipoIngreso($tipoIngreso)
    {
        $qb = $this->createQueryBuilder('cc')
                ->join('GTWIN:TipoIngreso', 'ti', 'with', 'cc.tipoIngreso = ti.id')
                ->andWhere('ti.codigo = :codigoTipoIngreso')
                ->setParameter('codigoTipoIngreso', $tipoIngreso)
                ->orderBy('ti.codigo', 'ASC');

        dump($qb->getQuery(), $qb->getQuery()->getResult());
        die;

        return $qb->getQuery()->getResult();
    }

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
