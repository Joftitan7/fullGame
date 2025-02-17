<?php

// src/Controller/Api/AchievementController.php

namespace App\Controller\Api;

use App\Entity\Achievement;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/api/achievements')]
class AchievementController extends AbstractController
{
    #[Route('', methods: ['POST'])]
    public function unlockAchievement(Request $request, EntityManagerInterface $em, UserInterface $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $title = $data['title'] ?? null;

        if (!$title) {
            return new JsonResponse(['error' => 'Title is required'], 400);
        }

        // Check if the user has already unlocked this achievement
        $existingAchievement = $em->getRepository(Achievement::class)->findOneBy([
            'user' => $user,
            'title' => $title,
        ]);

        if ($existingAchievement) {
            return new JsonResponse(['error' => 'Achievement already unlocked'], 400);
        }

        // Create the achievement
        $achievement = new Achievement();
        $achievement->setTitle($title)
            ->setAchievedAt(new \DateTimeImmutable())
            ->setUser($user);

        $em->persist($achievement);
        $em->flush();

        return new JsonResponse(['message' => 'Achievement unlocked successfully'], 200);
    }
}

