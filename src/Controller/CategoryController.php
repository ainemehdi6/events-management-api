<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CategoryDTO;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/categories', name: 'api_categories_')]
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryService $categoryService,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $categories = $this->categoryRepository->findAll();

        return $this->json(
            $categories,
            Response::HTTP_OK,
            [],
            ['groups' => ['category:read']]
        );
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var CategoryDTO $categoryDTO */
        $categoryDTO = $this->serializer->deserialize(
            $request->getContent(),
            CategoryDTO::class,
            'json'
        );

        $errors = $this->validator->validate($categoryDTO);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }

            return $this->json(
                ['errors' => $errorMessages],
                Response::HTTP_BAD_REQUEST
            );
        }

        $category = $this->categoryService->createCategory($categoryDTO);

        return $this->json(
            $category,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['category:read']]
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Uuid $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            return $this->json(
                ['error' => 'Category not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->json(
            $category,
            Response::HTTP_OK,
            [],
            ['groups' => ['category:read']]
        );
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Uuid $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            return $this->json(
                ['error' => 'Category not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        /** @var CategoryDTO $categoryDTO */
        $categoryDTO = $this->serializer->deserialize(
            $request->getContent(),
            CategoryDTO::class,
            'json'
        );

        $errors = $this->validator->validate($categoryDTO);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }

            return $this->json(
                ['errors' => $errorMessages],
                Response::HTTP_BAD_REQUEST
            );
        }

        $updatedCategory = $this->categoryService->updateCategory($category, $categoryDTO);

        return $this->json(
            $updatedCategory,
            Response::HTTP_OK,
            [],
            ['groups' => ['category:read']]
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Uuid $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            return $this->json(
                ['error' => 'Category not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $this->categoryService->deleteCategory($category);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
