<?php

declare(strict_types=1);

class HeroPageController extends Controller
{
    public function __construct(
        FormCreatorService $frm,
        private HeroModel $md,
        private HtmlTemplatePathInterface $templatePath,
    ) {
        $this->frm = $frm;
        $this->layout('admin');
    }

    public function Add(): string
    {
        $this->pageTitle('Hero Section');

        $decorator = $this->decorate(
            HeroSectionFormDecorator::class,
            $this,
            ['action' => '/hero-section-save/index'],
        );

        return $this->render(
            '/components/hero_section',
            $decorator->page(),
        );
    }

    public function edit(
        #[Alias(['public_id', 'hero_id'])]
        string $id,
    ): string|Response {
        $this->pageTitle('Edit Hero Section');
        $saveAction = '/hero-section-save/index';
        $deleteAction = '/hero-confirm-deletion/confirm';

        [$values, $errors, $files] = $this->getFlashData($saveAction);

        if (empty($values) && isset($id)) {
            $values = $this->md->getById($id, 'hero_id');
        }

        $decorator = $this->decorate(
            HeroSectionFormDecorator::class,
            $this,
            [
                'action' => $saveAction,
                'deleteAction' => $deleteAction,
                'formValues' => $values,
                'formErrors' => $errors,
                'files' => $files,
            ],
        );

        $pageData = $decorator->page();
        $pageData = $this->mergeDeletionModal($pageData, $id);

        return $this->render('/components/hero_section', $pageData);
    }

    private function mergeDeletionModal(
        array $pageData,
        string $id,
    ): array {
        $flashKey = DeletionFlowConfig::flashKeyFor('Hero Section');

        $deleteFlash = $this->flash->peekData($flashKey);

        if (!$deleteFlash || ($deleteFlash['id'] ?? null) !== $id) {
            return $pageData;
        }

        $dto = ConfirmDeletionDTO::fromFlashData(
            flashData: $deleteFlash,
            label: 'Hero Section',
            deleteRoute: '/hero-section-delete/delete',
            cancelRoute: '/hero-confirm-deletion/cancel',
            isVisible: true,
            isAjax: $this->request->isAjax(),
        );

        $modalDecorator = $this->decorate(
            ConfirmDeletionDecorator::class,
            $this,
            [
                'dto' => $dto,
            ],
        );

        return array_merge($pageData, $modalDecorator->page());
    }
}