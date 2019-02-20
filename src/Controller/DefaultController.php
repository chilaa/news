<?php

namespace App\Controller;

use App\Entity\Post;
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
        $entityManager = $this->getDoctrine()->getManager();
        $posts = $entityManager->getRepository(Post::class)->findAll();
        return $this->render("article/home.html.twig", [
            "posts" => $posts
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

    /**
     * @Route("/add/post", name="add_post")
     */
    public function addPost()
    {
        return $this->render("editor/new-post.html.twig");
    }

    /**
     * @Route("/submit/post", name="submit")
     */
    public function submitPost()
    {
        $data = $_POST;
        $username = $this->getUser()->getUsername();
        $post = new Post($data, $username);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->persist($post);
        $entityManager->flush();

        $posts = $entityManager->getRepository(Post::class)->findAll();
        return $this->redirectToRoute("home");
    }
}
