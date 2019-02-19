<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use http\Env\Request;
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

    /**
     * @Route("/show/users", name="show_users" )
     */
    public function showUsers()
    {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();
        return $this->render("admin/users.html.twig", [
            'controller_name' => "DefaultController",
            "session" => $_SESSION,
            'users' => $users

        ]);
    }

    /**
     * @Route("/change-role/{id}", name="changeRole")
     */
    public function changeRole($id)
    {
        $role = [];
        $role[] = $_POST['role'];
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);
        $user->setRoles($role);
        $entityManager->flush();

        return $this->render("article/home.html.twig", [
            'controller_name' => "DefaultController",
            "session" => $_SESSION
        ]);
    }

    /**
     * @Route("/delete/user/{id}", name="delete_user")
     */
    public function deleteUser($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);
        $entityManager->flush();
        $entityManager->remove($user);
        $entityManager->flush();
        return $this->redirectToRoute("show_users");
    }


}
