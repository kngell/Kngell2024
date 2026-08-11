<?php

declare(strict_types=1);

interface PageLayoutInterface
{
    public function buildLayout(HtmlRegularSectionManager $sectionManager, HtmlBuilder $builder, AbstractHtml $htmlInstance, PageConfig $config, array $entities, array $pagination = []): array;
}