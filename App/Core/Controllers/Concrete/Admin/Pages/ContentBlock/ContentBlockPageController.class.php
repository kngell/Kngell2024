<?php

declare(strict_types=1);

class ContentBlockPageController extends Controller
{
    public function __construct(
        FormCreatorService $frm,
        private ContentBlockModel $md,
        private ContentBlockFormFactory $factory,
    ) {
        $this->frm = $frm;
        $this->layout(NavbarType::ADMIN);
    }

    public function add(string $type): string
    {
        $blockType = $this->getBlockType($type);

        $this->pageTitle($blockType->getPageTitle() ?? 'Add Content Block');

        // Store in session for consistency across requests
        $this->session->set('current_block_type', $type);
        $this->session->set('current_block_id', null);

        $decorator = $this->decorate(
            FormDecorator::class,
            $this,
            [
                'action' => ContentBlockLinks::getSaveRoute($blockType),
                'factory' => $this->factory->create($blockType),
            ],
        );

        return $this->render($blockType->getView(), $decorator->page());
    }

    public function edit(string $type, string $id): string|Response
    {
        $blockType = $this->getBlockType($type);
        $this->pageTitle($blockType->getEditTitle() ?? 'Edit Content Block');

        [$values, $errors, $files] = $this->getFlashData(ContentBlockLinks::getSaveRoute($blockType));
        if (!empty($values)) {
            $values = $this->mergeValues($values, $files, $blockType);
            $files = [];
        }

        if (empty($values) && isset($id)) {
            $values = $this->md->getById($id, 'id')?->asClass() ?? [];
        }

        if (empty($values)) {
            $this->flash->add('Content block no longer available', FlashType::WARNING);
            return $this->redirect(ContentBlockLinks::getListRoute($blockType));
        }

        $decorator = $this->decorate(
            FormDecorator::class,
            $this,
            [
                'action' => ContentBlockLinks::getSaveRoute($blockType),
                'deleteAction' => ContentBlockLinks::getDeConfirmDeletionRoute(),
                'formValues' => $values,
                'formErrors' => $errors,
                'files' => $files,
                'factory' => $this->factory->create($blockType),
                'blockType' => $type,
                'cancelUrl' => ContentBlockLinks::getListRoute($blockType),
            ],
        );

        $pageData = $decorator->page();
        $pageData = $this->mergeDeletionModal($pageData, $id);

        return $this->render($blockType->getView(), $pageData);
    }

    private function mergeValues(array $formValues, array $files, BlockType $blockType): array
    {
        return match ($blockType) {
            BlockType::HERO,BlockType::SMALL_BANNER => $this->heroValues($formValues, $files),
            BlockType::BIG_BANNER => $this->bigBannerValues($formValues, $files),
            BlockType::SUMMER_BANNER => $this->handleSummerBanner($formValues, $files)
        };
    }

    private function handleSummerBanner(array $formValues, array $files): array
    {
        $expandedFiles = ArrayUtils::expandFromKeys($files);
        return array_merge($formValues, $expandedFiles);
    }

    private function bigBannerValues(array $formValues, array $files): array
    {
        $formValues['block_metadata']['image']['url'] = $files['block_metadata'];
        return $formValues;
    }

    private function heroValues(array $formValues, array $files): array
    {
        $formValues['block_metadata']['image']['url'] = $files['block_metadata'][0];
        return $formValues;
    }

    private function getBlockType(string $type): BlockType
    {
        $blockType = BlockType::tryFrom($type);

        if ($blockType === null) {
            throw new InvalidArgumentException('Invalid block type provided');
        }

        return $blockType;
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
            deleteRoute: '/admin/hero-section-delete/delete',
            cancelRoute: '/admin/hero-confirm-deletion/cancel',
            isVisible: true,
            isAjax: $this->request->isAjax(),
        );

        $modalDecorator = $this->decorate(
            FormDecorator::class,
            $this,
            [
                'dto' => $dto,
            ],
        );

        return array_merge($pageData, $modalDecorator->page());
    }
}