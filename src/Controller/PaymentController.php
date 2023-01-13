<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Controller\BaseController;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\PaymentTypeForm;
use App\Repository\PaymentRepository;

/**
 * @Route("/{_locale}", requirements={
 *	    "_locale": "es|eu|en"
 * })
 */
class PaymentController extends BaseController
{

    private PaymentRepository $paymentRepo;

    public function __construct(PaymentRepository $paymentRepo)
    {
        $this->paymentRepo = $paymentRepo;    
    }

    /**
     * @Route("/admin/payments", name="admin_payments_index", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function listPaymentsAction(Request $request, LoggerInterface $logger)
    {
        $logger->debug('-->listPaymentsAction: Start');
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
                'form' => $form->createView(),
                'payments' => $results,
                'search' => true,
                'readonly' => false,
                'filters' => $criteria,            
            ]);
        }
        $logger->debug('<--listPaymentsAction: End OK');
        $results = $this->paymentRepo->findPaymentsBy($criteria);
        return $this->render('payment/index.html.twig', [
            'form' => $form->createView(),
            'payments' => $results,
            'search' => true,
            'readonly' => false,
            'filters' => $this->formatCriteria($criteria),
        ]);
    }

    /**
     * @Route("/admin/payment/{id}", name="admin_show_payment", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function showAction(Request $request, Payment $payment, LoggerInterface $logger)
    {
        $logger->debug('-->showAction: Start');
        $this->loadQueryParameters($request);
        $logger->debug('Payment number: '.$payment->getId());
        $form = $this->createForm(PaymentTypeForm::class, $payment->toArray(), [
            'search' => false,
            'readonly' => true,
        ]);

        return $this->render('payment/show.html.twig', [
            'form' => $form->createView(),
            'payment' => $payment,
            'readonly' => true,
            'search' => false,
        ]);
    }
}
