<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use App\Service\GTWINIntegrationService;
use App\Utils\ApiResponse;
use Exception;
use JMS\Serializer\SerializerInterface;
use App\Entity\Payment;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
        $response = new JsonResponse($serializer->serialize($data, 'json'), 200);

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
    public function receiptsConfirmation(Request $request, LoggerInterface $logger, GTWINIntegrationService $gts, SerializerInterface $serializer)
    {
        $logger->debug('Origin: ' . $request->headers->get('Origin'));
        $logger->debug($request->getContent());
        if ($request->headers->get('Origin') !== $this->getParameter('api_origin')) {
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
        $payment = Payment::createPaymentFromJson($request->getContent());
        $em = $this->getDoctrine()->getManager();

        $existingPayment = $em->getRepository(Payment::class)->findOneBy([
            'referenceNumber' => $payment->getReferenceNumber(),
            'status' => Payment::PAYMENT_STATUS_OK,
        ]);
        if ($existingPayment) {
            return new JsonResponse(
                $serializer->serialize(
                    new ApiResponse('OK', 'Receipt already payd', null),
                    'json'
                )
            );
        }

        $recibo = $gts->findByNumReciboDni($payment->getReferenceNumber(), $payment->getNif());
        if (null !== $recibo) {
            try {
                $gts->paidWithCreditCard($recibo->getNumeroRecibo(), $recibo->getFraccion(), $payment->getQuantity(), $payment->getTimeStamp(), '', 'APP');
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
        $em = $this->getDoctrine()->getManager();
        $recibosNoPagados = [];
        foreach ($recibos as $recibo) {
            $payment = $em->getRepository(Payment::class)->findOneBy([
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
    public function getCategory($id, LoggerInterface $logger, SerializerInterface $serializer)
    {
        $em = $this->getDoctrine()->getManager();
        /* @var $category Category */
        $category = $em->getRepository(\App\Entity\Category::class)->find($id);
        $logger->debug('Get category Id: ' . $id);

        return new JsonResponse(
            $serializer->serialize(
                new ApiResponse('OK', 'Category found', $category),
                'json'
            )
        );
    }
}
