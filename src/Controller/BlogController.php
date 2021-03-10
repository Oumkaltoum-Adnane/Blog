<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/",name="home")
     */
    public function home():response
    {
        return $this->render('blog/home.html.twig',[
            'title' => "Bienvenue sur le blog Symfomy",
            'age' => 25
        ]);
    }

    /**
     * @Route("/blog", name="blog")
     */
    public function index(): Response
    {
        return $this->render('blog/index.html.twig', [
            'title' => 'Listes des articles',
        ]);
    }

    /**
     * @Route("/blog/12", name="blog_show")
     */
    public function show(): Response
    {
        return $this->render('blog/show.html.twig', [
        
        ]);
    }
}
