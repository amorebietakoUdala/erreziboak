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
use App\Entity\Concept;
use App\Entity\ConceptInscription;
use Exception;
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
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/api')]
class RestController extends AbstractController
{

    private array $defaultHeaders = [];

    public function __construct(
        private readonly PaymentRepository $paymentRepo, 
        private readonly ConceptRepository $conceptRepo, 
        private readonly GTWINIntegrationService $gts, 
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger
    )
    {
        $this->defaultHeaders = [
            'Content-Type' => 'application/json;charset=utf-8',
            'Cache-Control' => 'no-cache',
        ];
    }

    #[Route(path: '/concepts', name: 'api_get_concepts', methods: ['GET'], options: ['expose' => true])]
    public function getConcepts() : Response
    {
       $this->logger->debug('Getting concepts');
       $concepts = $this->conceptRepo->findAll();
       $this->logger->debug('Total Concepts:'. count($concepts));
       $conceptsArray = [];
       foreach ($concepts as $key => $value) {
            $tipoIngreso = $this->gts->findTipoIngresoByConceptoC60($value->getSuffix());
            $conceptsArray[] = [
                'concept' => $value,
                'tipoIngreso' => $tipoIngreso
            ];
       }
       $apiResponse = new ApiResponse('OK', 'Concepts found', $conceptsArray);
       $this->logger->debug('Before serialization');
       $json = $this->serializer->serialize($apiResponse,'json', ['groups' => ['show']]);
       $this->logger->debug('Serialized: '.$json);
       return new JsonResponse($json, Response::HTTP_OK, $this->defaultHeaders, true);
    }

    /**
     * Check the existance of the person by DNI.
     */
    #[Route(path: '/person/{dni}', name: 'api_person', methods: ['GET'])]
    public function getCheckPerson(string $dni)
    {
        $exists = $this->gts->personExists($dni);
        if ($exists) {
            $data = new ApiResponse('OK', 'Found', null);
        } else {
            $data = new ApiResponse('KO', 'Not Found', null);
        }
        $response = new JsonResponse($this->serializer->serialize($data, 'json', ['groups' => ['show']]), Response::HTTP_OK, [], true);
        return $response;
    }

    /**
     * Check the existance of the person by DNI.
     */
    #[Route(path: '/receipts/new', name: 'api_receipt_new', methods: ['POST'])]
    public function receiptNew (Request $request, RouterInterface $router) {
        $inscription = new ConceptInscription();
        $form = $this->createForm(ConceptInscriptionTypeForm::class, $inscription, [
            'locale' => $request->getLocale(),
            'csrf_protection' => false,
        ]);
        $json = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $form->submit($json);
        /** @var ConceptInscription $data */ 
        $data = $form->getData();
        $recibo = $this->gts->createReciboForInscription($data, true);
        $paymentURL = $router->generate('receipt_pay',[
            'numeroRecibo' => $recibo->getNumeroRecibo(),
            'dni' => $data->getDni(), 
        ], RouterInterface::ABSOLUTE_URL);

        $response = new JsonResponse($this->serializer->serialize(new ApiResponse('OK', 'Received', [
            'recibo' => $recibo,
            'paymentURL' => $paymentURL,
        ]), 'json', ['groups' => ['show']]), Response::HTTP_OK, [
            'Content-Type' => 'application/json;charset=utf-8'
        ], true);
        return $response;
    }

    /**
     * Returns a list of unpayd receipts for the DNI.
     */
    #[Route(path: '/receipts/{dni}', name: 'api_receipts_dni', methods: ['GET'])]
    public function getPersonReceipts($dni)
    {
        $dni = strtoupper($dni);
        $exists = $this->gts->personExists($dni);
        if ($exists) {
            $recibos = $this->gts->findByRecibosPendientesByDni($dni);
            $recibosNoPagados = $this->removeAlreadyPaid($recibos);
            $data = new ApiResponse('OK', 'Found', $recibosNoPagados);
        } else {
            $data = new ApiResponse('KO', 'Not Found', null);
        }
        $serialized = $this->serializer->serialize($data, 'json', ['groups' => ['show']]);
        $response = new JsonResponse($serialized, Response::HTTP_OK, [
            'Content-Type' => 'application/json;charset=utf-8'
        ], true);
        return $response;
    }

    #[Route(path: '/receipts/confirmation', methods: ['POST'])]
    public function receiptsConfirmation(Request $request, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('Origin: ' . $request->headers->get('Origin'));
        $logger->debug($request->getContent());
        $origin = $request->headers->get('Origin');
        // $origin = 'https://testamorebieta.smartappcity.com';
        if ($origin !== $this->getParameter('api_origin')) {
            return new Response(null, Response::HTTP_UNAUTHORIZED);
        }

        if (null === $request->getContent() || empty($request->getContent())) {
            return new JsonResponse(
                $this->serializer->serialize(
                    new ApiResponse('NOK', 'No response data found', null),
                    'json', ['groups' => ['show']]
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
                $this->serializer->serialize(
                    new ApiResponse('OK', 'Receipt already payd', null),
                    'json', ['groups' => ['show']]
                )
            );
        }
        $logger->debug('Before finding recibo: '. $payment->getReferenceNumber());
        $recibo = $this->gts->findByNumReciboDni($payment->getReferenceNumber(), $payment->getNif());
        if (null !== $recibo) {
            try {
                $logger->debug('Before paidWithCreditCard');
                $this->gts->paidWithCreditCard($recibo->getNumeroRecibo(), $recibo->getFraccion(), $payment->getQuantity(), $payment->getTimeStamp(), '', 1);
                $logger->debug('After paidWithCreditCard');
                $em->persist($payment);
                $em->flush();
                $logger->debug('Receipt number ' . $payment->getReferenceNumber() . ' successfully paid');

                return new JsonResponse(
                    $this->serializer->serialize(
                        new ApiResponse('OK', 'Receipt succesfully payd', null),
                        'json', ['groups' => ['show']]
                    )
                );
            } catch (Exception $e) {
                return new JsonResponse(
                    $this->serializer->serialize(
                        new ApiResponse('NOK', 'There was and error during the request: ' . $e->getMessage(), null),
                        'json', ['groups' => ['show']]
                    )
                );
            }
        }

        $logger->debug('Receipt number ' . $payment->getReferenceNumber() . ' not found.');

        return new JsonResponse(
            $this->serializer->serialize(
                new ApiResponse('NOK', 'Receipt not found', null),
                'json', ['groups' => ['show']]
            )
        );
    }

    public function removeAlreadyPaid($recibos)
    {
        $recibosNoPagados = [];
        foreach ($recibos as $recibo) {
            $payment = $this->paymentRepo->findOneBy([
                'referenceNumber' => str_pad((string) $recibo->getNumeroRecibo(), 10, '0', STR_PAD_LEFT),
                'status' => Payment::PAYMENT_STATUS_OK,
            ]);
            if (null === $payment) {
                $recibosNoPagados[] = $recibo;
            }
        }

        return $recibosNoPagados;
    }

    #[Route(path: '/category/{id}', name: 'api_category', methods: ['GET'], options: ['expose' => true])]
    public function getCategory($id, LoggerInterface $logger, CategoryRepository $repo)
    {
        /** @var Category $category */
        $category = $repo->find($id);
        $logger->debug('Get category Id: ' . $id);
        return new JsonResponse(
            $this->serializer->serialize(new ApiResponse('OK', 'Category found', $category),'json', ['groups' => ['show']]), 
            Response::HTTP_OK, [], true
        );
    }

    #[Route(path: '/instituciones', name: 'api_get_instituciones', methods: ['GET'], options: ['expose' => true])]
     public function getInstituciones() 
     {
        $instituciones = $this->gts->findInstituciones();

        return new JsonResponse(
            $this->serializer->serialize(new ApiResponse('OK', 'Instituciones found', $instituciones),'json', ['groups' => ['show']]), 
            Response::HTTP_OK, [], true
        );
    }

    #[Route(path: '/institucion/{codigo}/tiposIngreso', name: 'api_get_tipos_ingreso_institucion', methods: ['GET'], options: ['expose' => true])]
    public function getTiposIngresoPorInstitucion($codigo) 
    {
       $tiposIngreso = $this->gts->findTipoIngresoInstitucion($codigo);

       return new JsonResponse(
           $this->serializer->serialize(new ApiResponse('OK', 'Tipos de ingreso found', $tiposIngreso),'json', ['groups' => ['show']]), 
           Response::HTTP_OK, [], true
       );
   }



    #[Route(path: '/institucion/{codigo}', name: 'api_get_institucion', methods: ['GET'], options: ['expose' => true])]
    public function getInstitucion($codigo) 
    {
       $institucion = $this->gts->findInstitucionByCodigo($codigo);

       return new JsonResponse(
           $this->serializer->serialize(new ApiResponse('OK', 'Institucion found', $institucion),'json', ['groups' => ['show']]), 
           Response::HTTP_OK, [], true
       );
   }

    #[Route(path: '/tarifas/{sufijo}', name: 'api_get_tarifas', methods: ['GET'], options: ['expose' => true])]
    public function getTarifasTipoIngreso($sufijo)
    {
        $tarifas = $this->gts->findTarifasTipoIngreso($sufijo);

        return new JsonResponse(
            $this->serializer->serialize(new ApiResponse('OK', 'Tarifas found', $tarifas),'json', ['groups' => ['show']]), 
            Response::HTTP_OK, [
                'Content-Type' => 'application/json;charset=utf-8'
            ], true
        );
    }

    #[Route(path: '/tipo-ingreso/{codigo}', name: 'api_get_tipo_ingreso', methods: ['GET'], options: ['expose' => true])]
    public function getTiposIngreso($codigo) 
    {
       $tiposIngreso = $this->gts->findTipoIngresoByCodigo($codigo);

       return new JsonResponse(
           $this->serializer->serialize(new ApiResponse('OK', 'Tipos de ingreso found', $tiposIngreso),'json', ['groups' => ['show']]), 
           Response::HTTP_OK, [
               'Content-Type' => 'application/json;charset=utf-8'
           ], true
       );
   }

    #[Route(path: '/concept/{id}', name: 'api_get_concept', methods: ['GET'], options: ['expose' => true])]
    public function getConcept(Concept $concept) 
    {
       return new JsonResponse(
           $this->serializer->serialize(new ApiResponse('OK', 'Concept found', $concept),'json', ['groups' => ['show']]), 
           Response::HTTP_OK, [
               'Content-Type' => 'application/json;charset=utf-8'
           ], true
       );
   }

    #[Route(path: '/person/{dni}/has-debts', name: 'api_get_has_debts', methods: ['GET'], options: ['expose' => true])]
    public function getHasDebts(string $dni) 
    {
       $dni = mb_strtoupper($dni);
       $debts = $this->gts->hasDebts($dni);
       if ($debts === null) {
           return new JsonResponse(
               $this->serializer->serialize(new ApiResponse('KO', 'Person not found', null),'json', ['groups' => ['show']]), 
               Response::HTTP_OK, [
                   'Content-Type' => 'application/json;charset=utf-8'
               ], true
           );
       }
       $message = $debts['debts'] ? 'has debts' : 'has no debts';
       return new JsonResponse(
           $this->serializer->serialize(new ApiResponse('OK', $message, $debts),'json', ['groups' => ['show']]), 
           Response::HTTP_OK, [
               'Content-Type' => 'application/json;charset=utf-8'
           ], true
       );
   }
}
