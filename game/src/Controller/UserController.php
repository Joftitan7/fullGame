<?php

// src/Controller/UserController.php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/register', name: 'user_register')]
    public function register(): Response
    {
        return $this->render('user/register.html.twig');
    }

    #[Route('/login', name: 'user_login')]
    public function login(EntityManagerInterface $em): Response
    {
        $user = new User;
        $user->setEmail('test@test.be');
        $em->persist($user);
        $em->flush();


        return $this->render('user/login.html.twig');
    }

    #[Route('/profile', name: 'user_profile')]
    public function profile(): Response
    {
        return $this->render('user/profile.html.twig');
    }
}
