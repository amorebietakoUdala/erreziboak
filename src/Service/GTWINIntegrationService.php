<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Service;

use App\Entity\ConceptInscription;
use App\Entity\ExamInscription;
use App\Entity\GTWIN\ConceptoContable;
use App\Entity\GTWIN\Institucion;
use App\Entity\GTWIN\Recibo;
use App\Entity\GTWIN\TipoIngreso;
use App\Entity\GTWIN\OperacionesExternas;
use App\Entity\Receipt;
use DateTime;
use Psr\Log\LoggerInterface;
use App\Utils\Validaciones;
use App\Entity\GTWIN\Person;
use App\Entity\GTWIN\ReferenciaC60;
use App\Entity\GTWIN\Tarifa;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Description of MiPagoConstants.
 *
 * @author ibilbao
 */
class GTWINIntegrationService
{
    const INSTITUCIONES = [
        '480034' => 'AMOREBIE',
        '481166' => 'AMETX',
    ];

    private $em = null;
    private $logger = null;
    private $client = null;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, HttpClientInterface $client)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->client = $client;
        // $this->client = $client->withOptions([
        //     'verify_peer' => false,
        //     'verify_host' => false
        // ]);
    }

    public function findByExample(Recibo $criteria)
    {
        $newCriteria = $this->__remove_blank_filters($criteria->__toArray());
        unset($newCriteria['email']);
        if (null !== $criteria->getDni()) {
            $numero = substr($criteria->getDni(), 0, -1);
            $letra = substr($criteria->getDni(), -1);
            $numero = $this->__fixDniNumber($numero);
            $newCriteria['dni'] = $numero;
            $newCriteria['letra'] = $letra;
        }
        $recibosGTWIN = $this->em->getRepository(Recibo::class)->findBy($newCriteria);

        return $recibosGTWIN;
    }

    public function findByNumReciboDni($numRecibo, $dni): ?Recibo
    {
        $em = $this->em;
        $numero = substr($dni, 0, -1);
        $letra = strtoupper(substr($dni, -1));
        $numero = $this->__fixDniNumber($numero);
        $recibo = $em->getRepository(Recibo::class)->findByNumReciboDni($numRecibo, $numero, $letra);

        return $recibo;
    }

    public function findByNumRecibo($numRecibo)
    {
        $em = $this->em;
        $recibo = $em->getRepository(Recibo::class)->findOneBy([
            'numeroRecibo' => $numRecibo,
        ]);

        return $recibo;
    }

    public function findReferenciaC60($referenciaC60): array
    {
        $em = $this->em;
        /* Can be more than one */
        $referenciasC60 = $em->getRepository(ReferenciaC60::class)->findByReferenciaC60($referenciaC60);
        return $referenciasC60;
    }

    public function findRecibosByNumeroReferenciaC60($referencia): array
    {
        $recibos = [];
        $em = $this->em;
        /* Can be more than one */
        $referenciasC60 = $em->getRepository(ReferenciaC60::class)->findByReferenciaC60($referencia);
        foreach ($referenciasC60 as $referencia) {
            $recibos[] = $referencia->getRecibo();
        }
        return $recibos;
    }

    public function findRecibosByNumeroReferenciaC60AndDni($referenciaC60, $dni): array
    {
        $recibos = [];
        $em = $this->em;
        $numero = null;
        $letra = null;
        if (null !== $dni) {
            $numero = substr($dni, 0, -1);
            $letra = substr($dni, -1);
            $numero = $this->__fixDniNumber($numero);
        }
        /* Can be more than one */
        $recibos = $em->getRepository(Recibo::class)->findByReferenciaC60AndDni($referenciaC60, $numero, $letra);
        //        dd($recibos);
        return $recibos;
    }

    /**
     * Find a person by dni. DNI must come with it's control digit at the end.
     *
     * @param string $dni
     *
     * @return \App\Entity\GTWIN\Person
     */
    public function findByDni($dni)
    {
        $dc = substr($dni, -1);
        $numDocumento = substr($dni, 0, -1);
        $em = $this->em;
        $person = $em->getRepository(Person::class)->findOneBy([
            'numDocumento' => $this->__fixDniNumber($numDocumento),
            'digitoControl' => $dc,
        ]);
        return $person;
    }

    /**
     * Find a person by dni. DNI must come with it's control digit at the end.
     *
     * @param string $dni
     *
     * @return Person
     */
    public function personExists($dni)
    {
        $person = $this->findByDni($dni);

        return null !== $person;
    }

    /**
     * Find a person by dni. DNI must come with it's control digit at the end.
     *
     * @param string $dni
     *
     * @return \App\Entity\GTWIN\Person
     */
    public function findByRecibosPendientesByDni($dni)
    {
        $dc = substr($dni, -1);
        $numDocumento = substr($dni, 0, -1);
        $em = $this->em;
        $person = $em->getRepository(Recibo::class)->findByRecibosPendientesByDni(
            $this->__fixDniNumber($numDocumento),
            $dc
        );

        return $person;
    }

    /**
     * Calculate the principal debt amount of a person by DNI.
     *
     * @param string $dni
     *
     * @return null|string
     */
    public function findDeudaTotal($dni): ?string
    {
        $fixedDocument = $dni;
        if (Validaciones::valida_nif_cif_nie($dni) > 0) {
            $numDocumento = substr($dni, 0, -1);
            $fixedDocument = $this->__fixDniNumber($numDocumento);
        }
        $em = $this->em;
        $importe = $em->getRepository(Recibo::class)->findDeudaByDni($fixedDocument);
        if ($importe === null) {
            return "No";
        }
        return $importe;
    }

    /**
     * Return a list of receipt types.
     *
     * @return TipoIngreso
     */
    public function getReceiptTypes()
    {
        $em = $this->em;
        $results = $em->getRepository(TipoIngreso::class)->findBy([]);
        foreach ($results as $result) {
            $result->setDescripcion($result->getDescripcion());
        }

        return $results;
    }

    private function __fixDniNumber($numero)
    {
        if (is_numeric($numero)) {
            $numero = str_pad($numero, 9, '0', STR_PAD_LEFT);
        }

        return $numero;
    }

    public function paidWithCreditCard($numRecibo, $fraccion, $importe, $timestamp, $registeredPaymentId, $index)
    {
        $insert_template = 'INSERT INTO EXTCALL (DBOID, ACTIONCODE, INPUTPARS, OUTPUTPARS, OUTPARSMEMO, CALLTYPE, NUMRETRIES, QUEUE, PRIORITY, CALLSTATUS, CALLTIME, PROCTIME, CONFTIME, ORIGINOBJ, DESTOBJ, USERBW, MSGERROR, URLOK, URLOKPARAM, CONFSTATUS) VALUES ' .
            "('{DBOID}','OPERACION_PAGO_TAR','<NUMREC>{NUMREC}</NUMREC><NUMFRA>{NUMFRA}</NUMFRA><FECOPE>{FECOPE}</FECOPE><IMPORT>{IMPORT}</IMPORT><RECARG>0</RECARG><INTERE>0</INTERE><COSTAS>0</COSTAS><CAJCOB>INT</CAJCOB><NUMAUT>{NUMAUT}</NUMAUT><USERBW>{USERBW}</USERBW>',null, null,0,0,0,0,0,TO_DATE('{CALLTIME}','DD/MM/YYYY HH24:MI:SS'),TO_DATE('{PROCTIME}','DD/MM/YYYY HH24:MI:SS'),null,null,null,'{USERBW}',null,null,null,0)";
        $time_start = substr('' . floatval(microtime(true)) * 10000, 0, 12);
        $dboid = str_pad('1235' . $time_start . $index, 21, '0', STR_PAD_RIGHT);
        $now = new DateTime();

        $params = [
            '{DBOID}' => $dboid,
            '{NUMREC}' => $numRecibo,
            '{NUMFRA}' => $fraccion,
            '{FECOPE}' => $timestamp->format('d/m/Y H:i:s'),
            '{IMPORT}' => $importe,
            '{NUMAUT}' => $registeredPaymentId,
            '{CALLTIME}' => $now->format('d/m/Y H:i:s'),
            '{PROCTIME}' => $now->format('d/m/Y H:i:s'),
            '{USERBW}' => 'INT',
        ];
        $sql = str_replace(array_keys($params), $params, $insert_template);
        $statement = $this->em->getConnection()->prepare($sql);
        return $statement->execute();
    }

    /**
     * Crear Recibo en GTWIN con los datos de la inscripción a exam
     *
     * @abstract
     *
     * @param ConceptInscription $inscription Recibo de ordaindu
     *
     * @return Recibo|null
     *
     * @throws Exception Si la operaciónExterna devuelve un error
     */
    public function createReciboForInscription(ConceptInscription $inscription, bool $wait = true): ?Recibo
    {
        $concept = $inscription->getConcept();
        /* If Concept has a service URL to retrive the price we must get it */
        if (null === $inscription->getPrice()) {
            if (null !== $concept->getServiceURL()) {
                try {
                    $response = $this->client->request('GET', $concept->getServiceURL());
                    $actualPrice = json_decode($response->getContent(), true);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                }
            } else {
                $actualPrice = $concept->getUnitaryPrice();
            }
        } else {
            $actualPrice = $inscription->getPrice();
        }

        $tipoIngreso = $this->em->getRepository(TipoIngreso::class)->findOneBy([
            'conceptoC60' => $inscription->getConcept()->getSuffix(),
        ]);
        $now = new DateTime();
        $reference = $inscription->getExternalReference() ?? $now->format('YmdHis');
        
        $inputparams = $this->createReciboParams(
            null,
            $reference,
            $tipoIngreso->getCodigo(),
            $concept->getEntity(),
            mb_convert_encoding($inscription->getApellido1() . '*' . $inscription->getApellido2() . ',' . $inscription->getNombre(), 'ISO-8859-1'),
            substr($inscription->getDni(), 0, -1),
            substr($inscription->getDni(), -1),
            (Validaciones::validar_dni($inscription->getDni()) ? 'ES' : 'EX'),
            str_pad($actualPrice, '15', ' ', STR_PAD_RIGHT) . str_pad(mb_convert_encoding($concept->getName(), 'ISO-8859-1'), '80', ' ', STR_PAD_RIGHT),
            $tipoIngreso->getTipoDefecto(),
            'P',
            'V',
            'F',
            $concept->getAccountingConcept(),
            $actualPrice
        );
        $dboid = $this->__insertExternalOperation('CREA_RECIBO', $inputparams);
        $result = null;
        if ($wait) {
            $operacionExterna = $this->__waitUntilProcessed($dboid);
            if ($operacionExterna->procesadaOk()) {
                $em = $this->em;
                $result = $em->getRepository(Recibo::class)->findOneBy(['numeroReferenciaExterna' => $reference]);
            } else {
                throw new \Exception($operacionExterna->getMensajeError()->getDescripcion());
            }
        }

        return $result;
    }

    /**
     * Crear Recibo en GTWIN con los datos de la inscripción a exam
     *
     * @abstract
     *
     * @param ExamInscription $receipt Recibo de ordaindu
     *
     * @return Recibo|null
     *
     * @throws Exception Si la operaciónExterna devuelve un error
     */
    public function createReciboOpt(ExamInscription $exam, bool $wait = true): ?Recibo
    {
        $concept = $exam->getCategory()->getConcept();
        /* If Concept has a service URL to retrive the price we must get it */
        if (null !== $concept->getServiceURL()) {
            try {
                $response = $this->client->request('GET', $concept->getServiceURL());
                $actualPrice = json_decode($response->getContent(), true);
            } catch (Exception $e) {
                throw new \Exception($e->getMessage());
            }
        } else {
            $actualPrice = $concept->getUnitaryPrice();
        }

        $tipoIngreso = $this->em->getRepository(TipoIngreso::class)->findOneBy([
            'conceptoC60' => $exam->getCategory()->getConcept()->getSuffix(),
        ]);
        $now = new DateTime();
        $reference = $now->format('YmdHis');
        $inputparams = $this->createReciboParams(
            null,
            $reference,
            $tipoIngreso->getCodigo(),
            $concept->getEntity(),
            mb_convert_encoding($exam->getApellido1() . '*' . $exam->getApellido2() . ',' . $exam->getNombre(), 'ISO-8859-1'),
            substr($exam->getDni(), 0, -1),
            substr($exam->getDni(), -1),
            (Validaciones::validar_dni($exam->getDni()) ? 'ES' : 'EX'),
            str_pad($actualPrice, '15', ' ', STR_PAD_RIGHT) . str_pad(mb_convert_encoding($concept->getName(), 'ISO-8859-1'), '80', ' ', STR_PAD_RIGHT),
            $tipoIngreso->getTipoDefecto(),
            'P',
            'V',
            'F',
            $concept->getAccountingConcept(),
            $actualPrice
        );
        $dboid = $this->__insertExternalOperation('CREA_RECIBO', $inputparams);
        $result = null;
        if ($wait) {
            $operacionExterna = $this->__waitUntilProcessed($dboid);
            if ($operacionExterna->procesadaOk()) {
                $em = $this->em;
                $result = $em->getRepository(Recibo::class)->findOneBy(['numeroReferenciaExterna' => $reference]);
            } else {
                throw new \Exception($operacionExterna->getMensajeError()->getDescripcion());
            }
        }

        return $result;
    }

    public function createReciboParams($numRecibo, $reference, $codtin, $codins, $nomcom, $dninif, $carcon, $sigpai, $cuerpo, $tipexa, $estado, $situacion, $indpar = 'F', $codcon = '107', $importe)
    {
        $createReciboParamsTemplate = '
			<CODTIN>{CODTIN}</CODTIN>
			<CODINS>{CODINS}</CODINS>
			<REFERE>{REFERE}</REFERE>
			<FECCRE>{FECCRE}</FECCRE>
			<FECINI>{FECINI}</FECINI>
			<FECFIN>{FECFIN}</FECFIN>
			<NOMCOM>{NOMCOM}</NOMCOM>
			<DNINIF>{DNINIF}</DNINIF>
			<CARCON>{CARCON}</CARCON>
			<SIGPAI>{SIGPAI}</SIGPAI>
			<CUERPO>{CUERPO}</CUERPO>
			<TIPEXA>{TIPEXA}</TIPEXA>
			<ESTADO>{ESTADO}</ESTADO>
			<SITUAC>{SITUAC}</SITUAC>
			<INDPAR>{INDPAR}</INDPAR>
			<linea>
				<CODCON>{CODCON}</CODCON>
				<IMPORT>{IMPORT}</IMPORT>
			</linea>';
        $now = new DateTime();
        $params = [
            '{NUMREC}' => $numRecibo,
            '{CODTIN}' => $codtin,
            '{CODINS}' => $codins,
            '{NUMFRA}' => '0',
            '{REFERE}' => $reference,
            '{FECCRE}' => $now->format('d/m/Y H:i:s'),
            '{FECINI}' => $now->format('d/m/Y H:i:s'),
            '{FECFIN}' => $now->format('d/m/Y H:i:s'),
            '{NOMCOM}' => $nomcom,
            '{DNINIF}' => $dninif,
            '{CARCON}' => $carcon,
            '{SIGPAI}' => $sigpai,
            '{CUERPO}' => $cuerpo,
            '{TIPEXA}' => $tipexa,
            '{ESTADO}' => $estado,
            '{SITUAC}' => $situacion,
            '{INDPAR}' => $indpar,
            '{CODCON}' => $codcon,
            '{IMPORT}' => $importe,
        ];
        $params_string = str_replace(array_keys($params), $params, $createReciboParamsTemplate);

        return $params_string;
    }

    private function __insertExternalOperation($operation, $inputparams)
    {
        $time_start = substr('' . floatval(microtime(true)) * 10000, 0, 12);
        $dboid = str_pad('1235' . $time_start, 21, '0', STR_PAD_RIGHT);
        $now = new DateTime();
        $insert_template = "INSERT INTO EXTCALL (DBOID, ACTIONCODE, INPUTPARS, OUTPUTPARS, OUTPARSMEMO, CALLTYPE, NUMRETRIES, QUEUE, PRIORITY, CALLSTATUS, CALLTIME, PROCTIME, CONFTIME, ORIGINOBJ, DESTOBJ, USERBW, MSGERROR, URLOK, URLOKPARAM, CONFSTATUS) VALUES ('{DBOID}','{OPERATION}','{INPUTPARAMS}',null, null,0,0,0,0,0,TO_DATE('{CALLTIME}','DD/MM/YYYY HH24:MI:SS'),TO_DATE('{PROCTIME}','DD/MM/YYYY HH24:MI:SS'),null,null,null,'{USERBW}',null,null,null,0)";
        $params = [
            '{DBOID}' => $dboid,
            '{OPERATION}' => $operation,
            '{INPUTPARAMS}' => $inputparams,
            '{CALLTIME}' => $now->format('d/m/Y H:i:s'),
            '{PROCTIME}' => $now->format('d/m/Y H:i:s'),
            '{USERBW}' => 'INT',
        ];
        $sql = str_replace(array_keys($params), $params, $insert_template);
        $statement = $this->em->getConnection()->prepare($sql);
        $statement->execute();

        return $dboid;
    }

    private function __waitUntilProcessed($dboid)
    {
        $em = $this->em;
        $operationExterna = $em->getRepository(OperacionesExternas::class)->find($dboid);
        $status = $operationExterna->getEstado();
        $retries = 0;
        while (0 === $status && $retries < 5) {
            sleep(7);
            // Borrar la caché para que vuelva a forzar la lectura de base de datos
            $em->clear();
            $operationExterna = $em->getRepository(OperacionesExternas::class)->find($dboid);
            $status = $operationExterna->getEstado();
            ++$retries;
        }

        return $operationExterna;
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

    public function findConceptoContables()
    {
        $conceptosContables = $this->em->getRepository(\App\Entity\GTWIN\ConceptoContable::class)->findAll();

        return $conceptosContables;
    }

    public function findInstitucionByCodigo($codigo)
    {
        $result = $this->em->getRepository(Institucion::class)->findBy([
            'codigo' => $codigo,
        ]);

        return $result;
    }

    public function findInstituciones() {
        $result = $this->em->getRepository(Institucion::class)->findAll();

        return $result;
    }

    public function findTipoIngresoInstitucion($institucion)
    {
        $result = $this->em->getRepository(TipoIngreso::class)->findByInstitucion($institucion);

        return $result;
    }

    public function findTipoIngresoByCodigo($codigo)
    {
        $result = $this->em->getRepository(TipoIngreso::class)->findBy([
            'codigo' => $codigo,
        ]);
        return $result;
    }

    public function findTipoIngresoByConceptoC60($suffix)
    {
        $tipoIngreso = $this->em->getRepository(TipoIngreso::class)->findOneBy([
            'conceptoC60' => $suffix,
        ]);
        return $tipoIngreso;
    }


    public function findTarifasTipoIngreso($tipoIngreso)
    {   
        $result = $this->em->getRepository(Tarifa::class)->findByTipoIngreso($tipoIngreso);

        return $result;
    }
}
