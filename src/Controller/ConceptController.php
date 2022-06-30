<?php

namespace App\Controller;

use App\Entity\Concept;
use App\Form\ConceptTypeForm;
use App\Service\GTWINIntegrationService;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @Route("/concept/new", name="admin_concept_new", methods={"GET","POST"})
     */
    public function newAction(Request $request, LoggerInterface $logger, GTWINIntegrationService $gts, EntityManagerInterface $em)
    {
        $logger->debug('-->newAction: Start');
        $form = $this->createForm(ConceptTypeForm::class, new Concept(), [
            'readonly' => false,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Concept $concept */
            $concept = $form->getData();
            $em->persist($concept);
            $em->flush();
            $this->addFlash('success', 'message.concept_created');

            return $this->redirectToRoute('admin_concept_list');
        }
        $logger->debug('<--newAction: End OK');

        return $this->render('concept/new.html.twig', [
            'form' => $form->createView(),
            'readonly' => false,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/concept", name="admin_concept_list", methods={"GET"})
     */
    public function listAction(Request $request, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $concepts = $em->getRepository(Concept::class)->findAll();

        return $this->render('concept/list.html.twig', [
            'concepts' => $concepts,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/concept/{id}", name="admin_concept_show", methods={"GET"})
     */
    public function showAction(Concept $id, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('-->showAction: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $form = $this->createForm(ConceptTypeForm::class, $id, [
            'readonly' => true,
        ]);
        $logger->debug('<--showAction: End OK');

        return $this->render('concept/show.html.twig', [
            'form' => $form->createView(),
            'readonly' => true,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/concept/{id}/edit", name="admin_concept_edit", methods={"GET","POST"})
     */
    public function editAction(Request $request, Concept $id, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('-->ConceptEditAction: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $form = $this->createForm(ConceptTypeForm::class, $id, [
            'readonly' => false,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $concept = $form->getData();
            $em->persist($concept);
            $em->flush();
            $this->addFlash('success', 'message.concept_saved');
        }
        $logger->debug('<--ConceptEditAction: End OK');

        return $this->render('concept/edit.html.twig', [
            'form' => $form->createView(),
            'readonly' => false,
            'new' => false,
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/concept/{id}/delete", name="admin_concept_delete", methods={"GET"})
     */
    public function deleteAction(Concept $id, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('-->deleteAction: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $em->remove($id);
        $em->flush();
        $this->addFlash('success', 'El concepto se ha eliminado correctamente.');
        $logger->debug('<--deleteAction: End OK');

        return $this->redirectToRoute('admin_concept_list');
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/concept/tiposIngreso/select", name="admin_tipos_ingreso_select", methods={"GET"})
     */
    public function getTiposIngresoInstitucion(Request $request, GTWINIntegrationService $gts, SerializerInterface $serializer)
    {
        $tiposIngreso = $gts->findTipoIngresoInstitucion($request->get('entity'));

        return new JsonResponse($serializer->serialize($tiposIngreso, 'json'), 200);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/concept/tarifas/select", name="admin_tarifas_select", methods={"GET"})
     */
    public function getTarifasTipoIngreso(Request $request, GTWINIntegrationService $gts, SerializerInterface $serializer)
    {
        $tarifas = $gts->findTarifasTipoIngreso($request->get('suffix'));

        return new JsonResponse($serializer->serialize($tarifas, 'json'), 200);
    }
}
