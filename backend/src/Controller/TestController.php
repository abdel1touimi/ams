<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/api/test/public', name: 'test_public', methods: ['GET'])]
    public function public(): JsonResponse
    {
        return $this->json([
            'message' => 'Public endpoint works!',
        ]);
    }

    #[Route('/api/test/protected', name: 'test_protected', methods: ['GET'])]
    public function protected(): JsonResponse
    {
        return $this->json([
            'message' => 'Protected endpoint works!',
            'user' => $this->getUser()?->getUserIdentifier()
        ]);
    }
}
