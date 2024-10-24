<?php

namespace App\Controller;

use App\DTO\ArticleDTO;
use App\Exception\ValidationException;
use App\Service\ArticleService;
use App\Service\ResponseHandler;
use App\Response\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Document\User;

#[Route('/api/articles')]
class ArticleController extends AbstractController
{
    public function __construct(
        private readonly ArticleService $articleService,
        private readonly ResponseHandler $responseHandler
    ) {}

    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): ApiResponse
    {
        $articles = $this->articleService->getUserArticles($user);
        return $this->responseHandler->success(
            $articles,
            'Articles retrieved successfully'
        );
    }

    #[Route('/{id}', methods: ['GET'])]
    public function get(string $id, #[CurrentUser] User $user): ApiResponse
    {
        $article = $this->articleService->getArticle($id, $user);

        if (!$article) {
            return $this->responseHandler->notFound('Article not found or not authorized');
        }

        return $this->responseHandler->success($article, 'Article retrieved successfully');
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request, #[CurrentUser] User $user): ApiResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $articleDTO = ArticleDTO::fromRequest($data);

            $article = $this->articleService->createArticle($articleDTO, $user);
            return $this->responseHandler->created($article);
        } catch (ValidationException $e) {
            return $this->responseHandler->validationError($e->getErrors());
        }
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(string $id, Request $request, #[CurrentUser] User $user): ApiResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $articleDTO = ArticleDTO::fromRequest($data);

            $article = $this->articleService->updateArticle($id, $articleDTO, $user);

            if (!$article) {
                return $this->responseHandler->notFound('Article not found or not authorized');
            }

            return $this->responseHandler->success($article, 'Article updated successfully');
        } catch (ValidationException $e) {
            return $this->responseHandler->validationError($e->getErrors());
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(string $id, #[CurrentUser] User $user): ApiResponse
    {
        $success = $this->articleService->deleteArticle($id, $user);

        if (!$success) {
            return $this->responseHandler->notFound('Article not found or not authorized');
        }

        return $this->responseHandler->noContent();
    }
}
