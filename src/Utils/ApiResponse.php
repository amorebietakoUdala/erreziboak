<?php

namespace App\Utils;

use App\Entity\GTWIN\Recibo;
use JMS\Serializer\Annotation as Serializer;

/**
 * This class holds the ApiResponse when.
 *
 * @Serializer\ExclusionPolicy("all")
 *
 * @author ibilbao
 */
class ApiResponse
{
    /**
     * @Serializer\Expose*
     */
    private $status = null;
    /**
     * @Serializer\Expose*
     */
    private $message = null;
    /**
     * @Serializer\Expose*
     */
    private $data = null;

    public function __construct($status, $message, $data)
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        //        $this->data = $this->convertEncodingToUTF8($data);
    }

    // private function convertEncodingToUTF8($data)
    // {
    //     if (is_array($data)) {
    //         foreach ($data as $element) {
    //             if ($element instanceof Recibo) {
    //                 /* @var $element Recibo */
    //                 $element->setNumeroReferenciaExterna(mb_convert_encoding($element->getNumeroReferenciaExterna(), 'UTF-8'));
    //                 $element->setNombreCompleto(mb_convert_encoding($element->getNombreCompleto(), 'UTF-8'));
    //                 $tipo_ingreso = new \App\Entity\GTWIN\TipoIngreso();
    //                 $tipo_ingreso->setCodigo($element->getTipoIngreso()->getCodigo());
    //                 $tipo_ingreso->setConceptoC60($element->getTipoIngreso()->getConceptoC60());
    //                 $tipo_ingreso->setDescripcion($element->getTipoIngreso()->getDescripcion());
    //                 $element->setTipoIngreso($tipo_ingreso);
    //             }
    //         }
    //     }

    //     return $data;
    // }
}
