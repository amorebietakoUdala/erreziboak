<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Service\GTWINIntegrationService;
use App\Utils\ApiResponse;
use App\Entity\Category;
use App\Entity\ConceptInscription;
use App\Entity\GTWIN\Recibo;
use Exception;
use JMS\Serializer\SerializerInterface;
use App\Entity\Payment;
use App\Form\ConceptInscriptionTypeForm;
use App\Repository\CategoryRepository;
use App\Repository\ConceptRepository;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * Description of RestController.
 *
 * @author ibilbao
 */

/**
 * @Route("/api")
 */
class RestController extends AbstractController
{

    private PaymentRepository $paymentRepo;
    private ConceptRepository $conceptRepo;

    public function __construct(PaymentRepository $paymentRepo, ConceptRepository $conceptRepo)
    {
       $this->paymentRepo = $paymentRepo;
       $this->conceptRepo = $conceptRepo;
    }

    /**
     * Check the existance of the person by DNI.
     *
     * @Route("/person/{dni}", name="api_person",  methods={"GET"})
     */
    public function getCheckPersonAction(Request $request, GTWINIntegrationService $gts, SerializerInterface $serializer)
    {
        $dni = strtoupper($request->get('dni'));
        $exists = $gts->personExists($dni);
        if ($exists) {
            $data = new ApiResponse('OK', 'Found', null);
        } else {
            $data = new ApiResponse('KO', 'Not Found', null);
        }
        $response = new JsonResponse($serializer->serialize($data, 'json'), 200, [
            'Content-Type' => 'application/json;charset=utf-8'
        ], true);

        return $response;
    }

    /**
     * Check the existance of the person by DNI.
     *
     * @Route("/receipts/new", name="api_receipt_new",  methods={"POST"})
     */
    public function receiptNew (Request $request, GTWINIntegrationService $gts, SerializerInterface $serializer, RouterInterface $router) {
        $inscription = new ConceptInscription();
        $form = $this->createForm(ConceptInscriptionTypeForm::class, $inscription, [
            'locale' => $request->getLocale(),
            'csrf_protection' => false,
        ]);
        $json = json_decode($request->getContent(), true);
        $form->submit($json);
        /** @var ConceptInscription $data */ 
        $data = $form->getData();
        $recibo = $gts->createReciboForInscription($data, true);
        $paymentURL = $router->generate('receipt_pay',[
            'numeroRecibo' => $recibo->getNumeroRecibo(),
            'dni' => $data->getDni(), 
        ], RouterInterface::ABSOLUTE_URL);

        $response = new JsonResponse($serializer->serialize(new ApiResponse('OK', 'Received', [
            'recibo' => $recibo,
            'paymentURL' => $paymentURL,
        ]), 'json'), 200, [
            'Content-Type' => 'application/json;charset=utf-8'
        ], true);
        return $response;
    }

    /**
     * Returns a list of unpayd receipts for the DNI.
     *
     * @Route("/receipts/{dni}", name="api_receipts_dni",  methods={"GET"})
     */
    public function getPersonReceiptsAction(Request $request, GTWINIntegrationService $gts, SerializerInterface $serializer)
    {
        $dni = strtoupper($request->get('dni'));
        $exists = $gts->personExists($dni);
        if ($exists) {
            $recibos = $gts->findByRecibosPendientesByDni($dni);
            $recibosNoPagados = $this->removeAlreadyPaid($recibos);
            $data = new ApiResponse('OK', 'Found', $recibosNoPagados);
        } else {
            $data = new ApiResponse('KO', 'Not Found', null);
        }
        $serialized = $serializer->serialize($data, 'json');
        $response = new JsonResponse($serialized, 200, [
            'Content-Type' => 'application/json;charset=utf-8'
        ], true);

        return $response;
    }

    /**
     * @Route("/receipts/confirmation", methods={"POST"})
     */
    public function receiptsConfirmation(Request $request, LoggerInterface $logger, GTWINIntegrationService $gts, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $logger->debug('Origin: ' . $request->headers->get('Origin'));
        $logger->debug($request->getContent());
        $origin = $request->headers->get('Origin');
        // $origin = 'https://testamorebieta.smartappcity.com';
        if ($origin !== $this->getParameter('api_origin')) {
            return new \Symfony\Component\HttpFoundation\Response(null, 401);
        }

        if (null === $request->getContent() || empty($request->getContent())) {
            return new JsonResponse(
                $serializer->serialize(
                    new ApiResponse('NOK', 'No response data found', null),
                    'json'
                )
            );
        }
        $logger->debug('Before create payment');
        $payment = Payment::createPaymentFromJson($request->getContent());
        $logger->debug('After create payment');

        $existingPayment = $this->paymentRepo->findOneBy([
            'referenceNumber' => $payment->getReferenceNumber(),
            'status' => Payment::PAYMENT_STATUS_OK,
        ]);
        if ($existingPayment) {
            $logger->debug('Receipt already payd');
            return new JsonResponse(
                $serializer->serialize(
                    new ApiResponse('OK', 'Receipt already payd', null),
                    'json'
                )
            );
        }
        $logger->debug('Before finding recibo: '. $payment->getReferenceNumber());
        $recibo = $gts->findByNumReciboDni($payment->getReferenceNumber(), $payment->getNif());
        if (null !== $recibo) {
            try {
                $logger->debug('Before paidWithCreditCard');
                $gts->paidWithCreditCard($recibo->getNumeroRecibo(), $recibo->getFraccion(), $payment->getQuantity(), $payment->getTimeStamp(), '', 1);
                $logger->debug('After paidWithCreditCard');
                $em->persist($payment);
                $em->flush();
                $logger->debug('Receipt number ' . $payment->getReferenceNumber() . ' successfully paid');

                return new JsonResponse(
                    $serializer->serialize(
                        new ApiResponse('OK', 'Receipt succesfully payd', null),
                        'json'
                    )
                );
            } catch (Exception $e) {
                return new JsonResponse(
                    $serializer->serialize(
                        new ApiResponse('NOK', 'There was and error during the request: ' . $e->getMessage(), null),
                        'json'
                    )
                );
            }
        }

        $logger->debug('Receipt number ' . $payment->getReferenceNumber() . ' not found.');

        return new JsonResponse(
            $serializer->serialize(
                new ApiResponse('NOK', 'Receipt not found', null),
                'json'
            )
        );
    }

    public function removeAlreadyPaid($recibos)
    {
        $recibosNoPagados = [];
        foreach ($recibos as $recibo) {
            $payment = $this->paymentRepo->findOneBy([
                'referenceNumber' => str_pad($recibo->getNumeroRecibo(), 10, '0', STR_PAD_LEFT),
                'status' => Payment::PAYMENT_STATUS_OK,
            ]);
            if (null === $payment) {
                $recibosNoPagados[] = $recibo;
            }
        }

        return $recibosNoPagados;
    }

    /**
     * @Route("/category/{id}", name="api_category", methods={"GET"}, options = { "expose" = true })
     */
    public function getCategory($id, LoggerInterface $logger, SerializerInterface $serializer, CategoryRepository $repo)
    {
        /** @var Category $category */
        $category = $repo->find($id);
        $logger->debug('Get category Id: ' . $id);

        return new JsonResponse(
            $serializer->serialize(new ApiResponse('OK', 'Category found', $category),'json'), 
            200, [
                'Content-Type' => 'application/json;charset=utf-8'
            ], true
        );
    }

    /**
     * @Route("/instituciones", name="api_get_instituciones", methods={"GET"}, options = { "expose" = true })
     */
     public function getInstituciones(GTWINIntegrationService $gts, SerializerInterface $serializer) 
     {
        $instituciones = $gts->findInstituciones();

        return new JsonResponse(
            $serializer->serialize(new ApiResponse('OK', 'Instituciones found', $instituciones),'json'), 
            200, [
                'Content-Type' => 'application/json;charset=utf-8'
            ], true
        );
    }

    /**
     * @Route("/institucion/{codigo}/tiposIngreso", name="api_get_tipos_ingreso_institucion", methods={"GET"}, options = { "expose" = true })
     */
    public function getTiposIngresoPorInstitucion($codigo, GTWINIntegrationService $gts, SerializerInterface $serializer) 
    {
       $tiposIngreso = $gts->findTipoIngresoInstitucion($codigo);

       return new JsonResponse(
           $serializer->serialize(new ApiResponse('OK', 'Tipos de ingreso found', $tiposIngreso),'json'), 
           200, [
               'Content-Type' => 'application/json;charset=utf-8'
           ], true
       );
   }



    /**
     * @Route("/institucion/{codigo}", name="api_get_institucion", methods={"GET"}, options = { "expose" = true })
     */
    public function getInstitucion($codigo, GTWINIntegrationService $gts, SerializerInterface $serializer) 
    {
       $institucion = $gts->findInstitucionByCodigo($codigo);

       return new JsonResponse(
           $serializer->serialize(new ApiResponse('OK', 'Institucion found', $institucion),'json'), 
           200, [
               'Content-Type' => 'application/json;charset=utf-8'
           ], true
       );
   }

    /**
     * @Route("/tarifas/{sufijo}", name="api_get_tarifas", methods={"GET"}, options = { "expose" = true })
     */
    public function getTarifasTipoIngreso($sufijo, GTWINIntegrationService $gts, SerializerInterface $serializer)
    {
        $tarifas = $gts->findTarifasTipoIngreso($sufijo);

        return new JsonResponse(
            $serializer->serialize(new ApiResponse('OK', 'Tarifas found', $tarifas),'json'), 
            200, [
                'Content-Type' => 'application/json;charset=utf-8'
            ], true
        );
    }

    /**
     * @Route("/tipo-ingreso/{codigo}", name="api_get_tipo_ingreso", methods={"GET"}, options = { "expose" = true })
     */
    public function getTiposIngreso($codigo, GTWINIntegrationService $gts, SerializerInterface $serializer) 
    {
       $tiposIngreso = $gts->findTipoIngresoByCodigo($codigo);

       return new JsonResponse(
           $serializer->serialize(new ApiResponse('OK', 'Tipos de ingreso found', $tiposIngreso),'json'), 
           200, [
               'Content-Type' => 'application/json;charset=utf-8'
           ], true
       );
   }

    /**
     * @Route("/concepts", name="api_get_concepts", methods={"GET"}, options = { "expose" = true })
     */
    public function getConcepts(SerializerInterface $serializer) 
    {
       $concepts = $this->conceptRepo->findAll();

       return new JsonResponse(
           $serializer->serialize(new ApiResponse('OK', 'Concepts found', $concepts),'json'), 
           200, [
               'Content-Type' => 'application/json;charset=utf-8'
           ], true
       );
   }

}
