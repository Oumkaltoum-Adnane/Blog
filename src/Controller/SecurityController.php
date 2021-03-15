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

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription",name="security_registration")
     */
    public function registration(Request $request ,EntityManagerInterface $manager,UserPasswordEncoderInterface $encoder): Response //5 ajouter request //10 ajouter manager et entity//13 ajouter userpassword
    {
        $user = new User;//1

        $formRegistration = $this->createForm(RegistrationFormType::class,$user);//2

        dump($request);//6

        $formRegistration->handleRequest($request);//7

       dump($user);//8

        if($formRegistration->isSubmitted() && $formRegistration->isValid())//9
        {
            $hash = $encoder->encodePassword($user,$user->getPassword());//14
           
            dump($hash);//15

            $user->setPassword($hash);//16

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
     * 
     * @Route("/connexion", name="security_login")
     * 
     */
    public function login(): Response
    {

        return $this->render('security/login.html.twig');
    }
    /**
     * @Route("/deconnexion",name="security_logout")
     */
    public function logout()
    {
        //cette methode ne retourne rien,il nous suffit d'avoir une route pour se deconnecter
    }
}