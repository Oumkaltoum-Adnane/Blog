<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleFormType;
use App\Form\CommentFormType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * il est possible de défenir plusieurs route qui execute la même methode
     * 
     * @Route("/blog/new",name="blog_create")
     * @Route("/blog/{id}/edit",name="blog_edit")
     */
        public function create(Article $articleCreate = null , Request $request,EntityManagerInterface $manager):Response
        {
        
            dump($request);

           // if($request->request->count() > 0)
            //{
                // Pour insérer dans la table Article, nous devons instancier un objet issu de la classe entité Article, qui est lié à la table SQL Article
               
               // $articleCreate = new Article;

                //on rensigne tout les setteurs de l'objet avec en arguments les données du formulaire ()
              //  $articleCreate->setTitle($request->request->get('title'))
                            //   ->setContent($request->request->get('content')) 
                            //   ->setImage($request->request->get('image'))
                            //   ->setCreatedAt(new \DateTime);

               // dump($articleCreate);  // on observe que l'objet entité Article $articleCreate, les propriétés contiennent bien les données du formaulaire
                
                // On fait appel au manager afin de pouvoir executer une insertion en BDD
               // $manager->persist($articleCreate);// on prépare et garde en mémoire l'insertion
                //$manager->flush();// on execute l'insertion

                // Après l'insertion, on redirige l'internaute vers le détail de l'article qui vient d'être inséré en BDD
                // Cela correspond à la route 'blog_show', mais c'est une route paramétrée qui attend un ID dans l'URL
                // En 2ème argument de redirectToRoute, nous transmettons l'ID de l'article qui vient d'être inséré en BDD


               // return$this->redirectToRoute('blog_show',[
                     //  'id' =>$articleCreate->getId()
               // ]);
           // }
           // si la variable $articleCreate n'est pas,si elle ne contient aucun artcile de la BDD, cela veut dire nous avons envoyé la route '/blog/new',c'est une insertion,on entre dans le IF et on crée une nouvelle instance de l'entité Article, création d'un nouvel article
             // Si la variable $articleCreate contient un article de la BDD, cela veut dire que nous avons envoyé la route '/blog/id/edit', c'est une modifiction d'article, on entre pas dans le IF
           if(!$articleCreate)
           {
            $articleCreate = new Article; // setTitle($_POST['title'])
           }
      

      ;
            //createFormBuilder()methode de symfony qui permet de générer un formulaire permettant de remplir un entité $articleCreate
            // $form = $this->createFormBuilder($articleCreate)
            //              ->add('title')// add() méthode permettant de générer des champs de formulaire

            //              ->add('content')
            //              ->add('image')

            //              ->getForm();//permet d'afficher le rendu final
            
            // Nous avons créer une classe qui permet de générer le formulaire d'ajout d'article, il faut dans le controller importer cette classe ArticleFormType et relier le formulaire à notre entité Article $articleCreate
            $form= $this->createForm(ArticleFormType::class,$articleCreate);
            // on pioche dans l'objet du formulaire la méthode handelrequest()qui permet de récupérer chque donnes saisie dans le formulaire($request) et de les bindé,de les transmettre directement dans les bons setteurs de mon entité $articleCreate
            //$_POST['title] -->setTitle($_POST['title])             
            $form->handleRequest($request);             

             // dump($articleCreate);
            
              if($form->isSubmitted() && $form->isValid())
              {
                  // on appel le setteur de la date,puisque nous n'avons pas de champs date dans le formulaire
                  if(!$articleCreate->getId())
                  {
                    $articleCreate->setCreatedAt(new \DateTime);
                  }
                 

                  $manager->persist($articleCreate);
                  $manager->flush();//on execute véritablement la request d'insertion en BDD

                  return $this->redirectToRoute("blog_show",[
                      "id" => $articleCreate->getId()

                  ]);
              }

            return $this->render('blog/create.html.twig',[
               'formArticle' => $form->createView(),// on transemt su rle template le formulaire,creatView() retourne un petit objet qui represente l'affichage du formulaire,on le récupére sur le templete create.html.twig
               'editMode'=>$articleCreate->getId()
            ]);
        }

    /**
     * on définit ici une route paramétrée,une route définit avec un ID qui va receptionnée un ID d'un artcile dans l'URL
     * /blog/9 --. {id} --> $id = 9
     * 
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article,Request $request,EntityManagerInterface $manager ): Response
    {
       // dump($article);

       $comment = new Comment; 

       $formComment= $this->createForm(CommentFormType::class,$comment);
       dump($request);

       $formComment->handleRequest($request);   
       dump($comment);

       if($formComment->isSubmitted() && $formComment->isValid())
       {
           $comment->setCreatedAt(new \DateTime)
                   ->setArticle($article);// on relie le commentaire a l'article
                   //la méthode set Article() attend en argument un objet issu de la class Article,c'est a dire un article de la BDD

                 $manager->persist($comment);
                 $manager->flush();

                $this->addFlash('success',"Le commentaire a été bien posté ! ");
            
           return $this->redirectToRoute('blog_show',[
               'id' => $article->getId()
           ]);      

       }
  


        return $this->render('blog/show.html.twig',[
           'articleTwig' =>$article ,
           // on envoi sur le template les données selectionnées en BDD, c'est à dire les informations d'1 article en fonction l'id transmit dans l'URL
          // $repoArticle est un objet issu de la classe ArticleRepository
           'formComment' => $formComment->createView()  
        ]);
    }
    /*
        En fonction de la route paramétrée {id} et de l'injection de dépendance $article, Symfony voit que l'on besoin d'un article de la BDD par rapport à l'ID transmit dans l'URL, il est donc capable de recupérer l'ID et de selectionner en BDD l'article correspondant et de l'envoyer directement en argument de la méthode show(Article $article)
        Tout ça grace à des ParamConverter qui appel des convertisseurs pour convertir les paramètres de l'objet. Ces objets sont stockés en tant qu'attribut de requete et peuvent donc être injectés an tant qu'argument de méthode de controller

     */
}
