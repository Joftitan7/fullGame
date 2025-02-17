<?php

namespace App\Controller;

use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomePageController extends AbstractController
{
    #[Route('/', name: 'app_home_page')]
    #[Route('/homepage', name: 'app_home_page_alt')]
    public function index(EntityManagerInterface $em): Response
    {
        // Fetch all public games (assuming a field `isPublic` exists in your Game entity)
        $publicGames = $em->getRepository(Game::class)->findBy(['visibility' => 'public']);

        return $this->render('home_page/index.html.twig', [
            'publicGames' => $publicGames,
        ]);
    }
}
