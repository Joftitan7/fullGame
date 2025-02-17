<?php

// src/Controller/UserController.php
namespace App\Controller;


use App\Entity\User;
use App\Entity\FriendRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


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
public function profile(EntityManagerInterface $em): Response
{
    $user = $this->getUser(); // Get the logged-in user

    if (!$user) {
        throw $this->createAccessDeniedException("You must be logged in to view this page.");
    }

    // Fetch received friend requests where 'toUser' is the logged-in user
    $receivedRequests = $em->getRepository(FriendRequest::class)
        ->findBy(['toUser' => $user, 'status' => 'pending']);

    return $this->render('user/profile.html.twig', [
        'receivedRequests' => $receivedRequests, // ✅ Now Twig can use it
    ]);
}


#[Route('/profile/edit', name: 'user_profile_edit')]
public function editProfile(Request $request, EntityManagerInterface $em): Response
{
    $user = $this->getUser(); // Get the logged-in user

    if (!$user instanceof User) {
        throw new \LogicException('Expected App\Entity\User but got ' . get_class($user));
    }

    if (!$user) {
        return $this->redirectToRoute('user_login');
    }

    if ($request->isMethod('POST')) {
        $user->setUsername($request->request->get('username'));
        $user->setEmail($request->request->get('email'));

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Profile updated successfully!');
        return $this->redirectToRoute('user_profile');
    }

    return $this->render('user/edit_profile.html.twig', [
        'user' => $user,
    ]);
}

#[Route('/profile/change-password', name: 'user_change_password')]
public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
{
    $user = $this->getUser();
    
    if (!$user) {
        return $this->redirectToRoute('user_login');
    }


    if ($request->isMethod('POST')) {
        $oldPassword = $request->request->get('old_password');
        $newPassword = $request->request->get('new_password');

        if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
            $this->addFlash('error', 'Old password is incorrect.');
            return $this->redirectToRoute('user_change_password');
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'Password changed successfully!');
        return $this->redirectToRoute('user_profile');
    }

    return $this->render('user/change_password.html.twig');
}

#[Route('/search', name: 'user_search')]
public function search(Request $request, EntityManagerInterface $em): Response
{
    $query = $request->query->get('q'); // Get the search query from URL
    $users = [];

    if ($query) {
        $users = $em->getRepository(User::class)->createQueryBuilder('u')
            ->where('u.username LIKE :query OR u.email LIKE :query')
            ->setParameter('query', "%{$query}%")
            ->getQuery()
            ->getResult();
    }

    return $this->render('user/search.html.twig', [
        'users' => $users,
        'query' => $query,
    ]);
}

#[Route('/send-friend-request/{id}', name: 'send_friend_request')]
public function sendFriendRequest(User $user, EntityManagerInterface $em): Response
{
    $currentUser = $this->getUser();

    if (!$currentUser) {
        return $this->redirectToRoute('user_login');
    }

    // Check if request already exists
    $existingRequest = $em->getRepository(FriendRequest::class)->findOneBy([
        'fromUser' => $currentUser,
        'toUser' => $user,
    ]);

    if ($existingRequest) {
        $this->addFlash('info', 'Friend request already sent.');
        return $this->redirectToRoute('user_search');
    }

    // Create new friend request
    $friendRequest = new FriendRequest();
    $friendRequest->setFromUser($currentUser);
    $friendRequest->setToUser($user);
    $friendRequest->setStatus('pending');

    $em->persist($friendRequest);
    $em->flush();

    $this->addFlash('success', 'Friend request sent successfully!');
    return $this->redirectToRoute('user_search');
}


}
