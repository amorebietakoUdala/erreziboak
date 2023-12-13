<?php

namespace App\Controller;

use App\Controller\BaseController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use App\Entity\Category;
use App\Form\CategoryTypeForm;
use Doctrine\ORM\EntityManagerInterface;

#[Route(path: '/{_locale}/admin', requirements: ['_locale' => 'es|eu'])]
class CategoryController extends BaseController
{
    #[Route(path: '/category/new', name: 'admin_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('-->new: Start');
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
        $logger->debug('<--new: End OK');

        return $this->render('category/edit.html.twig', [
            'form' => $form,
            'readonly' => false,
            'new' => true,
        ]);
    }

    #[Route(path: '/category', name: 'admin_category_index', methods: ['GET'])]
    public function list(Request $request, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $this->loadQueryParameters($request);
        $categorys = $em->getRepository(Category::class)->findAll();

        return $this->render('category/index.html.twig', [
            'categorys' => $categorys,
        ]);
    }

    #[Route(path: '/category/{id}', name: 'admin_category_show', methods: ['GET'])]
    public function show(Request $request, Category $id, LoggerInterface $logger)
    {
        $logger->debug('-->show: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $this->loadQueryParameters($request);
        $form = $this->createForm(CategoryTypeForm::class, $id, [
            'readonly' => true,
        ]);
        $logger->debug('<--show: End OK');

        return $this->render('category/edit.html.twig', [
            'form' => $form,
            'readonly' => true,
            'new' => false,
        ]);
    }

    #[Route(path: '/category/{id}/edit', name: 'admin_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $id, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('-->CategoryEdit: Start');
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
        $logger->debug('<--CategoryEdit: End OK');

        return $this->render('category/edit.html.twig', [
            'form' => $form,
            'readonly' => false,
            'new' => false,
        ]);
    }

    #[Route(path: '/category/{id}/delete', name: 'admin_category_delete', methods: ['GET'])]
    public function delete(Request $request, Category $id, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->debug('-->delete: Start');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $this->loadQueryParameters($request);        
        $em->remove($id);
        $em->flush();
        $this->addFlash('success', 'La categoría se ha eliminado correctamente.');
        $logger->debug('<--delete: End OK');

        return $this->redirectToRoute('admin_category_index');
    }
}
