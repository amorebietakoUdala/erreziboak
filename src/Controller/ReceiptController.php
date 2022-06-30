<?php

namespace App\Controller;

//use App\Entity\Receipt;

use App\Entity\GTWIN\Recibo;
use App\Entity\GTWIN\ReferenciaC60;
use App\Entity\Payment;
use App\Form\ReceiptSearchForm;
use App\Service\GTWINIntegrationService;
use App\Validator\IsValidIBANValidator;
use Doctrine\ORM\EntityManagerInterface;
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
     * @Route("/receipts/findReferenciaC60/{referenciac60}", name="receipt_find_referencia_c60", methods={"GET"})
     */
    // public function findReferenciaC60(Request $request, $referenciac60, EntityManagerInterface $oracleEntityManager)
    // {
    //     $repo = $oracleEntityManager->getRepository(ReferenciaC60::class);
    //     $result = $repo->findRecibosByNumeroReferenciaC60($referenciac60);
    //     dd($result->getRecibo()->getNumeroRecibo());
    // }

    /**
     * @Route("/receipts", name="receipt_find", methods={"GET","POST"})
     */
    public function findReceiptsAction(Request $request, LoggerInterface $logger, GTWINIntegrationService $gts)
    {
        $logger->debug('-->findReceiptsAction: Start');
        $referenciaC60 = $request->get('referenciaC60');
        $email = $request->get('email');
        $form = $this->createForm(ReceiptSearchForm::class, [
            'referenciaC60' => $referenciaC60,
            'email' => $email,
        ]);
        $results = [];
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $email = $data['email'];
            if (null === $data['referenciaC60']) {
                $this->addFlash('error', 'Debe especificar una referencia');

                return $this->render('receipt/list.html.twig', [
                    'form' => $form->createView(),
                    'receipts' => $results,
                ]);
            }
            if (null !== $data['referenciaC60'] && !is_numeric($data['referenciaC60'])) {
                $this->addFlash('error', 'La refrencia no es correcta debe ser un número.');

                return $this->render('receipt/list.html.twig', [
                    'form' => $form->createView(),
                    'receipts' => $results,
                ]);
            }
            $references = $gts->findReferenciaC60($data['referenciaC60']);
            if (null === $references || 0 === count($references)) {
                $this->addFlash('error', 'messages.referenceNotFound');
            } else {
                $importeTotal = 0;
                $fechaLimitePagoBanco = $references[0]->getFechaLimitePagoBanco();
                $concepto = 'many';
                if (count($references) === 1) {
                    $concepto = $references[0]->getRecibo()->getTipoIngreso()->getDescripcion();
                }
                foreach ($references as $reference) {
                    $importeTotal += ($reference->getCostas() + $reference->getRecargo()  + $reference->getIntereses() + $reference->getPrincipal());
                }
            }
            if ($fechaLimitePagoBanco < new \DateTime()) {
                $this->addFlash('error', 'messages.fechaLimitePagoBancoVencida');
                return $this->render('receipt/search.html.twig', [
                    'form' => $form->createView(),
                    'references' => [],
                ]);
            }
            return $this->render('receipt/search.html.twig', [
                'form' => $form->createView(),
                'fechaLimitePagoBanco' => $fechaLimitePagoBanco->format('Y/m/d'),
                'importeTotal' => $importeTotal,
                'referenciaC60' => $data['referenciaC60'],
                'references' => $references,
                'concepto' => $concepto,
                'email' => $data['email'],
            ]);
        }

        $logger->debug('<--findReceiptsAction: Results: ' . count($results));
        $logger->debug('<--findReceiptsAction: End OK');

        return $this->render('receipt/search.html.twig', [
            'form' => $form->createView(),
            'references' => $results,
            'search' => true,
            'readonly' => false,
        ]);
    }

    private function __createMiPagoParametersArray(Recibo $receipt)
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
                'citizen_nif' => $receipt->getDni() . $receipt->getLetra(),
                'citizen_phone' => null,
                'citizen_email' => $receipt->getEmail(),
            ],
            'receipt' => $receipt,
        ];

        return $params;
    }

    private function __createMiPagoParametersArrayFromC60Reference(array $referenciasC60, $email)
    {
        $importeTotal = 0;
        foreach ($referenciasC60 as $referencia) {
            $importeTotal += $referencia->getRecibo()->getImporteTotal();
        }
        $referencia = $referenciasC60[0];

        $params = [
            'reference_number' => substr($referencia->getReferenciaC60(), 0, -2),
            'payment_limit_date' => $referencia->getFechaLimitePagoBanco()->format('Ymd'),
            'sender' => $this->getParameter('mipago.sender'),
            'suffix' => $referencia->getConcepto(),
            'quantity' => $importeTotal,
            'extra' => [
                // 'citizen_name' => $receipt->getNombre(),
                // 'citizen_surname_1' => $receipt->getApellido1(),
                // 'citizen_surname_2' => $receipt->getApellido2(),
                // 'citizen_nif' => $receipt->getDni() . $receipt->getLetra(),
                // 'citizen_phone' => null,
                'citizen_email' => $email,
            ],
            'receipt' => $referenciasC60,
        ];
        return $params;
    }

    /**
     * @Route("/pay/{receipt}", name="receipt_forwarded_pay", methods={"POST"})
     */
    public function payForwardedReceiptAction(Request $request, Recibo $receipt, LoggerInterface $logger)
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
     * @Route("/pay/reference/{referencia}", name="referenciac60_pay", methods={"GET", "POST"}, options={"expose"=true})
     */
    public function payForwardedC60ReferenceAction(Request $request, $referencia, LoggerInterface $logger, GTWINIntegrationService $gts)
    {
        $logger->debug('-->payForwardedC60ReferenceAction: Start');
        $email = $request->get('email');
        $references = $gts->findReferenciaC60($referencia);
        if (count($references) === 0) {
            $this->addFlash('error', 'messages.referenceNotFound');
            return $this->redirectToRoute('receipt_find', [
                'referenciaC60' => $referencia,
                'email' => $email,
            ]);
        }

        if (null !== $references) {
            $logger->debug('<--payForwardedC60ReferenceAction: End Forwarded to MiPago\Bundle\Controller\PaymentController::sendRequestAction');

            return $this->forward('MiPago\Bundle\Controller\PaymentController::sendRequestAction', $this->__createMiPagoParametersArrayFromC60Reference($references, $email));
        } else {
            $this->addFlash('error', 'Recibo no encontrado');
            $logger->debug('<--payForwardedC60ReferenceAction: End Recibo no encontrado');

            return $this->redirectToRoute('receipt_find', [
                'referenciaC60' => $referencia,
                'email' => $email,
            ]);
        }
        $logger->debug('-->payForwardedC60ReferenceAction: End OK');
    }

    /**
     * @Route("/pay/{numeroRecibo}/{dni}", name="receipt_pay", methods={"GET","POST"}, options={"expose"=true})
     */
    public function payReceiptAction(Request $request, $numeroRecibo, $dni, GTWINIntegrationService $gts, LoggerInterface $logger)
    {
        $logger->debug('-->payReceiptAction: Start');
        $user = $this->getUser();
        $roles = (null === $user) ? ['IS_AUTHENTICATED_ANONYMOUSLY'] : $user->getRoles();
        $email = $request->get('email');
        $form = $this->createForm(ReceiptSearchForm::class, null);
        if (null === $user && (null === $dni || null === $numeroRecibo)) {
            $this->addFlash('error', 'El dni y el número de recibo son obligatorios');
            $logger->debug('<--payReceiptAction: End El dni y el número de recibo son obligatorios');

            return $this->render('receipt/list.html.twig', [
                'form' => $form->createView(),
                'receipts' => [],
                'email' => $email,
            ]);
        }
        $receipt = $gts->findByNumReciboDni($numeroRecibo, $dni);
        if (null === $receipt) {
            $this->addFlash('error', 'Recibo no encontrado');
            $logger->debug('<--payReceiptAction: End Recibo no encontrado');

            return $this->render('receipt/list.html.twig', [
                'form' => $form->createView(),
                'receipts' => [],
                'email' => $email,
            ]);
        }
        $logger->debug('<--payReceiptAction: End Forwarded to sendRequest');
        $receipt->setEmail($email);

        return $this->forward('MiPago\Bundle\Controller\PaymentController::sendRequestAction', $this->__createMiPagoParametersArray($receipt));
    }

    /**
     * @Route("/receiptConfirmation", name="receipt_confirmation", methods={"GET","POST"})
     */
    public function receiptConfirmationAction(Request $request, LoggerInterface $logger, GTWINIntegrationService $gts, Swift_Mailer $mailer)
    {
        $logger->debug('-->ReceiptConfirmationAction: Start');
        $payment = $request->get('payment');
        $reference_number = intval($payment->getReferenceNumberDC());
        $logger->info('ReferenceNumberDC: ' . $reference_number . ', Status: ' . $payment->getStatus() . ', PaymentId: ' . $payment->getId());
        /* A reference number can be specify one or more receipts */
        $recibos = $gts->findRecibosByNumeroReferenciaC60($payment->getReferenceNumberDC());
        $this->__sendConfirmationEmails($recibos, $payment, $mailer);
        $message = $this->__updatePayment($recibos, $payment, $logger, $gts);
        $logger->debug('<--ReceiptConfirmationAction: End OK');

        return new JsonResponse($message);
    }

    private function __sendConfirmationEmails(array $recibos, Payment $payment, $mailer)
    {
        foreach ($recibos as $recibo) {
            if (true === $this->getParameter('mailer_sendConfirmation') && !empty($payment->getEmail())) {
                $emails = [$payment->getEmail()];
                $this->__sendMessage('Confirmación del Pago / Ordainketaren konfirmazioa', $recibo, $payment, $emails, $mailer);
            }
            if (true === $this->getParameter('mailer_sendBCC')) {
                $bccs = $this->getParameter('mailer_BCC_email');
                $this->__sendMessage('Confirmación del Pago / Ordainketaren konfirmazioa', $recibo, $payment, $bccs, $mailer);
            }
        }
    }

    private function __sendMessage($subject, Recibo $receipt, Payment $payment, $emails, $mailer)
    {
        $from = $this->getParameter('mailer_from');
        $message = new Swift_Message($subject);

        $message->setFrom($from);
        $message->setTo($emails);
        $message->setBody(
            $this->renderView('receipt/PaymentConfirmationMail.html.twig', [
                'receipt' => $receipt,
                'payment' => $payment,
            ])
        );
        $message->setContentType('text/html');
        $mailer->send($message);
    }

    private function __updatePayment($recibos, Payment $payment, LoggerInterface $logger, GTWINIntegrationService $gts)
    {
        $errors = [];
        $allErrors = [];
        $index = 1;
        foreach ($recibos as $recibo) {
            // No need to update
            if (null === $recibo->getNumeroRecibo()) {
                $logger->info('Receipt not found: ' . $recibo->getNumeroRecibo());
                $errors[] =  'Receipt not found: ' . $recibo->getNumeroRecibo();
                $allErrors[] = 'Receipt not found: ' . $recibo->getNumeroRecibo();
            }
            if ($recibo->getEstaPagado()) {
                $logger->info('Already paid: ' . $recibo->getNumeroRecibo());
                $errors[] =  'Already paid: ' . $recibo->getNumeroRecibo();
                $allErrors[] = 'Already paid: ' . $recibo->getNumeroRecibo();
            }

            if (null === $payment) {
                $logger->info('No payment to update status');
                $errors[] =  'No payment to update status';
                $allErrors[] =  'No payment to update status';
            }
            if (!$payment->isPaymentSuccessfull()) {
                $logger->info('Payment not successfull');
                $errors[] =  'Payment not successfull';
                $allErrors[] =  'Payment not successfull';
            }
            if (count($errors) === 0) {
                $gts->paidWithCreditCard($recibo->getNumeroRecibo(), $recibo->getFraccion(), $recibo->getImporteTotal(), $payment->getTimestamp(), $payment->getRegisteredPaymentId(), $index);
            }
            $errors = [];
            $index += 1;
        }
        if (count($allErrors) > 0) {
            return 'NOK';
        } else {
            return 'OK';
        }
    }

    /**
     * @Route("/receipts/testIBAN", name="receipt_testIBAN", methods={"GET"})
     */
    public function testIBANAction(Request $request, IsValidIBANValidator $validator)
    {
        $iban = $request->get('iban');
        $valid = $validator->validateIBAN($iban);

        return new JsonResponse([
            'valid' => $valid,
        ]);
    }
}
