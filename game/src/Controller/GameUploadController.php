<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameUploadFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GameUploadController extends AbstractController
{
    #[Route('/game/upload', name: 'game_upload')]
    #[IsGranted('ROLE_USER')] // Only logged-in users can upload games
    public function upload(Request $request, EntityManagerInterface $entityManager): Response
{
    $game = new Game();
    $form = $this->createForm(GameUploadFormType::class, $game);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Handle game file upload
        /** @var UploadedFile $gameFile */
        $gameFile = $form->get('gameFile')->getData();
        if ($gameFile) {
            $newFilename = uniqid().'.'.$gameFile->getClientOriginalExtension(); // Use getClientOriginalExtension instead of guessExtension
            try {
                $gameFile->move($this->getParameter('games_directory'), $newFilename);
                $game->setGameFile($newFilename); // Save the file name to the entity
            } catch (FileException $e) {
                $this->addFlash('error', 'Failed to upload game.');
            }
        }

        $locationUrl = $form->get('locationUrl')->getData();
        if ($locationUrl) {
            $game->setLocationUrl($locationUrl);  // Set the location URL to the entity
        }

        // Handle thumbnail upload
        /** @var UploadedFile $thumbnail */
        $thumbnail = $form->get('thumbnail')->getData();
        if ($thumbnail) {
            $newThumbFilename = uniqid().'.'.$thumbnail->getClientOriginalExtension(); // Same for thumbnail
            try {
                $thumbnail->move($this->getParameter('thumbnails_directory'), $newThumbFilename);
                $game->setThumbnail($newThumbFilename); // Save the thumbnail file name to the entity
            } catch (FileException $e) {
                $this->addFlash('error', 'Failed to upload thumbnail.');
            }
        }
        
        if ($form->isSubmitted() && $form->isValid()) {
            $game = $form->getData();
        
            // Ensure the visibility is set before persisting
            if (null === $game->getVisibility()) {
                $game->setVisibility('public');  // Default visibility (if necessary)
            }

        }


        // Set the game owner and save it to the DB
        $game->setUser($this->getUser());
        $entityManager->persist($game);
        $entityManager->flush();

        $this->addFlash('success', 'Game uploaded successfully!');
        return $this->redirectToRoute('game_list');
    }

    return $this->render('game/upload.html.twig', [
        'form' => $form->createView(),
    ]);
}


}
