<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'app_homepage', methods: ['GET'])]
    public function home(Request $request)
    {
        $locale = $request->attributes->get('_locale');
        if (null !== $locale) {
            $request->getSession()->set('_locale', $locale);
        } else {
            $request->setLocale('es');
        }

        return $this->redirectToRoute('receipt_find');
    }
}
