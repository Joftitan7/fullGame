<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
    
            // Handle profile photo upload
            $profilePhotoFile = $form->get('profilePhoto')->getData();
            
            dump($profilePhotoFile);

            if ($profilePhotoFile) {
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
                if (!in_array($profilePhotoFile->getMimeType(), $allowedMimeTypes)) {
                    $this->addFlash('error', 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.');
                    return $this->redirectToRoute('app_register');
                }
    
                $originalFilename = pathinfo($profilePhotoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profilePhotoFile->guessExtension();
    
                try {
                    $profilePhotoFile->move(
                        $this->getParameter('profile_photos_directory'), // Define this in services.yaml
                        $newFilename
                    );
                    $user->setProfilePhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Profile photo upload failed.');
                    return $this->redirectToRoute('app_register');
                }
            }
    
            // Encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
