<?php

namespace App\Controller;

use App\Entity\Concept;
use App\Form\ConceptTypeForm;
use App\Controller\BaseController;
use App\Service\GTWINIntegrationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/{_locale}', requirements: ['_locale' => 'es|eu|en'])]
class ConceptController extends BaseController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/concept/new', name: 'admin_concept_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('-->new: Start');
        $this->loadQueryParameters($request);
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

            return $this->redirectToRoute('admin_concept_index');
        }
        $logger->debug('<--new: End OK');

        return $this->render('concept/edit.html.twig', [
            'form' => $form,
            'readonly' => false,
            'new' => true,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/concept', name: 'admin_concept_index', methods: ['GET'])]
    public function list(Request $request, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $this->loadQueryParameters($request);
        $concepts = $em->getRepository(Concept::class)->findAll();

        return $this->render('concept/index.html.twig', [
            'concepts' => $concepts,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/concept/{id}', name: 'admin_concept_show', methods: ['GET'])]
    public function show(Request $request, Concept $id, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('-->show: Start');
        $this->loadQueryParameters($request);
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $form = $this->createForm(ConceptTypeForm::class, $id, [
            'readonly' => true,
        ]);
        $logger->debug('<--show: End OK');

        return $this->render('concept/edit.html.twig', [
            'form' => $form,
            'readonly' => true,
            'new' => false,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/concept/{id}/edit', name: 'admin_concept_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Concept $id, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('-->ConceptEdit: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $this->loadQueryParameters($request);
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
        $logger->debug('<--ConceptEdit: End OK');

        return $this->render('concept/edit.html.twig', [
            'form' => $form,
            'readonly' => false,
            'new' => false,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/concept/{id}/delete', name: 'admin_concept_delete', methods: ['GET'])]
    public function delete(Request $request, Concept $id, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('-->delete: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $this->loadQueryParameters($request);
        $em->remove($id);
        $em->flush();
        $this->addFlash('success', 'El concepto se ha eliminado correctamente.');
        $logger->debug('<--delete: End OK');

        return $this->redirectToRoute('admin_concept_index');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/concept/tiposIngreso/select', name: 'admin_tipos_ingreso_select', methods: ['GET'])]
    public function getTiposIngresoInstitucion(Request $request, GTWINIntegrationService $gts, SerializerInterface $serializer)
    {
        $tiposIngreso = $gts->findTipoIngresoInstitucion($request->get('entity'));

        return new JsonResponse($serializer->serialize($tiposIngreso, 'json',['groups' => ['show']]), Response::HTTP_OK, [], true);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/concept/tarifas/select', name: 'admin_tarifas_select', methods: ['GET'])]
    public function getTarifasTipoIngreso(Request $request, GTWINIntegrationService $gts, SerializerInterface $serializer)
    {
        $tarifas = $gts->findTarifasTipoIngreso($request->get('suffix'));

        return new JsonResponse($serializer->serialize($tarifas, 'json',['groups' => ['show']]), Response::HTTP_OK, [], true);
    }
}
