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
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/{_locale}/exam', requirements: ['_locale' => 'es|eu'])]
class ExamController extends AbstractController
{
    #[Route(path: '/new', name: 'exam_new')]
    public function newExam(Request $request, LoggerInterface $logger, GTWINIntegrationService $gts)
    {
        $logger->debug('-->newExam: Start');
        $form = $this->createForm(ExamInscriptionTypeForm::class, new ExamInscription(), [
            'readonly' => false,
            'locale' => $request->getLocale(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $exam ExamInscription */
            $exam = $form->getData();
            try {
                $logger->debug('-->newExam: Create GTWIN Receipt');
                $recibo = $gts->createReciboOpt($exam);
                $recibo->setEmail($exam->getEmail());
                $logger->debug('-->newExam: GTWIN Receipt Created successfully');
                $logger->debug('-->newExam: End forwarded to payForwardedReceipt');

                return $this->forward('App\Controller\ReceiptController::payForwardedReceipt', [
                    'receipt' => $recibo,
                ]);
            } catch (Exception $e) {
                $logger->debug('-->newExam: Error: ' . $e->getMessage());
                $this->addFlash('error', $e->getMessage());
            }
        }
        $logger->debug('-->newExam: End');

        return $this->render('exam/new.html.twig', [
            'form' => $form,
            'readonly' => false,
        ]);
    }

    private function createReceiptFromInscriptionData(ExamInscription $exam)
    {
        $recibo = new Recibo();
        $recibo->setDni(strtoupper((string) $exam->getDni()));
        $recibo->setNombreCompleto(strtoupper($exam->getNombre() . '*' . $exam->getApellido1() . '*' . $exam->getApellido2()));
        $recibo->setEmail($exam->getEmail());
        //        $recibo->setTelefono($exam->getTelefono());

        return $recibo;
    }
}
