<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function index(ArticleRepository $repo): Response
    {
        /*
            Pour selectionner des données dans une table SQL, nous devons absolument avoir accès à la classe Repository de l'entité correspondante 
            Un Repository est une classe permettant uniquement d'executer des requetes de selection en BDD (SELECT)
            Nous devons donc accéder au repository de l'netité Article au sein de notre controller  

            On appel l'ORM doctrine (getDoctrine()), puis on importe le repositoritory de la classe Article grace à la méthode getRepository()
            $repo est un objet issu de la classe ArticleRepository
            cet objet contient des méthodes permettant d'executer des requetes de selections
            findAll() : méthode issue de la classe ArticleRepository permettant de selectionner l'ensemble de la table SQL 'Article'
        */
        //                            import le contenu d'une entité
        // $repo = $this->getDoctrine()->getRepository(Article::class);

    //outil de debugage de symfony 
        dump($repo);

        $articles = $repo->findAll();// equivalent de SELECT * from artcile +fetchAll


      //outil de debugage de symfony 
        dump($articles);

        return $this->render('blog/index.html.twig', [
            'title' => 'Musique elle accompagne notre vie',
            'articles'=>$articles // on envoie sur le template, les articles selectionnés en BDD afin de pouvoir les afficher dynamiquement sur le template à l'aide du langage Twig
        ]);
    }


    /**
     * Méthode permettant d'insérer et de modifier un article
     * 
     * @Route("/blog/new",name="blog_create")
     */
        public function create(Request $request):Response
        {
            //la class Request de symfony permet de véhiculer les données des super globales PHP ($_POST,$_Files,$_COOKIES,$_SESSION)
            //$request est un objet issu de la classe Request injecté en dependance de la méthode create()

            dump($request);

            return $this->render('blog/create.html.twig');
        }

    /**
     * on définit ici une route paramétrée,une route définit avec un ID qui va receptionnée un ID d'un artcile dans l'URL
     * /blog/9 --. {id} --> $id = 9
     * 
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article): Response
    {  
        //$repoArticle est un objet issu de la class ARTICLESRepository
        // $repoArticle = $this->getDoctrine()->getRepository(Article::class);

       // dump($repoArticle);
       // dump($id); // id=9

        // on transmet a la méthode find() de la classe articleRepository l'id recupéré dans l'url et transmit en argument de la fonction show ($id) | $id = 3
        //find() permet de selectionner en bdd un article par son ID
       // $article = $repoArticle->find($id);

        //dump($article);

        return $this->render('blog/show.html.twig',[
           'articleTwig' =>$article 
           // on envoi sur le template les données selectionnées en BDD, c'est à dire les informations d'1 article en fonction l'id transmit dans l'URL
          // $repoArticle est un objet issu de la classe ArticleRepository
              
        ]);
    }
    /*
        En fonction de la route paramétrée {id} et de l'injection de dépendance $article, Symfony voit que l'on besoin d'un article de la BDD par rapport à l'ID transmit dans l'URL, il est donc capable de recupérer l'ID et de selectionner en BDD l'article correspondant et de l'envoyer directement en argument de la méthode show(Article $article)
        Tout ça grace à des ParamConverter qui appel des convertisseurs pour convertir les paramètres de l'objet. Ces objets sont stockés en tant qu'attribut de requete et peuvent donc être injectés an tant qu'argument de méthode de controller

     */
}
