<?php

declare(strict_types=1);

class PostFormCreator extends AbstractFormCreator
{
    public function __construct(private FormBuilder $formBuilder)
    {
    }

    public function create(string $action): ?FormTemplateInterface
    {
        return match (true) {
            StringUtils::strContainsArrayItem($action, ['update']) => new EditPostForm($this->formBuilder),
            StringUtils::strContainsArrayItem($action, ['create']) => new InsertPostForm($this->formBuilder),
            StringUtils::strContainsArrayItem($action, ['destroy']) => new DeletePostForm($this->formBuilder),
            default => null,
        };
    }
}