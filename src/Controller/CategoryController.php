<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use App\Entity\Category;
use App\Form\CategoryTypeForm;

/**
 * @Route("/{_locale}/admin", requirements={
 *	    "_locale": "es|eu"
 * })
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/category/new", name="admin_category_new", methods={"GET","POST"})
     */
    public function newAction(Request $request, LoggerInterface $logger)
    {
        $logger->debug('-->newAction: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(CategoryTypeForm::class, new Category(), [
        'readonly' => false,
    ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', 'La nueva categoría se ha guardado correctamente.');

            return $this->redirectToRoute('admin_category_list');
        }
        $logger->debug('<--newAction: End OK');

        return $this->render('/category/new.html.twig', [
        'form' => $form->createView(),
        'readonly' => false,
    ]);
    }

    /**
     * @Route("/category", name="admin_category_list", methods={"GET"})
     */
    public function listAction(Request $request, LoggerInterface $logger)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        $categorys = $em->getRepository(Category::class)->findAll();

        return $this->render('/category/list.html.twig', [
        'categorys' => $categorys,
        ]);
    }

    /**
     * @Route("/category/{id}", name="admin_category_show", methods={"GET"})
     */
    public function showAction(Request $request, Category $id, LoggerInterface $logger)
    {
        $logger->debug('-->showAction: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $form = $this->createForm(CategoryTypeForm::class, $id, [
        'readonly' => true,
    ]);
        $logger->debug('<--showAction: End OK');

        return $this->render('/category/show.html.twig', [
        'form' => $form->createView(),
        'readonly' => true,
    ]);
    }

    /**
     * @Route("/category/{id}/edit", name="admin_category_edit", methods={"GET","POST"})
     */
    public function editAction(Request $request, Category $id, LoggerInterface $logger)
    {
        $logger->debug('-->CategoryEditAction: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $form = $this->createForm(CategoryTypeForm::class, $id, [
        'readonly' => false,
    ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', 'message.category_saved');
        }
        $logger->debug('<--CategoryEditAction: End OK');

        return $this->render('/category/edit.html.twig', [
        'form' => $form->createView(),
        'readonly' => false,
        'new' => false,
    ]);
    }

    /**
     * @Route("/category/{id}/delete", name="admin_category_delete", methods={"GET"})
     */
    public function deleteAction(Request $request, Category $id, LoggerInterface $logger)
    {
        $logger->debug('-->deleteAction: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $em = $this->getDoctrine()->getManager();
        $em->remove($id);
        $em->flush();
        $this->addFlash('success', 'La categoría se ha eliminado correctamente.');
        $logger->debug('<--deleteAction: End OK');

        return $this->redirectToRoute('admin_category_list');
    }
}
