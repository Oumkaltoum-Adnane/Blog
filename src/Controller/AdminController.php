<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use App\Form\ArticleFormType;
use App\Form\CommentFormType;
use App\Form\CategoryFormType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * Méthode permettant d'afficher l'accueil
     * 
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/admin/articles",name="admin_articles")
     * @Route("/admin/{id}/remove", name="admin_remove_article")
     * 
     */
    public function adminArticles(EntityManagerInterface $manager,ArticleRepository $repoArticle,Article $article = null): Response//13
    {
       //le manager permet de manipuler la bdd,on execute la méthode getclassmetadata() afin de selectionner les méta données des colonnes (primary key, not nul, type, taille etc...)
        // getFieldNames() permet de seelctionner le noms des champs/colonne de la table Article de la bdd
        // $colonne = $data->getColumnMeta() -> $colonne['name']
       $colonnes = $manager->getClassMetadata(Article::class)->getFieldNames();

       dump($colonnes);

       $articles = $repoArticle->findAll();

       dump($articles);

       if($article)//15
       {
       $id = $article->getId();

       $manager->remove($article);// prepare la requete de suppression

       $manager->flush();

       $this->addFlash('success',"l'article n$id  a bien été supprimeé");

       return $this->redirectToRoute('admin_articles');

       }
        return $this->render('admin/admin_articles.html.twig',[
            'colonnes' => $colonnes,
            'articlesBdd' => $articles//on trasmet a la methode render les articles selectionnés en BDD au template afin de pouvoir les afficher
        ]);
    }

    /**
     * 
     * @Route("/admin/{id}/edit-article", name="admin_edit_article")
     */
    public function adminEditArticles(Article $article,Request $request, EntityManagerInterface $manager )//1//5
    {
        dump($article);
       //on crée un formulaire via la classe ArticleFormtype qui a pour bit de remplir l'entité $article
        $formArticle = $this->createForm(ArticleFormType::class,$article);//3
        dump($request);//6

        $formArticle->handleRequest($request);//7

        if($formArticle->isSubmitted() && $formArticle->isValid())//8
        {
            $manager->persist($article);//9
            $manager->flush();//10

            $this->addFlash('success',"l'artcile n ". $article->getId() ." a été bien modifier ");//11

            return $this->redirectToRoute('admin_articles');//12

        }
     
        return $this->render('admin/admin_edit_article.html.twig',[
           'idArticle'=> $article->getId(),//2
           'formArticle'=>$formArticle->createView()//4

        ]);
    }

    /**
     * @Route("/admin/categories",name="admin_category")
     * @Route("/admin/category/{id}/remove", name="admin_remove_category")
     * 
     */
    public function adminCategory(EntityManagerInterface $manager,CategoryRepository $repoCategory,Category $categorie = null): Response
    {
      $colonnes = $manager->getClassMetadata(Category::class)->getFieldNames();

      $categories=$repoCategory->findAll();

      dump($categories);
     // si la variable $category retourne TRUE,cela veut dir qu'elle contient une catégorie de la BDD,alors on entre dans le if 
      if($categorie)
      {
          // Nous avons une relation entre la table Article et Category et une contrainte d'intégrité en RESTRICT
            // Donc ne pourrons pas supprimer la catégorie si 1 article lui est toujours associé
            // getArticles() de l'entité Category retourne tout les articles associés à la catégorie (relation bi-drirectionnelle)
            // Si getArticles() retourne un résultat vide, cela veut dire qu'il n'y a plus aucun article associé à la catégorie, nous pouvons dcon la supprimer 
          if($categorie->getArticles()->isEmpty())
          {
            $manager->remove($categorie);

            $manager->flush();

            $this->addFlash('success',"la categorie  a bien été supprimeé");

            return $this->redirectToRoute('admin_category');
          }
          else // si non dans les autres cas,des articles sont toujours associée a la catégorie,on affiche un message d'erreur utilisateur
          {
            $this->addFlash('danger',"impossible de supprimer la catégorie :article affiliées à celle-ci");
          }
        

          
      }

        return $this->render('admin/admin_category.html.twig',[
            'colonnes' => $colonnes,
            'categoryBDD' =>$categories
        ]);
    }
    /**
     * @Route("/admin/category/new",name="admin_new_category")
     * @Route("/admin/category/{id}/edit", name="admin_edit_category")
     */
     public function adminFormCategory(Category $categorie= null,Request $request, EntityManagerInterface $manager):Response
     {
      
         if(!$categorie)
         {
             $categorie = new Category;
         };
        dump($categorie);
       
         $formCategorie = $this->createForm(CategoryFormType::class,$categorie);
         dump($request);//6
 
         $formCategorie->handleRequest($request);
 
         if($formCategorie->isSubmitted() && $formCategorie->isValid())
         {
             if(!$categorie->getId())
                $message = " la categorie ".$categorie->getTitle() . " a été enregistrer avec succès ! ";
              else
                $message = "la catégorie " .$categorie->getTitle() ." a été modifier  avec succés !";  
            
             $manager->persist($categorie);
             $manager->flush();


             $this->addFlash('success', $message );//11
 
             return $this->redirectToRoute('admin_category');
 
         }
        return $this->render('admin/admin_form_category.html.twig',[
            'idCategorie'=>$categorie->getId(),
            'formCategorie'=>$formCategorie->createView()
          
        ]);
     }

     /**
      * 
      * @Route("/admin/comments",name="admin_comments")
      * @Route("/admin/comment/{id}/remove",name="admin_remove_comment")
      */
      public function adminComment( Comment $comment = null ,CommentRepository $repoComment,EntityManagerInterface $manager):Response
      {
        $colonnes = $manager->getClassMetadata(Comment::class)->getFieldNames();

        $commentsbdd = $repoComment->findAll();
         return $this->render('admin/admin_comments.html.twig',[
             'colonnes' => $colonnes,
             'commentsBdd' => $commentsbdd
             ]);

      }
    /**
     * @Route("/admin/comment/{id}/edit",name="admin_edit_comment")
     * 
     */
     public function editComment(Comment $comments,Request $request,EntityManagerInterface $manager ):Response
     {
        $formComment = $this->createForm(CommentFormType::class,$comments);
         dump($request);
 
         $formComment->handleRequest($request);
 
         if($formComment->isSubmitted() && $formComment->isValid())
         {
          
             $manager->persist($comments);
             $manager->flush();


 
             return $this->redirectToRoute('admin_comments');
         }   
        return $this->render('admin/admin_edit_comments.html.twig',[
            'idComment'=>$comments->getId(),
            'formComment'=>$formComment->createView()

        ]);
     }

 }
/*
            1. Faites en sorte de récupérer les métadonnée de la table Comment afin de récupérer le nom des champs/colonne de la table SQL comment et les transmettre au template
            2. Afficher le nom des champs/colonne sous forme de tableau HTML
            3. Dans le controller, seelctionner tout les commentaires stockés en BDD et les transmettre au template
            4. Afficher tout les commentaires de la BDD sous forme de tableau HTML dans le template
            5. Prévoir 2 liens (modification / suppression) pour chaque commentaire 
            6. Réaliser le traitement permettant de supprimer un commentaire dans la BDD
         */