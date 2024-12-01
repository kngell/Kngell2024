<?php

declare(strict_types=1);

class UserOrdersPaginationHTMLElement extends AbstractHTMLElement
{
    private AbstractPagination $pagination;

    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
        $this->pagination = $params['pagination'];
    }

    public function display(): array
    {
        list($previous, $next, $links) = $this->pagination->display();
        $template = str_replace('{{previsouLink}}', $previous, $this->getTemplate('paginPath'));
        $template = str_replace('{{links}}', $links, $template);
        $template = str_replace('{{nextLink}}', $next, $template);
        return ['pagination' => $template];
    }
}