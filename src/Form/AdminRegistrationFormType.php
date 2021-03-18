<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdminRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('username')
            ->add('roles',CollectionType::class,[
                'label_format'=> 'Role utilisateur',
                'entry_type'=>ChoiceType::class,//on définit le champ comme une liste déroulante
                'entry_options' =>[// on définit les valeur des balise <option></option> du selecteur
                    'choices' =>[
                        'utilisateur' => 'ROLE_USER',
                        'Administrateur'=>'ROLE_ADMIN'
                        //'utilisateur' --> contenu de la basile <option></option>
                        //'ROLE_USER' -->DANS l'attribut value <option value='ROLE_USER'>utilisateur</option>
                    ]
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
