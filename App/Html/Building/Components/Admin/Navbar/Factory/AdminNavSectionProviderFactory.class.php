<?php

declare(strict_types=1);

class AdminNavSectionProviderFactory implements SectionProviderFactoryInterface
{
    public function __construct(
        private IconBuilder $iconBuilder,
        private Request $request,
        private NavigationConfigParser $parser,
        private HtmlEscaper $escaper,
    ) {
    }

    public function create(): SectionProviderInterface
    {
        return new AdminNavSectionProvider(
            $this->iconBuilder,
            $this->request,
            $this->parser,
            $this->escaper,
        );
    }
}