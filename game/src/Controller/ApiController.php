<?php

namespace App\Controller;

use App\Entity\Achievement;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ApiController extends AbstractController
{
    private $entityManager;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/api/save-steps", methods={"POST"})
     */
    public function saveSteps(Request $request): JsonResponse
    {
        // Retrieve data from the request
        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'];
        $difficulty = $data['difficulty'];
        $steps = $data['steps'];

        // Get the User from the database
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Track minimum steps per difficulty
        $this->saveUserSteps($user, $difficulty, $steps);

        return new JsonResponse(['status' => 'success'], JsonResponse::HTTP_OK);
    }

    private function saveUserSteps(User $user, string $difficulty, int $steps)
    {
        // You can have a property on User like $stepsForDifficulties which is an associative array
        // e.g., ['normal' => 150, 'hard' => 100, 'extreme' => 250]
        
        $stepsField = "stepsFor" . ucfirst($difficulty);  // Generate the field name dynamically, e.g., stepsForNormal
        $currentSteps = $user->$stepsField();  // Get the current steps for this difficulty
        
        // Save the minimum steps for this difficulty level
        if (!$currentSteps || $steps < $currentSteps) {
            $user->$stepsField = $steps;
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}
