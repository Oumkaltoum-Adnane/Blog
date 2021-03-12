<?php

namespace App\DataFixtures;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
      //j'importe la librairie Faker installée via composer
      $faker = \Faker\Factory::create('fr_FR');
      //librairie permettant de créer 

      //création de 3 catégorie
      for($i = 1; $i <= 3 ; $i++)
      {
        //pour insérer dans la table category, nous devons remplir des objets issu de son entité Category::class
        $category = new Category;
        //on appel les setteurs de l'objet
        $category->setTitle($faker->sentence()) //creer des phrases aleatoires
                ->setDescription($faker->paragraph());//on cree un paragraphe aleatoire

        $manager->persist($category); //on garde en mémoire et on prépare les requetes d'insertion   
        
        //création de 4 a 6  articles pour chaque category
        for($j = 1;$j <= mt_rand(4,6);$j++)
        {
          $article = new Article;

          $content= '<p>' .join($faker->paragraphs(5),'</p><p>').'</p>';

          $article->setTitle($faker->sentence())
                  ->setContent($content)
                  ->setImage("https://picsum.photos/seed/picsum/600/400")
                  ->setCreatedAt($faker->dateTimeBetween('-6 months'))
                  ->setCategory($category);

          $manager->persist($article);
          
          //creation de 4 à 10 commentaires pour chaque article
          for($k = 1;$k <= mt_rand(4,10); $k++)
          {
            $comment = new Comment;

            $content= '<p>' . join($faker->paragraphs(2),'</p><p>') .'</p>';

            $now = new \DateTime;// retourne la date du jour
            $interval = $now->diff($article->getCreatedAt());//retourne un timestamp (temps en secondes) entre la date de creation des articles et aujourd'hui
            $days = $interval->days;// nombre de jour entre la date de création des articles et aujourd'hui
            $minimum = "-$days days";/* -100 days le but est d'avoir des commentaires qui à l'interval de la création des articles, des commentaires de - de 6 mois à aujourd'hui */

            $comment->setAuthor($faker->name)
                    ->setContent($content)
                    ->setCreatedAt($faker->dateTimeBetween($minimum))
                    ->setArticle($article);

            $manager->persist($comment);        
          }
        }
                
      }
      $manager->flush();
    }

}
