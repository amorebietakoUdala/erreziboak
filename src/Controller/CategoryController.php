<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use App\Entity\Category;
use App\Form\CategoryTypeForm;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/{_locale}/admin", requirements={
 *	    "_locale": "es|eu"
 * })
 */
class CategoryController extends BaseController
{
    /**
     * @Route("/category/new", name="admin_category_new", methods={"GET","POST"})
     */
    public function newAction(Request $request, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('-->newAction: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $this->loadQueryParameters($request);
        $user = $this->getUser();
        $form = $this->createForm(CategoryTypeForm::class, new Category(), [
            'readonly' => false,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', 'La nueva categoría se ha guardado correctamente.');

            return $this->redirectToRoute('admin_category_index');
        }
        $logger->debug('<--newAction: End OK');

        return $this->render('category/edit.html.twig', [
            'form' => $form->createView(),
            'readonly' => false,
            'new' => true,
        ]);
    }

    /**
     * @Route("/category", name="admin_category_index", methods={"GET"})
     */
    public function listAction(Request $request, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $this->loadQueryParameters($request);
        $categorys = $em->getRepository(Category::class)->findAll();

        return $this->render('category/index.html.twig', [
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
        $this->loadQueryParameters($request);
        $form = $this->createForm(CategoryTypeForm::class, $id, [
            'readonly' => true,
        ]);
        $logger->debug('<--showAction: End OK');

        return $this->render('category/edit.html.twig', [
            'form' => $form->createView(),
            'readonly' => true,
            'new' => false,
        ]);
    }

    /**
     * @Route("/category/{id}/edit", name="admin_category_edit", methods={"GET","POST"})
     */
    public function editAction(Request $request, Category $id, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('-->CategoryEditAction: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $this->loadQueryParameters($request);
        $form = $this->createForm(CategoryTypeForm::class, $id, [
            'readonly' => false,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $em->persist($category);
            $em->flush();
            $this->addFlash('success', 'message.category_saved');
        }
        $logger->debug('<--CategoryEditAction: End OK');

        return $this->render('category/edit.html.twig', [
            'form' => $form->createView(),
            'readonly' => false,
            'new' => false,
        ]);
    }

    /**
     * @Route("/category/{id}/delete", name="admin_category_delete", methods={"GET"})
     */
    public function deleteAction(Request $request, Category $id, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('-->deleteAction: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $this->loadQueryParameters($request);        
        $em->remove($id);
        $em->flush();
        $this->addFlash('success', 'La categoría se ha eliminado correctamente.');
        $logger->debug('<--deleteAction: End OK');

        return $this->redirectToRoute('admin_category_index');
    }
}
