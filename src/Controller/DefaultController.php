<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Subscriber;
use App\Entity\User;
use App\Repository\UserRepository;
use http\Message\Body;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Entity\File;

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
            "posts" => $posts,
            "this" => $this
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

        return $this->redirectToRoute("home");
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
    public function submitPost(\Swift_Mailer $mailer, Request $request)
    {
        $data = $_POST;

        $username = $this->getUser()->getUsername();
        $post = new Post($data, $username);

        $entityManager = $this->getDoctrine()->getManager();

//        /** @var File $file */
//        $file = $request->files->get()


        $entityManager->persist($post);
        $entityManager->flush();

        $subject = "New Post On News";


        $subscribers = $entityManager->getRepository(Subscriber::class)->findAll();

        foreach ($subscribers as $subscriber ){
            $body = $this->render("mail/new-post.html.twig",[
                "subscriber"=>$subscriber
            ]);
            $this->sendEmail($subject, $subscriber->getEmail(),$body, $mailer);
        }

        return $this->redirectToRoute("home");
    }

    public function generateUniqueFileName()
    {
        return md5(uniqid());
    }

    /**
     * @Route("/edit/post/{id}", name = "edit_post")
     */
    public function editPost($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $post = $entityManager->getRepository(Post::class)->find($id);
        return $this->render("editor/edit-post.html.twig", [
            "data" => $post
        ]);
    }

    /**
     *
     * @Route("/submit/edited-post/{id}", name="submit-edited-post")
     */

    public function submitEditedPost($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $data = $entityManager->getRepository(Post::class)->find($id);
        $submition = $_POST;
        $data->setTitle($submition["title"]);
        $data->setSubtitle($submition['subtitle']);
        $data->setBody($submition['body']);
        if ($submition['image']) {
            $data->setImage($submition['image']);
        }
        $entityManager->persist($data);
        $entityManager->flush();
        return $this->redirectToRoute("home");
    }

    /**
     * @Route("/delete/post/{id}", name="delete-post")
     */
    public function deletePost($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $post = $entityManager->getRepository(Post::class)->find($id);
        $entityManager->remove($post);
        $entityManager->flush();
        return $this->redirectToRoute("home");
    }

    /**
     * @Route("/add/subscriber" , name="add-subscriber")
     */
    public function addSubscriber(\Swift_Mailer $mailer)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $subscriber = new Subscriber($_POST);
        if ($entityManager->getRepository(Subscriber::class)->findOneBy(
            ["email" => $subscriber->getEmail()]
        )) {
            return $this->render("article/error.html.twig",[
                "error" => "You already are our subscriber :0 thanks a lot"
            ]);
        }

        $entityManager->persist($subscriber);
        $entityManager->flush();
        $subject = "Welcome";
        $body = $this->render("mail/registered.html.twig", [
            "subscriber" => $subscriber
        ]);
        $this->sendEmail($subject, $subscriber->getEmail(),$body, $mailer);

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/show/subscribers", name="show-subscribers")
     */
    public function showSubscribers()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $subscribers = $entityManager->getRepository(Subscriber::class)->findAll();
        return $this->render("admin/subscribers.html.twig", [
            "subscribers" => $subscribers
        ]);
    }

    /**
     * @Route("/delete/subscriber/{id}", name="remove-subscriber")
     */
    public function removeSubscriber($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $subscriber = $entityManager->getRepository(Subscriber::class)->find($id);
        $entityManager->remove($subscriber);
        $entityManager->flush();

        return $this->redirectToRoute("show-subscribers");
    }

    public function sendEmail($subject,  $address,  $body, \Swift_Mailer $mailer)
    {
        $message = (new \Swift_Message($subject))
            ->setFrom('farnnn.ff@gmail.com')
            ->setTo($address)
            ->setBody(
                $body,
                "text/html"
            );
        $mailer->send($message);
        return true;
    }
}
