<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;  // Use the correct import
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        
        // Create the registration form with the User object
        $form = $this->createForm(RegistrationFormType::class, $user);

        // Handle the form request (bind data to the $user object)
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the plain password data
            $plainPassword = $form->get('plainPassword')->getData();

            // Hash the password using the password hasher
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Optionally: Set any default values or perform additional logic for new user
            // Example: Setting default roles (if not set in form)
            if (empty($user->getRoles())) {
                $user->setRoles(['ROLE_USER']);
            }

            // Persist the user entity in the database
            $entityManager->persist($user);
            $entityManager->flush();

            // Redirect to the homepage after successful registration
            return $this->redirectToRoute('app_home_page');
        }

        // Render the registration form
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
