<?php

declare(strict_types=1);

class FooterPageController extends Controller
{
    public function __construct(
        private FooterMenuShowModel $columnShowModel,
        private FooterSocialModel $socialModel,
        private FooterAboutModel $aboutModel,
        private FooterPageConfigFactory $pageFactory,
        FormCreatorService $frm,
    ) {
        $this->layout(NavbarType::ADMIN);
        $this->frm = $frm;
    }

    public function index(): string
    {
        $this->pageTitle('Footer');
        $decorated = $this->decorate(
            HtmlPageDecorator::class,
            $this,
            [
                'factory' => $this->pageFactory,
                'adapter' => [
                    new FooterColumnsPaginatedAdapter($this->columnShowModel),
                    new FooterSocialPaginatedAdapter($this->socialModel),
                    new FooterAboutPaginatedAdapter($this->aboutModel),
                ],
            ],
        );

        return $this->render('/footer/footer', $decorated->page());
    }
}