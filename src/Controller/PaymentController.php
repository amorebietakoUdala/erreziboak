<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Controller\BaseController;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\PaymentTypeForm;
use App\Repository\PaymentRepository;

#[Route(path: '/{_locale}', requirements: ['_locale' => 'es|eu|en'])]
class PaymentController extends BaseController
{

    public function __construct(private readonly PaymentRepository $paymentRepo)
    {
    }

    #[Route(path: '/admin/payments', name: 'admin_payments_index', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function listPayments(Request $request, LoggerInterface $logger)
    {
        $logger->debug('-->listPayments: Start');
        $this->loadQueryParameters($request);
        $criteria = $request->query->all();
        $criteria = $this->createDateTimeObjects($criteria);
        $criteria = $this->removePaginationParameters($criteria);
        $form = $this->createForm(PaymentTypeForm::class, $criteria, [
            'search' => true,
            'readonly' => false,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $results = $this->paymentRepo->findPaymentsBy($data);
            $criteria = $this->removeBlanks($data);
            $criteria = $this->formatCriteria($criteria);
            $this->setPage(1);
            return $this->render('payment/index.html.twig', [
                'form' => $form,
                'payments' => $results,
                'search' => true,
                'readonly' => false,
                'filters' => $criteria,            
            ]);
        }
        $logger->debug('<--listPayments: End OK');
        $results = $this->paymentRepo->findPaymentsBy($criteria);
        return $this->render('payment/index.html.twig', [
            'form' => $form,
            'payments' => $results,
            'search' => true,
            'readonly' => false,
            'filters' => $this->formatCriteria($criteria),
        ]);
    }

    #[Route(path: '/admin/payment/{id}', name: 'admin_show_payment', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Request $request, Payment $payment, LoggerInterface $logger)
    {
        $logger->debug('-->show: Start');
        $this->loadQueryParameters($request);
        $logger->debug('Payment number: '.$payment->getId());
        $form = $this->createForm(PaymentTypeForm::class, $payment->toArray(), [
            'search' => false,
            'readonly' => true,
        ]);

        return $this->render('payment/show.html.twig', [
            'form' => $form,
            'payment' => $payment,
            'readonly' => true,
            'search' => false,
        ]);
    }
}
