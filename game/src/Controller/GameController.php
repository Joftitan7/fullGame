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

#[Route('/game')]
class GameController extends AbstractController
{
    private $security;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    // /**
    //  * @Route("/game/save-steps", name="save_game_steps", methods={"POST"})
    //  */
    // public function saveSteps(Request $request): JsonResponse
    // {
    //     // Get the current user
    //     $user = $this->security->getUser();

    //     // Get the data from the request
    //     $data = json_decode($request->getContent(), true);

    //     // Ensure that the difficulty and steps are provided
    //     if (!isset($data['difficulty']) || !isset($data['steps'])) {
    //         return new JsonResponse(['success' => false, 'message' => 'Invalid data.'], 400);
    //     }

    //     $difficulty = $data['difficulty'];
    //     $steps = (int) $data['steps'];

    //     // Update the minimum steps for the respective difficulty
    //     switch ($difficulty) {
    //         case 'normal':
    //             if ($user->getStepsForNormal() === null || $steps < $user->getStepsForNormal()) {
    //                 $user->setStepsForNormal($steps);
    //             }
    //             break;
    //         case 'hard':
    //             if ($user->getStepsForHard() === null || $steps < $user->getStepsForHard()) {
    //                 $user->setStepsForHard($steps);
    //             }
    //             break;
    //         case 'extreme':
    //             if ($user->getStepsForExtreme() === null || $steps < $user->getStepsForExtreme()) {
    //                 $user->setStepsForExtreme($steps);
    //             }
    //             break;
    //         default:
    //             return new JsonResponse(['success' => false, 'message' => 'Invalid difficulty.'], 400);
    //     }

    //     // Save the updated user entity
    //     $entityManager = $this->getDoctrine()->getManager();
    //     $entityManager->flush();

    //     return new JsonResponse(['success' => true]);
    // }

    #[Route('/complete', methods: ['POST'])]
    public function completeGame(Request $request, EntityManagerInterface $em, UserInterface $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $stepsTaken = $data['stepsTaken'] ?? null;
        $difficulty = $data['difficulty'] ?? null;

        if (!$stepsTaken || !$difficulty) {
            return new JsonResponse(['error' => 'Steps and difficulty are required'], 400);
        }

        // Update the user record with the steps based on difficulty
        switch ($difficulty) {
            case 'normal':
                $user->setStepsNormal($stepsTaken);
                break;
            case 'hard':
                $user->setStepsHard($stepsTaken);
                break;
            case 'extreme':
                $user->setStepsExtreme($stepsTaken);
                break;
            default:
                return new JsonResponse(['error' => 'Invalid difficulty'], 400);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['message' => 'Game completed and steps saved'], 200);
    }



    



    #[Route('/new', name: 'game_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $game = new Game();
        $form = $this->createForm(GameFormType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $game->setUser($this->getUser()); // Set the logged-in user

            // Handle file upload with image validation
            $thumbnailFile = $form->get('thumbnail')->getData();
            if ($thumbnailFile) {
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                if (!in_array($thumbnailFile->getMimeType(), $allowedMimeTypes)) {
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

            $entityManager->persist($game);
            $entityManager->flush();

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

    // src/Controller/GameController.php

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
    public function edit(Game $game, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
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

                if (!in_array($thumbnailFile->getMimeType(), $allowedMimeTypes)) {
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

            $entityManager->flush();

            return $this->redirectToRoute('game_list');
        }

        return $this->render('game/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'game_delete')]
    public function delete(Game $game, EntityManagerInterface $entityManager): Response
    {
        if ($game->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($game);
        $entityManager->flush();

        return $this->redirectToRoute('game_list');
    }

    #[Route('/game/{gameId}', name: 'game_play')]
public function play(int $gameId, GameRepository $gameRepo): Response
{
    $game = $gameRepo->find($gameId);
    // Redirect to the game's page
    return $this->render('game/play.html.twig', ['game' => $game]);
}
}


