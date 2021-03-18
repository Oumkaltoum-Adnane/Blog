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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

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
        public function create(Article $articleCreate = null , Request $request,EntityManagerInterface $manager ,SluggerInterface $slugger):Response
        {
        
            dump($request);

           if(!$articleCreate)
           {
            $articleCreate = new Article; // setTitle($_POST['title'])
           }
      
            $form= $this->createForm(ArticleFormType::class,$articleCreate);
                        
            $form->handleRequest($request);             

             // dump($articleCreate);
            
              if($form->isSubmitted() && $form->isValid())
              {
                  /** @var UploadedFile $imageFile */
                  $imageFile = $form->get('image')->getData();
                  dump($imageFile);

                  if($imageFile)
                  {
                      $originalFilename = pathinfo($imageFile->getClientOriginalName(),PATHINFO_FILENAME);
                      dump($originalFilename);// permet de récuperer le nom du fichier

                      $safeFilename= $slugger->slug($originalFilename);
                      dump($safeFilename);
                      
                      //on obtient le nom du fichier définitif concaténé avec un identifiant unique et l'extension du fichier
                      $newFilename=$safeFilename . "-" . uniqid() .'.' . $imageFile->guessExtension();

                      try// on tente de copier l'image dans le bon dossier sur le serveur
                      {
                        $imageFile->move(
                            $this->getParameter('image_directory'),
                            $newFilename
                        );
                      }
                      catch(FileException $e)
                      {

                      }
                       //on envoi l'image definitive dans le bon setter de l'objet afin que l'image soit stockée en BDD
                      $articleCreate->setImage($newFilename);
                  }


                  if(!$articleCreate->getId())
                  {
                    $articleCreate->setCreatedAt(new \DateTime);
                  }
                  $manager->persist($articleCreate);
                  $manager->flush();

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
