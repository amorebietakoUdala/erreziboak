<?php

namespace App\Controller;

//use App\Entity\Receipt;

use App\Entity\GTWIN\ReciboGTWIN;
use App\Form\ReceiptSearchForm;
use App\Service\GTWINIntegrationService;
use Psr\Log\LoggerInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{_locale}", requirements={
 *	    "_locale": "es|eu|en"
 * })
 */
class ReceiptController extends AbstractController
{
    /**
     * @Route("/receipts", name="receipt_find", methods={"GET","POST"})
     */
    public function findReceiptsAction(Request $request, LoggerInterface $logger, GTWINIntegrationService $gts)
    {
        $logger->debug('-->findReceiptsAction: Start');
        $user = $this->getUser();
        $roles = (null === $user) ? ['IS_AUTHENTICATED_ANONYMOUSLY'] : $user->getRoles();
        $numeroRecibo = $request->get('numeroRecibo');
        $dni = $request->get('dni');
        $reciboGTWIN = new ReciboGTWIN();
        $reciboGTWIN->setDni($dni);
        $reciboGTWIN->setNumeroRecibo($numeroRecibo);
        $form = $this->createForm(ReceiptSearchForm::class, $reciboGTWIN, [
            'roles' => $roles,
        ]);
        $results = [];
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $data ReciboGTWIN */
            $data = $form->getData();
            if (null === $user && (null === $data->getDni() || null === $data->getNumeroRecibo())) {
                $this->addFlash('error', 'El dni y el número de recibo son obligatorios');

                return $this->render('receipt/list.html.twig', [
                    'form' => $form->createView(),
                    'receipts' => $results,
                ]);
            }
            /* @var $result ReciboGTWIN */
            $result = $gts->findByExample($data);
            $receipts = [];
            if (null === $result || 0 === count($result)) {
                $this->addFlash('error', 'Recibo no encontrado');
            }
            if (null !== $result && 1 === count($result)) {
                $errores = $result[0]->comprobarCondicionesPago();
                foreach ($errores as $error) {
                    $this->addFlash('error', $error);
                }
                if (0 === sizeof($errores)) {
                    $receipts = $result;
                }
            } else {
                $receipts = $result;
            }

            return $this->render('receipt/list.html.twig', [
                'form' => $form->createView(),
                'receipts' => $receipts,
            ]);
        }

        $logger->debug('<--findReceiptsAction: Results: '.count($results));
        $logger->debug('<--findReceiptsAction: End OK');

        return $this->render('receipt/list.html.twig', [
            'form' => $form->createView(),
            'receipts' => $results,
            'search' => true,
            'readonly' => false,
        ]);
    }

    private function __createMiPagoParametersArray(ReciboGTWIN $receipt)
    {
        $params = [
            'reference_number' => $receipt->getNumeroRecibo(),
            'payment_limit_date' => $receipt->getFechaLimitePagoBanco()->format('Ymd'),
            'sender' => $this->getParameter('mipago.sender'),
            'suffix' => $receipt->getTipoIngreso()->getConceptoC60(),
            'quantity' => $receipt->getImporteTotal(),
            'extra' => [
                'citizen_name' => $receipt->getNombre(),
                'citizen_surname_1' => $receipt->getApellido1(),
                'citizen_surname_2' => $receipt->getApellido2(),
                'citizen_nif' => $receipt->getDni().$receipt->getLetra(),
                'citizen_phone' => null,
                'citizen_email' => null,
            ],
            'receipt' => $receipt,
        ];

        return $params;
    }

    /**
     * @Route("/pay/{receipt}", name="receipt_forwarded_pay", methods={"POST"})
     */
    public function payForwardedReceiptAction(Request $request, ReciboGTWIN $receipt, LoggerInterface $logger)
    {
        $logger->debug('-->payForwardedReceiptAction: Start');
        if (null != $receipt) {
            $logger->debug('<--payForwardedReceiptAction: End Forwarded to MiPago\Bundle\Controller\PaymentController::sendRequestAction');

            return $this->forward('MiPago\Bundle\Controller\PaymentController::sendRequestAction', $this->__createMiPagoParametersArray($receipt));
        } else {
            $this->addFlash('error', 'Recibo no encontrado');
            $logger->debug('<--payForwardedReceiptAction: End Recibo no encontrado');

            return $this->render('receipt/list.html.twig', [
            'form' => $form->createView(),
            'receipts' => [],
            ]);
        }
        $logger->debug('-->payForwardedReceiptAction: End OK');
    }

    /**
     * @Route("/pay/{numeroRecibo}/{dni}", name="receipt_pay", methods={"GET","POST"}, options={"expose"=true})
     */
    public function payReceiptAction(Request $request, $numeroRecibo, $dni, GTWINIntegrationService $gts, LoggerInterface $logger)
    {
        $logger->debug('-->payReceiptAction: Start');
        $user = $this->getUser();
        $roles = (null === $user) ? ['IS_AUTHENTICATED_ANONYMOUSLY'] : $user->getRoles();
        $form = $this->createForm(ReceiptSearchForm::class, new ReciboGTWIN(), [
            'roles' => $roles,
        ]);
        if (null === $user && (null === $dni || null === $numeroRecibo)) {
            $this->addFlash('error', 'El dni y el número de recibo son obligatorios');
            $logger->debug('<--payReceiptAction: End El dni y el número de recibo son obligatorios');

            return $this->render('receipt/list.html.twig', [
                'form' => $form->createView(),
                'receipts' => [],
            ]);
        }
        $receipt = $gts->findByNumReciboDni($numeroRecibo, $dni);
        if (null === $receipt) {
            $this->addFlash('error', 'Recibo no encontrado');
            $logger->debug('<--payReceiptAction: End Recibo no encontrado');

            return $this->render('receipt/list.html.twig', [
                'form' => $form->createView(),
                'receipts' => [],
            ]);
        }
        $logger->debug('<--payReceiptAction: End Forwarded to sendRequest');

        return $this->forward('MiPago\Bundle\Controller\PaymentController::sendRequestAction', $this->__createMiPagoParametersArray($receipt));
    }

    /**
     * @Route("/receiptConfirmation", name="receipt_confirmation", methods={"GET","POST"})
     */
    public function receiptConfirmationAction(Request $request, LoggerInterface $logger, GTWINIntegrationService $gts, Swift_Mailer $mailer)
    {
        $logger->debug('-->ReceiptConfirmationAction: Start');
        $payment = $request->get('payment');
        $reference_number = intval($payment->getReference_number());
        $logger->info('ReferenceNumber: '.$reference_number.', Status: '.$payment->getStatus().', PaymentId: '.$payment->getId());
        $receipt = $gts->findByNumRecibo($payment->getReference_number());
        $this->__sendConfirmationEmails($receipt, $payment, $mailer);
        $this->__updatePayment($receipt, $payment, $logger, $gts);
        $logger->debug('<--ReceiptConfirmationAction: End OK');

        return new JsonResponse('OK');
    }

    private function __sendConfirmationEmails(ReciboGTWIN $receipt, \MiPago\Bundle\Entity\Payment $payment, $mailer)
    {
        if (true === $this->getParameter('mailer_sendConfirmation') && !empty($receipt->getEmail())) {
            $emails = [$receipt->getEmail()];
            $this->__sendMessage('Confirmación del Pago / Ordainketaren konfirmazioa', $receipt, $payment, $emails, $mailer);
        }
        if (true === $this->getParameter('mailer_sendBCC')) {
            $bccs = $this->getParameter('mailer_BCC_email');
            $this->__sendMessage('Confirmación del Pago / Ordainketaren konfirmazioa', $receipt, $payment, $bccs, $mailer);
        }
    }

    private function __sendMessage($subject, ReciboGTWIN $receipt, \MiPago\Bundle\Entity\Payment $payment, $emails, $mailer)
    {
        $from = $this->getParameter('mailer_from');
        $message = new Swift_Message($subject);
        $message->setFrom($from);
        $message->setTo($emails);
        $message->setBody(
            $this->renderView('/receipt/PaymentConfirmationMail.html.twig', [
                'receipt' => $receipt,
                'payment' => $payment,
            ])
        );
        $message->setContentType('text/html');
        $mailer->send($message);
    }

    private function __updatePayment(ReciboGTWIN $receipt, \MiPago\Bundle\Entity\Payment $payment, LoggerInterface $logger, GTWINIntegrationService $gts)
    {
        // No need to update
        if (null === $receipt->getNumeroRecibo()) {
            $logger->info('No GTWIN reference to update');

            return;
        }
        if (null === $payment) {
            $logger->info('No payment to update status in GTWIN');

            return;
        }
        if (!$payment->isPaymentSuccessfull()) {
            $logger->info('Payment not successfull no need to update payment in GTWIN');

            return;
        }
        $gts->paidWithCreditCard($receipt->getNumeroRecibo(), $receipt->getFraccion(), $payment->getQuantity(), $payment->getTimestamp(), $payment->getRegistered_payment_id());
    }
}
