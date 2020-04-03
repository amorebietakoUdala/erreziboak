<?php

namespace App\Controller;

use App\Entity\Payment;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\PaymentTypeForm;

/**
 * @Route("/{_locale}", requirements={
 *	    "_locale": "es|eu|en"
 * })
 */
class PaymentController extends AbstractController
{
    /**
     * @Route("/admin/payments", name="admin_list_payments", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function listPaymentsAction(Request $request, LoggerInterface $logger)
    {
        $logger->debug('-->listPaymentsAction: Start');
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(PaymentTypeForm::class, null, [
            'search' => true,
            'readonly' => false,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $results = $em->getRepository(Payment::class)->findPaymentsBy($data);

            return $this->render('payment/list.html.twig', [
                'form' => $form->createView(),
                'payments' => $results,
                'search' => true,
                'readonly' => false,
            ]);
        }
        $logger->debug('<--listPaymentsAction: End OK');

        return $this->render('payment/list.html.twig', [
            'form' => $form->createView(),
            'search' => true,
            'readonly' => false,
        ]);
    }

    /**
     * @Route("/admin/payment/{id}", name="admin_show_payment", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function showAction(Payment $payment, LoggerInterface $logger)
    {
        $logger->debug('-->showAction: Start');
        $logger->debug('Payment number: '.$payment->getId());
        $form = $this->createForm(PaymentTypeForm::class, $payment->toArray(), [
            'search' => false,
            'readonly' => true,
        ]);

        return    $this->render('payment/show.html.twig', [
            'form' => $form->createView(),
            'payment' => $payment,
            'readonly' => true,
            'search' => false,
        ]);
    }
}
