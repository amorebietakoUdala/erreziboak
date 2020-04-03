<?php

namespace App\Controller;

use App\Entity\ExamInscription;
use App\Entity\GTWIN\ReciboGTWIN;
use App\Form\ExamInscriptionTypeForm;
use App\Service\GTWINIntegrationService;
use DateTime;
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
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(ExamInscriptionTypeForm::class, new ExamInscription(), [
        'readonly' => false,
        'locale' => $request->getLocale(),
    ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /* @var $exam ExamInscription */
            $exam = $form->getData();
            if (Validaciones::valida_nif_cif_nie($exam->getDni()) <= 0) {
                $this->addFlash('error', 'EL DNI no es correcto');

                return $this->render('/exam/new.html.twig', [
                'form' => $form->createView(),
                'readonly' => false,
            ]);
            }
            $concept = $exam->getCategory()->getConcept();
            $receipt = $this->createReceiptFromInscriptionData($exam);
            $receipt->setImporte($concept->getUnitaryPrice());
            $receipt->setConcepto($concept->getName());
            $receipt->setEntidad($concept->getEntity());
            $receipt->setSufijo($concept->getSuffix());
            $receipt->setConceptoRenta($concept->getAccountingConcept());
            $date = new DateTime();
            $receipt->setUltimoDiaPago($date);
            try {
                $logger->debug('-->newExamAction: Create GTWIN Receipt');
                $em->persist($receipt);
                $em->flush();
                $reciboGTWIN = $gts->createReciboOpt($receipt);
                $logger->debug('-->newExamAction: GTWIN Receipt Created successfully');
                $receipt->setNumeroReferenciaGTWIN($reciboGTWIN->getNumeroRecibo());
                $logger->debug('-->newExamAction: Numero ReferenciaGTWIN: '.$reciboGTWIN->getId());
                $em->persist($receipt);
                $em->flush();
                $logger->debug('-->newExamAction: End forwarded to payForwardedReceiptAction');

                return $this->forward('App\Controller\ReceiptController::payForwardedReceiptAction', [
                    'receipt' => $receipt,
                ]);
            } catch (Exception $e) {
                $logger->debug('-->newExamAction: Error: '.$e->getMessage());
                $this->addFlash('error', $e->getMessage());
                $em->remove($receipt);
                $em->flush();
            }
        }
        $logger->debug('-->newExamAction: End');

        return $this->render('/exam/new.html.twig', [
        'form' => $form->createView(),
        'readonly' => false,
    ]);
    }

    private function createReceiptFromInscriptionData(ExamInscription $exam)
    {
        $recibo = new ReciboGTWIN();
        $recibo->setDni(strtoupper($exam->getDni()));
        $recibo->setNombre(strtoupper($exam->getNombre()));
        $recibo->setApellido1(strtoupper($exam->getApellido1()));
        $recibo->setApellido2(strtoupper($exam->getApellido2()));
        $recibo->setEmail($exam->getEmail());
        $recibo->setTelefono($exam->getTelefono());

        return $recibo;
    }
}
