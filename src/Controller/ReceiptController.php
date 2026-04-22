<?php

namespace App\Controller;

use App\Entity\GTWIN\Recibo;
use App\Service\BarcodeService;
use App\Entity\Payment;
use App\Form\ReceiptSearchForm;
use App\Repository\PaymentRepository;
use App\Service\GTWINIntegrationService;
use App\Service\JsonConfigManager;
use App\Validator\IsValidIBANValidator;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/{_locale}', requirements: ['_locale' => 'es|eu|en'])]
class ReceiptController extends BaseController
{

    private $definiciones = null;

    public function __construct(
        private readonly MailerInterface $mailer, 
        private readonly GTWINIntegrationService $gts,
        private readonly PaymentRepository $paymentRepo,
        private readonly LoggerInterface $logger,
        private readonly JsonConfigManager $jcm,

    )
    {
        $this->definiciones = $this->jcm->all();
    }


    // public function findReferenciaC60(Request $request, $referenciac60, EntityManagerInterface $oracleEntityManager)
    // {
    //     $repo = $oracleEntityManager->getRepository(ReferenciaC60::class);
    //     $result = $repo->findRecibosByNumeroReferenciaC60($referenciac60);
    //     dd($result->getRecibo()->getNumeroRecibo());
    // }

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

    #[Route(path: '/my-unpaid-receipts', name: 'my_receipts_unpayed_receipts', methods: ['GET'])]
    public function getPersonReceipts(Request $request): Response
    {
        $this->loadQueryParameters($request);
        $this->queryParams['pageSize'] = $request->query->get('pageSize', 100);
        if (!$request->getSession()->has('giltzaUser')) {
            return $this->redirectToRoute('amreu_giltza_login');
        }
        $giltzaUser = $request->getSession()->get('giltzaUser');
        $this->logger->debug('Giltza User: ' . json_encode($giltzaUser));

        $dni = null;
        if ( isset($giltzaUser['cif']) && isset($giltzaUser['person_status']) && $giltzaUser['person_status'] === 'RE' ) {
            $dni = $giltzaUser['cif'];
        }
        if ( null === $dni && isset($giltzaUser['dni']) ) {
            $dni = $giltzaUser['dni'];
        }

        $dni = '72318623E';

        $this->logger->info("Giltza User DNI or CIF: $dni" );
        $exists = $this->gts->personExists($dni);
        if ($exists) {
            $recibos = $this->gts->findByRecibosPendientesByDni($dni);
            $recibosNoPagados = $this->removeAlreadyPaid($recibos);
        } else {
            $recibosNoPagados = [];
            $this->addFlash('error', 'messages.personNotExists');
        }

        return $this->render('receipt/unpaid_receipts.html.twig', [
            'dni' => $dni,
            'receipts' => $recibosNoPagados,
        ]);
    }

    // #[Route('/barcode/{value}', name: 'barcode')]
    // public function barcode(string $value, BarcodeService $barcodeService): Response
    // {
    //     $image = $barcodeService->generateCode128($value);

    //     return new Response($image, 200, [
    //         'Content-Type' => 'image/png',
    //     ]);
    // }

    #[Route(path: '/receipt/{id}', name: 'receipt_show', methods: ['GET'])]
    public function show(Request $request, string $id)
    {
        if ( $this->getUser() && $this->isGranted('ROLE_RECEIPTS') ) {
            $this->logger->debug('User is granted ROLE_RECEIPTS no need to login with Giltza');
        } else {
            if (!$request->getSession()->has('giltzaUser')) {
                return $this->redirectToRoute('amreu_giltza_login');
            }
            $giltzaUser = $request->getSession()->get('giltzaUser');
            $this->logger->debug('Giltza User: ' . json_encode($giltzaUser));
        }
        /** @var Recibo $recibo */
        $recibo = $this->gts->find($id);
        $cuerpo = $recibo->getCamposBaseImponible($this->definiciones);
        return $this->render('receipt/show.html.twig', [
            'receipt' => $recibo,
            'cuerpo' => $cuerpo,
        ]);
    }

    #[IsGranted('ROLE_RECEIPTS')]
    #[Route(path: '/receipts', name: 'receipt_find', methods: ['GET', 'POST'])]
    public function findReceipts(Request $request)
    {
        $this->logger->debug('-->findReceipts: Start');
        $this->loadQueryParameters($request);
        $form = $this->createForm(ReceiptSearchForm::class);
        $receipts = [];
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Not necesary to fill referenciac60
            // if (null === $data['referenciaC60']) {
            //     $this->addFlash('error', 'Debe especificar una referencia');

            //     return $this->render('receipt/search.html.twig', [
            //         'form' => $form,
            //         'references' => $results,
            //     ]);
            // }
            if (null === $data['referenciaC60'] && null === $data['numeroRecibo'] && null === $data['dni'] && null === $data['numeroReferenciaExterna'] ) {
                $this->addFlash('error', 'messages.atLeastOneCriteria');

                return $this->render('receipt/search.html.twig', [
                    'form' => $form,
                    'receipts' => $receipts,
                ]);
            }
            if (null !== $data['referenciaC60'] && !is_numeric($data['referenciaC60'])) {
                $this->addFlash('error', 'La refrencia no es correcta debe ser un número.');

                return $this->render('receipt/search.html.twig', [
                    'form' => $form,
                    'references' => $receipts,
                    'receipts' => $receipts,
                ]);
            }
            if ( null !== $data['referenciaC60'] ) {
                $references = $this->gts->findReferenciaC60($data['referenciaC60']);
                $receipts = $references;
            } else {
                $references = $this->gts->findByCriteria($data);
            }
            $receipts = $references;   
            // Uncomment is references are needed.
            // $fechaLimitePagoBanco = null;
            // $importeTotal = 0;
            // $concepto = null;
            // if (null === $references || 0 === count($references)) {
            //     $this->addFlash('error', 'messages.referenceNotFound');
            // } else {
            //     $fechaLimitePagoBanco = $references[0]->getFechaLimitePagoBanco();
            //     $concepto = 'many';
            //     if (count($references) === 1) {
            //         $concepto = $references[0]->getRecibo()->getTipoIngreso()->getDescripcion();
            //     }
            //     foreach ($references as $reference) {
            //         $importeTotal += ($reference->getCostas() + $reference->getRecargo()  + $reference->getIntereses() + $reference->getPrincipal());
            //     }
            //     if ($fechaLimitePagoBanco < new \DateTime()) {
            //         $this->addFlash('error', 'messages.fechaLimitePagoBancoVencida');
            //         return $this->render('receipt/search.html.twig', [
            //             'form' => $form,
            //             'references' => [],
            //         ]);
            //     }
            // }
            return $this->render('receipt/search.html.twig', [
                'form' => $form,
                // 'fechaLimitePagoBanco' => $fechaLimitePagoBanco !== null ? $fechaLimitePagoBanco->format('Y/m/d') : null,
                // 'importeTotal' => $importeTotal,
                'referenciaC60' => $data['referenciaC60'],
                'references' => $references,
                'receipts' => $receipts,
                // 'concepto' => $concepto,
                'readonly' => false,
            ]);
        }

        
        $this->logger->debug('<--findReceipts: Results: ' . count($receipts));
        $this->logger->debug('<--findReceipts: End OK');

        return $this->render('receipt/search.html.twig', [
            'form' => $form,
            'receipts' => $receipts,
            'search' => true,
            'readonly' => false,
        ]);
    }

    private function __createMiPagoParametersArray(Recibo $receipt)
    {
        $params = [
            'reference_number' => $receipt->getNumeroRecibo(),
            // 'payment_limit_date' => $receipt->getFechaLimitePagoBanco()->format('Ymd'),
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
            'reference_number' => substr((string) $referencia->getReferenciaC60(), 0, -2),
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

    #[Route(path: '/pay/{numeroRecibo}', name: 'receipt_forwarded_pay', methods: ['GET'])]
    public function payForwardedReceipt(string $numeroRecibo, Request $request)
    {
        if (!$request->getSession()->has('giltzaUser')) {
            return $this->redirectToRoute('amreu_giltza_login');
        }
        $receipt= $this->gts->findByNumRecibo($numeroRecibo);
        $this->logger->debug('-->payForwardedReceipt: Start');
        if (null != $receipt) {
            $this->logger->debug('<--payForwardedReceipt: End Forwarded to MiPago\Bundle\Controller\PaymentController::sendRequest');

            return $this->forward('MiPago\Bundle\Controller\PaymentController::sendRequest', $this->__createMiPagoParametersArray($receipt));
        } else {
            $this->addFlash('error', 'Recibo no encontrado');
            $this->logger->debug('<--payForwardedReceipt: End Recibo no encontrado');
            $form = $this->createForm(ReceiptSearchForm::class);

            return $this->render('receipt/search.html.twig', [
                'form' => $form,
                'references' => [],
            ]);
        }
        $this->logger->debug('-->payForwardedReceipt: End OK');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/pay/reference/{referencia}', name: 'referenciac60_pay', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function payForwardedC60Reference(Request $request, $referencia)
    {
        $this->logger->debug('-->payForwardedC60Reference: Start');
        $email = $request->get('email');
        $references = $this->gts->findReferenciaC60($referencia);
        if (count($references) === 0) {
            $this->addFlash('error', 'messages.referenceNotFound');
            return $this->redirectToRoute('receipt_find', [
                'referenciaC60' => $referencia,
                'email' => $email,
            ]);
        }

        if (null !== $references) {
            $this->logger->debug('<--payForwardedC60Reference: End Forwarded to MiPago\Bundle\Controller\PaymentController::sendRequest');

            return $this->forward('MiPago\Bundle\Controller\PaymentController::sendRequest', $this->__createMiPagoParametersArrayFromC60Reference($references, $email));
        } else {
            $this->addFlash('error', 'Recibo no encontrado');
            $this->logger->debug('<--payForwardedC60Reference: End Recibo no encontrado');

            return $this->redirectToRoute('receipt_find', [
                'referenciaC60' => $referencia,
                'email' => $email,
            ]);
        }
        $this->logger->debug('-->payForwardedC60Reference: End OK');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/pay/{numeroRecibo}/{dni}', name: 'receipt_pay', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function payReceipt(Request $request, $numeroRecibo, $dni)
    {
        $this->logger->debug('-->payReceipt: Start');
        $user = $this->getUser();
        $roles = (null === $user) ? ['IS_AUTHENTICATED_ANONYMOUSLY'] : $user->getRoles();
        $email = $request->get('email');
        $form = $this->createForm(ReceiptSearchForm::class, null);
        if (null === $user && (null === $dni || null === $numeroRecibo)) {
            $this->addFlash('error', 'El dni y el número de recibo son obligatorios');
            $this->logger->debug('<--payReceipt: End El dni y el número de recibo son obligatorios');

            return $this->render('receipt/search.html.twig', [
                'form' => $form,
                'references' => [],
                'email' => $email,
            ]);
        }
        $receipt = $this->gts->findByNumReciboDni($numeroRecibo, $dni);
        if (null === $receipt) {
            $this->addFlash('error', 'Recibo no encontrado');
            $this->logger->debug('<--payReceipt: End Recibo no encontrado');

            return $this->render('receipt/search.html.twig', [
                'form' => $form,
                'references' => [],
                'email' => $email,
            ]);
        }
        $this->logger->debug('<--payReceipt: End Forwarded to sendRequest');
        $receipt->setEmail($email);

        return $this->forward('MiPago\Bundle\Controller\PaymentController::sendRequest', $this->__createMiPagoParametersArray($receipt));
    }

    #[Route(path: '/receiptConfirmation', name: 'receipt_confirmation', methods: ['GET', 'POST'])]
    public function receiptConfirmation(Request $request)
    {
        $this->logger->debug('-->ReceiptConfirmation: Start');
        $payment = $request->get('payment');
        $reference_number = intval($payment->getReferenceNumberDC());
        $this->logger->info('ReferenceNumberDC: ' . $reference_number . ', Status: ' . $payment->getStatus() . ', PaymentId: ' . $payment->getId());
        /* A reference number can be specify one or more receipts */
        $recibos = $this->gts->findRecibosByNumeroReferenciaC60($payment->getReferenceNumberDC());
        $this->sendConfirmationEmails($recibos, $payment);
        $message = $this->__updatePayment($recibos, $payment);
        $this->logger->debug('<--ReceiptConfirmation: End OK');

        return new JsonResponse($message);
    }

    private function sendConfirmationEmails(array $recibos, Payment $payment)
    {
        foreach ($recibos as $recibo) {
            if (true === $this->getParameter('mailer_sendConfirmation') && !empty($payment->getEmail())) {
                $emails = [$payment->getEmail()];
                $this->sendMessage('Confirmación del Pago / Ordainketaren konfirmazioa', $recibo, $payment, $emails);
            }
            if (true === $this->getParameter('mailer_sendBCC')) {
                $bccs = $this->getParameter('mailer_BCC_email');
                $this->sendMessage('Confirmación del Pago / Ordainketaren konfirmazioa', $recibo, $payment, $bccs);
            }
        }
    }

    private function sendMessage($subject, Recibo $receipt, Payment $payment, $emails)
    {
        $email = (new Email())
            ->from($this->getParameter('mailer_from'))
            ->to($emails)
            ->subject($subject)
            ->html($this->renderView('receipt/PaymentConfirmationMail.html.twig', [
                'receipt' => $receipt,
                'payment' => $payment,
            ]),
            'text/html'
        );
        $this->mailer->send($email);
        return;
    }

    private function __updatePayment($recibos, Payment $payment)
    {
        $errors = [];
        $allErrors = [];
        $index = 1;
        foreach ($recibos as $recibo) {
            // No need to update
            if (null === $recibo->getNumeroRecibo()) {
                $this->logger->info('Receipt not found: ' . $recibo->getNumeroRecibo());
                $errors[] =  'Receipt not found: ' . $recibo->getNumeroRecibo();
                $allErrors[] = 'Receipt not found: ' . $recibo->getNumeroRecibo();
            }
            if ($recibo->getEstaPagado()) {
                $this->logger->info('Already paid: ' . $recibo->getNumeroRecibo());
                $errors[] =  'Already paid: ' . $recibo->getNumeroRecibo();
                $allErrors[] = 'Already paid: ' . $recibo->getNumeroRecibo();
            }

            if (null === $payment) {
                $this->logger->info('No payment to update status');
                $errors[] =  'No payment to update status';
                $allErrors[] =  'No payment to update status';
            }
            if (!$payment->isPaymentSuccessfull()) {
                $this->logger->info('Payment not successfull');
                $errors[] =  'Payment not successfull';
                $allErrors[] =  'Payment not successfull';
            }
            if (count($errors) === 0) {
                $this->gts->paidWithCreditCard($recibo->getNumeroRecibo(), $recibo->getFraccion(), $recibo->getImporteTotal(), $payment->getTimestamp(), $payment->getRegisteredPaymentId(), $index);
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

    #[Route(path: '/receipts/testIBAN', name: 'receipt_testIBAN', methods: ['GET'])]
    public function testIBAN(Request $request, IsValidIBANValidator $validator)
    {
        $iban = $request->get('iban');
        $valid = $validator->validateIBAN($iban);

        return new JsonResponse([
            'valid' => $valid,
        ]);
    }
}
