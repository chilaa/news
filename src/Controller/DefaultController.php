<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('article/home.html.twig', [
            'controller_name' => 'DefaultController',
            'session' => $_SESSION
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logOut()
    {
        return $this->render("article/home.html.twig", [
            'controller_name' => "DefaultController",
            "session" => $_SESSION
        ]);
    }

}
