<?php

namespace App\Repository\GTWIN;

use App\Entity\GTWIN\Tarifa;
use Doctrine\ORM\EntityRepository;

/**
 * TipoIngresoRepository.
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TarifaRepository extends EntityRepository
{
    public function findByTipoIngreso($conceptoC60)
    {
        $sql = 'SELECT t.tarvalact, t.tarnomtar, max(t.taranypto) FROM SP_TRB_TARIFA t
                INNER JOIN SP_TRB_TIPING ti ON (ti.TINDBOIDE = t.TARTIPING)
                WHERE ti.TINCODO60=\''.$conceptoC60.'\'
                group by t.tarvalact, t.tarnomtar
                HAVING MAX(t.taranypto)=MAX(t.taranypto)';
        $tarifas = $this->getEntityManager()->getConnection()->executeQuery($sql)->fetchAllAssociative();
        $newTarifas = [];
        foreach ($tarifas as $tarifa) {
            $newTarifa = new Tarifa();
            $newTarifa->setNombre($tarifa['TARNOMTAR']);
            $newTarifa->setValorActual($tarifa['TARVALACT']);
            $newTarifas[] = $newTarifa;
        }
        return $newTarifas;
    }
}
