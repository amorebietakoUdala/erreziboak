<?php

namespace App\Repository\GTWIN;

use App\Entity\GTWIN\ReferenciaC60;

/**
 * ReferenciaC60Repository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ReferenciaC60Repository extends \Doctrine\ORM\EntityRepository
{
    //    public function findAll() {
    //	$qb = $this->createQueryBuilder('tp');
    //	return $qb->getQuery()->getResult();
    //    }

    public function findByReferenciaC60($referenciaC60): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.referenciaC60 = :referenciaC60')
            ->setParameter('referenciaC60', $referenciaC60);
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function __remove_blank_filters($criteria)
    {
        $new_criteria = [];
        foreach ($criteria as $key => $value) {
            if (!empty($value)) {
                $new_criteria[$key] = $value;
            }
        }

        return $new_criteria;
    }
}