<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FriendRequestController extends AbstractController{
    #[Route('/friend/request', name: 'app_friend_request')]
    public function index(): Response
    {
        return $this->render('friend_request/index.html.twig', [
            'controller_name' => 'FriendRequestController',
        ]);
    }
}
