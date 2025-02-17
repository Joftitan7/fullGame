<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\FriendRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserSearchController extends AbstractController
{
    #[Route('/send_friend_request/{id}', name: 'send_friend_request')]
    public function sendFriendRequest(User $recipient, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('user_login');
        }

        if ($user === $recipient) {
            $this->addFlash('error', 'You cannot send a friend request to yourself.');
            return $this->redirectToRoute('user_search');
        }

        // Check if a friend request already exists
        $existingRequest = $em->getRepository(FriendRequest::class)
            ->findOneBy(['fromUser' => $user, 'toUser' => $recipient]);

        if ($existingRequest) {
            $this->addFlash('error', 'You have already sent a friend request to this user.');
        } else {
            // Create a new friend request
            $friendRequest = new FriendRequest();
            $friendRequest->setFromUser($user);
            $friendRequest->setToUser($recipient);
            $friendRequest->setStatus('pending');

            $em->persist($friendRequest);
            $em->flush();

            $this->addFlash('success', 'Friend request sent!');
        }

        return $this->redirectToRoute('user_search');
    }

    #[Route('/search', name: 'user_search')]
    public function search(Request $request, EntityManagerInterface $em): Response
    {
        $query = $request->query->get('q'); // Get the search query parameter
        $users = [];

        if ($query) {
            $users = $em->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.username LIKE :query OR u.email LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->getQuery()
                ->getResult();
        }

        return $this->render('user/search.html.twig', [
            'users' => $users,
            'query' => $query
        ]);
    }
}
