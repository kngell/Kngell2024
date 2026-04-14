<?php

declare(strict_types=1);

class HeroPageController extends Controller
{
    public function __construct(
        FormCreatorService $frm,
        private HeroModel $md,
    ) {
        $this->frm = $frm;
        $this->layout('admin');
    }

    public function Add(): string
    {
        $this->pageTitle('Hero Section');
        $hero = new HeroSectionFormDecorator(
            controller: $this,
            action:'hero-section-save/index',
        );
        return $this->render('/components/hero_section', $hero->page());
    }

    public function heroEdit(#[Alias(['public_id', 'hero_id'])]string $id): string|Response
    {
        $this->pageTitle('Edit Hero Section');
        $action = 'hero-section-save/index';

        list($values, $errors, $files) = $this->getFlashData($action);

        if (empty($values) && isset($id)) {
            $values = $this->md->getById($id, 'hero_id');
        }

        $decorator = new HeroSectionFormDecorator(
            controller: $this,
            action: $action,
            formValues: $values,
            formErrors:$errors,
            files: $files,
        );
        return $this->render('/components/hero_section', $decorator->page());
    }
}