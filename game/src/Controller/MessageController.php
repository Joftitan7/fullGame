<?php

// src/Controller/MessageController.php
namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Message;
use App\Form\MessageFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MessageController extends AbstractController
{
    // Route to display inbox
    #[Route('/messages/inbox', name: 'message_inbox')]
    public function inbox(EntityManagerInterface $em): Response
    {
        // Get logged-in user
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('user_login');
        }

        // Fetch messages where the user is the receiver
        $messages = $em->getRepository(Message::class)->findBy(['receiver' => $user], ['createdAt' => 'DESC']);

        return $this->render('message/inbox.html.twig', [
            'messages' => $messages,
        ]);
    }

    // Route to display sent messages
    #[Route('/messages/sent', name: 'message_sent')]
    public function sentMessages(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('user_login');
        }

        // Fetch messages where the user is the sender
        $messages = $em->getRepository(Message::class)->findBy(['sender' => $user], ['createdAt' => 'DESC']);

        return $this->render('message/sent.html.twig', [
            'messages' => $messages,
        ]);
    }

    // Route to send a message
    
    #[Route('/messages/send', name: 'message_send')]
    public function send(Request $request, EntityManagerInterface $em): Response
{
    $user = $this->getUser();

    if (!$user) {
        return $this->redirectToRoute('user_login');
    }

    $message = new Message();
    $parentMessage = $request->query->get('parentMessage')
        ? $em->getRepository(Message::class)->find($request->query->get('parentMessage'))
        : null;

    // Create the form, passing the current user and optional parent message
    $form = $this->createForm(MessageFormType::class, $message, [
        'user' => $user,
        'parent_message' => $parentMessage,
    ]);

    // Get the user's friends
    if ($user instanceof User) {
        $friends = $user->getFriends();

        if ($friends->isEmpty()) {
            $this->addFlash('error', 'You have no friends yet.');
        }
    } else {
        $friends = new ArrayCollection(); // Empty collection if user is invalid
    }

    // Handle the form submission
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $message->setSender($user);
        $receiver = $form->get('receiver')->getData();

        if ($receiver && $friends->contains($receiver)) {
            // Valid receiver, proceed with message sending
            $message->setReceiver($receiver);

            if ($parentMessage) {
                $message->setParentMessage($parentMessage);
            }

            $em->persist($message);
            $em->flush();

            $this->addFlash('success', 'Message sent successfully!');
            return $this->redirectToRoute('message_inbox');
        } else {
            // Invalid receiver or not a friend
            $this->addFlash('error', 'Receiver is not valid or not a friend.');
        }
    }

    // Render the template with friends and form data
    return $this->render('message/send.html.twig', [
        'friends' => $friends,
        'form' => $form->createView(),
    ]);
}



    


    // Route to view a specific message
    #[Route('/messages/{id}', name: 'message_view')]
public function viewMessage(int $id, EntityManagerInterface $em): Response
{
    $user = $this->getUser();
    $message = $em->getRepository(Message::class)->find($id);

    if (!$message || ($message->getSender() !== $user && $message->getReceiver() !== $user)) {
        throw $this->createAccessDeniedException('You are not allowed to view this message.');
    }

    $message->setRead(true);  // Mark message as read
    $em->flush();

    return $this->render('message/view.html.twig', [
        'message' => $message,
    ]);
}

#[Route('/messages/{id}/delete', name: 'message_delete')]
public function delete(int $id, EntityManagerInterface $em): Response
{
    $user = $this->getUser();
    $message = $em->getRepository(Message::class)->find($id);

    if (!$message) {
        throw $this->createNotFoundException('Message not found.');
    }

    // If the sender deletes, mark as deletedBySender
    if ($message->getSender() === $user) {
        $message->setDeletedBySender(true);
    }

    // If the receiver deletes, mark as read and archived (or you can implement deletedByReceiver)
    if ($message->getReceiver() === $user) {
        $message->setRead(true);
    }

    $em->flush();

    return $this->redirectToRoute('message_inbox');
}


    
}

