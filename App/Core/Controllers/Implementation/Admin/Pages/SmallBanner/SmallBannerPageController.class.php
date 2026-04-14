<?php

declare(strict_types=1);

class SmallBannerPageController extends Controller
{
    public function __construct(
        FormCreatorService $frm,
        private SmallBannerShowModel $md,
    ) {
        $this->frm = $frm;
        $this->layout('admin');
    }

    public function add(): string
    {
        $this->pageTitle('Small Banner Section');
        $action = 'small-banner-save/index';
        $decorated = new SmallBannerDecorator(
            controller: $this,
            action: $action,
        );
        return $this->render('/components/small_banner', $decorated->page());
    }

    public function edit(#[Alias(['public_id', 'sm_banner_id'])]string $id): string|Response
    {
        $this->pageTitle('Edit Small Banner Section');
        $action = 'small-banner-save/index';

        list($values, $errors, $files) = $this->getFlashData($action);

        if (empty($values) && isset($id)) {
            $values = $this->md->getById($id, 'sm_banner_id');
        }

        $decorator = new SmallBannerDecorator(
            controller: $this,
            action: $action,
            formValues: $values,
            formErrors:$errors,
            files: $files,
        );
        return $this->render('/components/small_banner', $decorator->page());
    }
}