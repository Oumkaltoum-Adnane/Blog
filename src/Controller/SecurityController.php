<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription",name="security_registration")
     */
    public function registration(Request $request ,EntityManagerInterface $manager,UserPasswordEncoderInterface $encoder): Response //5 ajouter request //10 ajouter manager et entity//13 ajouter userpassword
    {
        $user = new User;//1

        $formRegistration = $this->createForm(RegistrationFormType::class,$user,[
            'valisation_groups' => ['registration']
        ]);//2
       // Nous définissons un groupe de validation de contraintes afin qu'elles ne soient prise en compte uniquement lors de l'inscription et non lors de la modification dans le BackOffice
        dump($request);//6

        $formRegistration->handleRequest($request);//7

       dump($user);//8

        if($formRegistration->isSubmitted() && $formRegistration->isValid())//9
        {
            $hash = $encoder->encodePassword($user,$user->getPassword());//14
           
            dump($hash);//15

            $user->setPassword($hash);//16
            //pour chaque inscription ,l'utilisateur aura par defaut le ROLE_USER
            $user->setRoles(["ROLE_USER"]);//16

            dump($user);//17

            $manager->persist($user);//11 preparation et mise en memoire de la requete insert sql
            $manager->flush();//12 

           $this->addFlash('success',"Félicitations !!  votre compte est bien crée  vous pouvez vous connecté ! ");//19

           return $this->redirectToRoute('security_login');//18     

        }

        return $this->render('security/registration.html.twig',[
          'formRegistration'=> $formRegistration->createView()//3
        ]);
    }
    /**
     * AuthenticationUtils permet de récupérer le dernier Email saisie au moment de la connexion
     * AuthenticationUtils permet de récupérer les messages l'erreurs en cas de mauvaise connexion
     * @Route("/connexion", name="security_login")
     * 
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
       //recuperation 
        $error = $authenticationUtils->getLastAuthenticationError();

        //recupération du dernier username(email)saisi par l'internaute dans le formulaire de connexion en cas d'erreur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig',[
            'error' =>$error,
            'lastUsername'=>$lastUsername
        ]);
    }
    /**
     * @Route("/deconnexion",name="security_logout")
     */
    public function logout()
    {
        //cette methode ne retourne rien,il nous suffit d'avoir une route pour se deconnecter
    }
}
