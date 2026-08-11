<?php

declare(strict_types=1);

controller
{
    use AjaxResponseTrait;

    public function __construct(
        protected FlashInterface $flash,
        protected FlashRenderer $flashRenderer,
        private CategoryService $categoryService,
    ) {
        parent::__construct();
    }

    /**
     * Standard server-rendered action — flash via session, redirect.
     */
    public function update(): Response
    {
        $isAjax = $this->request->isAjax();
        $dto    = CategoryUpdateDTO::fromRequest($this->request);

        try {
            $category = $this->categoryService->update($dto);

            // Multiple flash messages can stack in one request
            $this->flash->add(
                'Category updated successfully.',
                FlashType::SUCCESS,
                [
                    'title'    => 'Saved',
                    'duration' => 4000,
                ],
            );

            // Conditional warning
            if ($category->hasOrphanedProducts()) {
                $this->flash->add(
                    sprintf('%d products are now uncategorized.', $category->orphanedCount()),
                    FlashType::WARNING,
                    [
                        'title'       => 'Action needed',
                        'duration'    => 8000,
                        'dismissible' => true,
                    ],
                );
            }

            return $this->redirect('/admin/categories');

        } catch (ValidationException $e) {
            // Form errors with old input
            $this->flash->addFormData(
                $this->request->getUri(),
                $this->request->getPost(),
                $e->getErrors(),
            );

            return $this->respondError(
                $isAjax,
                'Please fix the errors below.',
                '/admin/categories/edit/' . $dto->id,
                FlashType::WARNING,
                HttpStatusCode::HTTP_UNPROCESSABLE_ENTITY,
                flashOptions: [
                    'title' => 'Validation Failed',
                ],
            );

        } catch (Exception $e) {
            error_log('Category update exception: ' . $e->getMessage());

            return $this->respondError(
                $isAjax,
                'An unexpected error occurred.',
                '/admin/categories',
                FlashType::DANGER,
                HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
                flashOptions: [
                    'title'    => 'Error',
                    'duration' => null, // sticky
                ],
            );
        }
    }

    /**
     * AJAX-only action — embed flash data into JSON response.
     * Client side renders it via FlashMessage JS component.
     */
    public function quickToggle(): Response
    {
        try {
            $id = (int) $this->request->getPost('id');
            $this->categoryService->toggleActive($id);

            // Build flash DTO directly — no session involvement
            $flashDto = FlashMessageDTO::from(
                type:     FlashType::SUCCESS,
                message:  'Category status toggled.',
                duration: 3000,
            );

            return new JsonResponse([
                'success' => true,
                'flash'   => $flashDto->toArray(),
            ]);

        } catch (Exception $e) {
            $flashDto = FlashMessageDTO::from(
                type:     FlashType::DANGER,
                message:  'Failed to toggle category.',
                title:    'Error',
                duration: null,
            );

            return new JsonResponse([
                'success' => false,
                'flash'   => $flashDto->toArray(),
            ], HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Hybrid AJAX — return rendered flash HTML to inject into the page.
     */
    public function bulkArchive(): Response
    {
        $ids = $this->request->getPost('ids', []);
        $count = $this->categoryService->archiveMany($ids);

        // Use FlashRenderer to produce ready-to-inject HTML
        $flashHtml = $this->flashRenderer->renderMessages([
            FlashMessageDTO::from(
                type:     FlashType::SUCCESS,
                message:  sprintf('%d categories archived.', $count),
                title:    'Bulk Archive Complete',
                duration: 5000,
            ),
        ]);

        return new JsonResponse([
            'success'    => true,
            'flash_html' => $flashHtml,
            'count'      => $count,
        ]);
    }

    /**
     * Show form with potential validation errors from previous request.
     */
    public function edit(int $id): Response
    {
        $category = $this->categoryService->findById($id);

        // Pull old form data if a previous validation failed
        $formData = $this->flash->getFormData($this->request->getUri());

        return $this->view->render('categories/edit', [
            'category' => $category,
            'values'   => $formData['values'] ?? [],
            'errors'   => $formData['errors'] ?? [],
            // Note: flash messages render automatically in layout via FlashRenderer
        ]);
    }
}