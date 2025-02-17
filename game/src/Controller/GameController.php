<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameFormType;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Entity\User; // Import the User entity class

#[Route('/game')]
class GameController extends AbstractController
{
    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    #[Route('/complete', methods: ['POST'])]
    public function completeGame(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'User not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $stepsTaken = $data['stepsTaken'] ?? null;
        $difficulty = $data['difficulty'] ?? null;

        if ($stepsTaken === null || $difficulty === null) {
            return new JsonResponse(['error' => 'Steps and difficulty are required'], 400);
        }

        // Update the user record with the steps based on difficulty
        switch ($difficulty) {
            case 'normal':
                $user->setStepsForNormal($stepsTaken);
                break;
            case 'hard':
                $user->setStepsForHard($stepsTaken);
                break;
            case 'extreme':
                $user->setStepsForExtreme($stepsTaken);
                break;
            default:
                return new JsonResponse(['error' => 'Invalid difficulty'], 400);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Game completed and steps saved'], 200);
    }

    #[Route('/new', name: 'game_create')]
public function create(Request $request, SluggerInterface $slugger): Response
{
    $game = new Game();
    $form = $this->createForm(GameFormType::class, $game);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        /** @var User|null $user */
        $user = $this->getUser();

        if ($user instanceof User) {
            $game->setUser($user);  // Set the logged-in user as the game owner
        } else {
            // Handle the case where the user is not logged in or is not a valid User instance
            return $this->redirectToRoute('game_create');  // Or return an error response
        }

        // Handle file upload with image validation
        $thumbnailFile = $form->get('thumbnail')->getData();
        if ($thumbnailFile) {
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            if (!in_array($thumbnailFile->getMimeType(), $allowedMimeTypes, true)) {
                $this->addFlash('error', 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.');
                return $this->redirectToRoute('game_create');
            }

            $originalFilename = pathinfo($thumbnailFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $thumbnailFile->guessExtension();

            try {
                $thumbnailFile->move(
                    $this->getParameter('thumbnails_directory'), // Configure this in services.yaml
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('error', 'Failed to upload the image.');
                return $this->redirectToRoute('game_create');
            }

            $game->setThumbnail($newFilename);
        }

        $this->entityManager->persist($game);
        $this->entityManager->flush();

        return $this->redirectToRoute('game_list');
    }

    return $this->render('game/create.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('/', name: 'game_list')]
    public function list(GameRepository $gameRepository): Response
    {
        $games = $gameRepository->findBy(['user' => $this->getUser()]);

        return $this->render('game/list.html.twig', [
            'games' => $games,
        ]);
    }

    #[Route('/games', name: 'game_public_list')]
    public function publicGames(GameRepository $gameRepository): Response
    {
        // Fetch public games
        $games = $gameRepository->findBy(['visibility' => 'public']);

        return $this->render('game/public_list.html.twig', [
            'games' => $games,
        ]);
    }

    #[Route('/edit/{id}', name: 'game_edit')]
    public function edit(Game $game, Request $request, SluggerInterface $slugger): Response
    {
        if ($game->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(GameFormType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thumbnailFile = $form->get('thumbnail')->getData();
            if ($thumbnailFile) {
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                if (!in_array($thumbnailFile->getMimeType(), $allowedMimeTypes, true)) {
                    $this->addFlash('error', 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.');
                    return $this->redirectToRoute('game_edit', ['id' => $game->getId()]);
                }

                $originalFilename = pathinfo($thumbnailFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $thumbnailFile->guessExtension();

                try {
                    $thumbnailFile->move(
                        $this->getParameter('thumbnails_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload the image.');
                    return $this->redirectToRoute('game_edit', ['id' => $game->getId()]);
                }

                $game->setThumbnail($newFilename);
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('game_list');
        }

        return $this->render('game/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'game_delete')]
    public function delete(Game $game): Response
    {
        if ($game->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $this->entityManager->remove($game);
        $this->entityManager->flush();

        return $this->redirectToRoute('game_list');
    }

    #[Route('/game/{gameId}', name: 'game_play')]
    public function play(int $gameId, GameRepository $gameRepo): Response
    {
        $game = $gameRepo->find($gameId);
        if (!$game) {
            throw $this->createNotFoundException('Game not found');
        }

        return $this->render('game/play.html.twig', ['game' => $game]);
    }
}
