<?php

namespace App\Controller\Api;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/leaderboard')]
class LeaderboardController extends AbstractController
{
    
    #[Route('/normal', methods: ['GET'])]
    public function getNormalLeaderboard(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findBy([], ['stepsForNormal' => 'ASC']); // Use stepsForNormal instead of stepForNormal
        $leaderboard = array_map(fn($user) => [
            'username' => $user->getUsername(),
            'steps' => $user->getStepsForNormal(), // Use getStepsForNormal
        ], $users);

        return new JsonResponse($leaderboard);
    }

    #[Route('/hard', methods: ['GET'])]
    public function getHardLeaderboard(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findBy([], ['stepsForHard' => 'ASC']); // Use stepsForHard instead of stepForHard
        $leaderboard = array_map(fn($user) => [
            'username' => $user->getUsername(),
            'steps' => $user->getStepsForHard(), // Use getStepsForHard
        ], $users);

        return new JsonResponse($leaderboard);
    }

    #[Route('/extreme', methods: ['GET'])]
    public function getExtremeLeaderboard(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findBy([], ['stepsForExtreme' => 'ASC']); // Use stepsForExtreme instead of stepForExtreme
        $leaderboard = array_map(fn($user) => [
            'username' => $user->getUsername(),
            'steps' => $user->getStepsForExtreme(), // Use getStepsForExtreme
        ], $users);

        return new JsonResponse($leaderboard);
    }
}
