<?php

namespace App\Controller;

use App\Entity\Concept;
use App\Form\ConceptTypeForm;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{_locale}", requirements={
 *	    "_locale": "es|eu|en"
 * })
 */
class ConceptController extends AbstractController
{
    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/concept/new", name="concept_new", methods={"GET","POST"})
     */
    public function newAction(Request $request, LoggerInterface $logger)
    {
        $logger->debug('-->newAction: Start');
        $em = $this->getDoctrine()->getManager();
        $conceptoContable = $this->getDoctrine()->getManager('oracle')
            ->getRepository(\App\Entity\GTWIN\ConceptoContable::class)->findOneBy(['codigo' => 107]);

        $conceptosRentas = $conceptoContable->getConceptosRentas();
        foreach ($conceptosRentas as $conceptoRenta) {
            dump($conceptoRenta->getConceptoEconomico());
        }
        die;
        $form = $this->createForm(ConceptTypeForm::class, new Concept(), [
        'readonly' => false,
    ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $concept = $form->getData();
            $em->persist($concept);
            $em->flush();
            $this->addFlash('success', 'message.concept_created');

            return $this->redirectToRoute('concept_list');
        }
        $logger->debug('<--newAction: End OK');

        return $this->render('/concept/new.html.twig', [
        'form' => $form->createView(),
        'readonly' => false,
    ]);
    }

    /**
     * @Route("/concept", name="concept_list", methods={"GET"})
     */
    public function listAction(Request $request, LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        $concepts = $em->getRepository(Concept::class)->findAll();

        return $this->render('/concept/list.html.twig', [
        'concepts' => $concepts,
        ]);
    }

    /**
     * @Route("/concept/{id}", name="concept_show", methods={"GET"})
     */
    public function showAction(Concept $id, LoggerInterface $logger)
    {
        $logger->debug('-->showAction: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $form = $this->createForm(ConceptTypeForm::class, $id, [
        'readonly' => true,
    ]);
        $logger->debug('<--showAction: End OK');

        return $this->render('/concept/show.html.twig', [
        'form' => $form->createView(),
        'readonly' => true,
    ]);
    }

    /**
     * @Route("/concept/{id}/edit", name="concept_edit", methods={"GET","POST"})
     */
    public function editAction(Request $request, Concept $id, LoggerInterface $logger)
    {
        $logger->debug('-->ConceptEditAction: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $form = $this->createForm(ConceptTypeForm::class, $id, [
        'readonly' => false,
    ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $concept = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($concept);
            $em->flush();
            $this->addFlash('success', 'message.concept_saved');
        }
        $logger->debug('<--ConceptEditAction: End OK');

        return $this->render('/concept/edit.html.twig', [
        'form' => $form->createView(),
        'readonly' => false,
        'new' => false,
    ]);
    }

    /**
     * @Route("/concept/{id}/delete", name="concept_delete", methods={"GET"})
     */
    public function deleteAction(Concept $id, LoggerInterface $logger)
    {
        $logger->debug('-->deleteAction: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        $em->remove($id);
        $em->flush();
        $this->addFlash('success', 'El concepto se ha eliminado correctamente.');
        $logger->debug('<--deleteAction: End OK');

        return $this->redirectToRoute('concept_list');
    }
}
