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

        $response = new JsonResponse($serializer->serialize($data, 'json'), 200);

        return $response;
    }

    /**
     * @Route("/receipts/confirmation", methods={"POST"})
     */
    public function receiptsConfirmation(Request $request, LoggerInterface $logger, GTWINIntegrationService $gts, SerializerInterface $serializer)
    {
        $logger->debug($request->getContent());
        $payment = Payment::createPaymentFromJson($request->getContent());

        $em = $this->getDoctrine()->getManager();

        $existingPayment = $em->getRepository(Payment::class)->findOneBy([
            'referenceNumber' => $payment->getReference_number(),
            'status' => Payment::PAYMENT_STATUS_OK,
        ]);
        if ($existingPayment) {
            return new JsonResponse(
                $serializer->serialize(
                        new ApiResponse('OK', 'Receipt already payd', null), 'json')
            );
        }

        $recibo = $gts->findByNumReciboDni($payment->getReference_number(), $payment->getNif());
        if (null !== $recibo) {
            try {
                $gts->paidWithCreditCard($recibo->getNumeroRecibo(), $recibo->getFraccion(), $payment->getQuantity(), $payment->getTimeStamp(), '', 'APP');
                $em->persist($payment);
                $em->flush();
                $logger->debug('Receipt number '.$payment->getReference_number().' successfully paid');

                return new JsonResponse(
                    $serializer->serialize(
                        new ApiResponse('OK', 'Receipt succesfully payd', null), 'json')
                    );
            } catch (Exception $e) {
                return new JsonResponse(
                    $serializer->serialize(
                        new ApiResponse('NOK', 'There was and error during the request: '.$e->getMessage(), null), 'json')
                );
            }
        }

        $logger->debug('Receipt number '.$payment->getReference_number().' not found.');

        return new JsonResponse(
            $serializer->serialize(
                new ApiResponse('NOK', 'Receipt not found', null), 'json')
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
}
