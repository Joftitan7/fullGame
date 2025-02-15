<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameFormType;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/game')]
class GameController extends AbstractController
{
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
}
