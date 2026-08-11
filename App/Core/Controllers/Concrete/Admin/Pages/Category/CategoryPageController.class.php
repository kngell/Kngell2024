<?php

declare(strict_types=1);

class CategoryPageController extends Controller
{
    public function __construct(
        FormCreatorService $frm,
        private CategoryModel $md,
        private CategoryFormConfigFactory $formFactory,
    ) {
        $this->frm = $frm;
        $this->layout(NavbarType::ADMIN);
    }

    public function add(): string
    {
        $this->pageTitle('Category');

        // return $this->cachePage(
        //     function () {
        $decorated = $this->decorate(FormDecorator::class, $this, [
            'action' => '/admin/category-save/index',
            'factory' => $this->formFactory,
        ]);
        return $this->render('/components/main_form', $decorated->page());
        //     },
        //     ttl: 3600, // 1 hour
        // );
    }

    public function edit(#[Alias(['public_id', 'cat_id'])]string $id): string|Response
    {
        $this->pageTitle('Edit Category Section');
        $action = '/admin/category-save/index';

        list($values, $errors, $files) = $this->getFlashData($action);

        if (empty($values) && isset($id)) {
            $values = $this->md->getById($id)?->asClass();
        }

        $decorator = $this->decorate(FormDecorator::class, $this, [
            'action' => $action,
            'formValues' => $values,
            'formErrors' => $errors,
            'files' => $files,
            'factory' => $this->formFactory,
        ]);
        return $this->render('/components/main_form', $decorator->page());
    }
}