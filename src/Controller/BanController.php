<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class BanController extends AbstractController
{
    #[Route('/ban', name: 'app_ban')]
    public function ban(Request $req): Response
    {
        $req->getSession()->invalidate();
        $this->container->get('security.token_storage')->setToken(null);
        return $this->render('ban.html.twig');
    }
}
