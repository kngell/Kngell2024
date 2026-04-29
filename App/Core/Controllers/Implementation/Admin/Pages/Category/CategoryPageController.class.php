<?php

declare(strict_types=1);

class CategoryPageController extends Controller
{
    public function __construct(
        FormCreatorService $frm,
        private CategoryModel $md,
    ) {
        $this->frm = $frm;
        $this->layout('admin');
    }

    public function Add(): string
    {
        $this->pageTitle('Category');
        $hero = $this->decorate(CategoryFormDecorator::class, $this, [
            'action' => 'category-save/index',
        ]);
        return $this->render('/components/category', $hero->page());
    }

    public function edit(#[Alias(['public_id', 'cat_id'])]string $id): string|Response
    {
        $this->pageTitle('Edit Category Section');
        $action = 'category-save/index';

        list($values, $errors, $files) = $this->getFlashData($action);

        if (empty($values) && isset($id)) {
            $values = $this->md->getById($id, 'cat_id');
        }

        $decorator = $this->decorate(CategoryFormDecorator::class, $this, [
            'action' => $action,
            'formValues' => $values,
            'formErrors' => $errors,
            'files' => $files,
        ]);
        return $this->render('/components/category', $decorator->page());
    }
}