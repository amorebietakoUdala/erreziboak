<?php

namespace App\Repository\GTWIN;

/**
 * TipoIngresoRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TipoIngresoRepository extends \Doctrine\ORM\EntityRepository
{
//    public function findAll() {
    //	$qb = $this->createQueryBuilder('tp');
    //	return $qb->getQuery()->getResult();
//    }

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
