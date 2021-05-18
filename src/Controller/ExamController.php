<?php

namespace App\Controller;

use App\Entity\ExamInscription;
use App\Entity\GTWIN\Recibo;
use App\Form\ExamInscriptionTypeForm;
use App\Service\GTWINIntegrationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{_locale}/exam", requirements={
 *	    "_locale": "es|eu"
 * })
 */
class ExamController extends AbstractController
{
    /**
     * @Route("/new", name="exam_new")
     */
    public function newExamAction(Request $request, LoggerInterface $logger, GTWINIntegrationService $gts)
    {
        $logger->debug('-->newExamAction: Start');
        $form = $this->createForm(ExamInscriptionTypeForm::class, new ExamInscription(), [
            'readonly' => false,
            'locale' => $request->getLocale(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $exam ExamInscription */
            $exam = $form->getData();
            try {
                $logger->debug('-->newExamAction: Create GTWIN Receipt');
                $recibo = $gts->createReciboOpt($exam);
                $recibo->setEmail($exam->getEmail());
                $logger->debug('-->newExamAction: GTWIN Receipt Created successfully');
                $logger->debug('-->newExamAction: End forwarded to payForwardedReceiptAction');

                return $this->forward('App\Controller\ReceiptController::payForwardedReceiptAction', [
                    'receipt' => $recibo,
                ]);
            } catch (Exception $e) {
                $logger->debug('-->newExamAction: Error: ' . $e->getMessage());
                $this->addFlash('error', $e->getMessage());
            }
        }
        $logger->debug('-->newExamAction: End');

        return $this->render('exam/new.html.twig', [
            'form' => $form->createView(),
            'readonly' => false,
        ]);
    }

    private function createReceiptFromInscriptionData(ExamInscription $exam)
    {
        $recibo = new Recibo();
        $recibo->setDni(strtoupper($exam->getDni()));
        $recibo->setNombreCompleto(strtoupper($exam->getNombre() . '*' . $exam->getApellido1() . '*' . $exam->getApellido2()));
        $recibo->setEmail($exam->getEmail());
        //        $recibo->setTelefono($exam->getTelefono());

        return $recibo;
    }
}
