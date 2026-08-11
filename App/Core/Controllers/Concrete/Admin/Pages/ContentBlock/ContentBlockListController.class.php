<?php

declare(strict_types=1);
final class ContentBlockListController extends Controller
{
    public function __construct(
        private readonly HtmlSectionPresentationService $presenter,
        private readonly ContentBlockModelFactory $modelFactory,
    ) {
        $this->layout(NavbarType::ADMIN);
    }

    public function index(string $type): string
    {
        $this->pageTitle('Content Block List');
        $blockType = BlockType::tryFrom($type);
        if (!$blockType) {
            throw new InvalidArgumentException('Invalid block type: ' . $type);
        }

        $list = $this->decorate(
            ListDecorator::class,
            $this,
            [
                'factory' => new ContentBlockTableConfigFactory(
                    presenter: $this->presenter,
                    type: $type,
                ),
                'adapter' => new ContentBlockPaginatedAdapter(
                    model: $this->modelFactory->createForType($blockType),
                    blockType: $type,
                ),
            ],
        );

        return $this->render('/table-list', $list->page());
    }
}