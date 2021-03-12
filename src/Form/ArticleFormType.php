<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            //on ajoute le champs'category' au formulaire d'insertions des articles étant donné que nous somme obligé,
            ->add('category',EntityType::class, [
                  'class' => Category::class,
                  'choice_label' => 'title'
                  ])
            ->add('content')
            ->add('image')
            // ->add('createdAt')nous n'avons pas de champs 'date' dans le formulaire
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
