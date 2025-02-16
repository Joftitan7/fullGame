<?php
namespace App\Controller;

use App\Entity\FriendRequest;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function profile(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Get received friend requests
        $receivedRequests = $em->getRepository(FriendRequest::class)
        ->findBy(['toUser' => $user, 'status' => 'pending']); // Only show pending requests


        dump($receivedRequests);


    return $this->render('user/profile.html.twig', [
        'receivedRequests' => $receivedRequests,
         // Pass receivedRequests variable to template
    ]);
    }

    #[Route('/accept_friend_request/{id}', name: 'accept_friend_request')]
    public function acceptFriendRequest(FriendRequest $friendRequest, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($friendRequest->getToUser() === $user) {
            $friendRequest->setStatus('accepted');
            $em->flush();

            $this->addFlash('success', 'Friend request accepted!');
        } else {
            $this->addFlash('error', 'You can only accept requests sent to you.');
        }

        return $this->redirectToRoute('profile');
    }

    #[Route('/decline_friend_request/{id}', name: 'decline_friend_request')]
    public function declineFriendRequest(FriendRequest $friendRequest, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if ($friendRequest->getToUser() === $user) {
            $em->remove($friendRequest);
            $em->flush();

            $this->addFlash('success', 'Friend request declined.');
        } else {
            $this->addFlash('error', 'You can only decline requests sent to you.');
        }

        return $this->redirectToRoute('profile');
    }
}
