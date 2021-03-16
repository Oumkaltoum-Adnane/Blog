<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleFormType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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

       $manager->remove($article);

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
     * @Route("/admin/categories",name="admin_category")
     * 
     */
    public function adminCategory(EntityManagerInterface $manager,CategoryRepository $repoCategory): Response
    {
      $colonnes = $manager->getClassMetadata(Category::class)->getFieldNames();

      $categories=$repoCategory->findAll();

        return $this->render('admin/admin_category.html.twig',[
            'colonnes' => $colonnes,
            'categoryBDD' =>$categories
        ]);
    }
    /**
     * @Route("/admin/category/new",name="admin_new_category")
     * @Route("/admin/category/{id}/edit", name="admin_edit_category")
     */
//     public function adminFormCategory()


 }
