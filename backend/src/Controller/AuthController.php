<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Exception\ValidationException;
use App\Service\AuthService;
use App\Service\ResponseHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Document\User;
use App\Response\ApiResponse;

#[Route('/api')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly ResponseHandler $responseHandler
    ) {}

    #[Route('/register', methods: ['POST'])]
    public function register(Request $request): ApiResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $userDTO = UserDTO::fromRequest($data);

            $user = $this->authService->register($userDTO);
            return $this->responseHandler->created($user, 'User registered successfully');
        } catch (ValidationException $e) {
            return $this->responseHandler->validationError($e->getErrors());
        }
    }

    #[Route('/me', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): ApiResponse
    {
        if (!$user) {
            return $this->responseHandler->unauthorized();
        }

        return $this->responseHandler->success(
            UserDTO::fromEntity($user),
            'User profile retrieved successfully'
        );
    }

    #[Route('/me', methods: ['PUT'])]
    public function updateProfile(Request $request, #[CurrentUser] ?User $user): ApiResponse
    {
        if (!$user) {
            return $this->responseHandler->unauthorized();
        }

        try {
            $data = json_decode($request->getContent(), true);
            $userDTO = UserDTO::fromRequest($data);

            $updatedUser = $this->authService->updateProfile($user, $userDTO);
            return $this->responseHandler->success($updatedUser, 'Profile updated successfully');
        } catch (ValidationException $e) {
            return $this->responseHandler->validationError($e->getErrors());
        }
    }

    #[Route('/me/password', methods: ['PUT'])]
    public function changePassword(Request $request, #[CurrentUser] ?User $user): ApiResponse
    {
        if (!$user) {
            return $this->responseHandler->unauthorized();
        }

        try {
            $data = json_decode($request->getContent(), true);
            $success = $this->authService->changePassword($user, $data);

            return $this->responseHandler->success(null, 'Password changed successfully');
        } catch (ValidationException $e) {
            return $this->responseHandler->validationError($e->getErrors());
        } catch (\InvalidArgumentException $e) {
            return $this->responseHandler->error($e->getMessage());
        }
    }
}
