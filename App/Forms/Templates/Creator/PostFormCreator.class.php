<?php

declare(strict_types=1);

class PostFormCreator extends AbstractFormCreator
{
    public function __construct(private HtmlBuilder $builder)
    {
    }

    public function create(string $action): ?FormTemplateInterface
    {
        return match (true) {
            StringUtils::strContainsArrayItem($action, ['update']) => new EditPostForm($this->builder),
            StringUtils::strContainsArrayItem($action, ['create']) => new InsertPostForm($this->builder),
            StringUtils::strContainsArrayItem($action, ['destroy']) => new DeletePostForm($this->builder),
            default => null,
        };
    }
}