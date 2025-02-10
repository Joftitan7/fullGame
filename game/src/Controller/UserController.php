<?php

// src/Controller/UserController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    #[Route('/register', name: 'user_register')]
    public function register(): Response
    {
        return $this->render('user/register.html.twig');
    }

    #[Route('/login', name: 'user_login')]
    public function login(): Response
    {
        return $this->render('user/login.html.twig');
    }

    #[Route('/profile', name: 'user_profile')]
    public function profile(): Response
    {
        return $this->render('user/profile.html.twig');
    }
}
