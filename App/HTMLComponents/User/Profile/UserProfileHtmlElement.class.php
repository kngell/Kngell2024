<?php

declare(strict_types=1);
class UserProfileHtmlElement extends AbstractHtml
{
    public function __construct(private HtmlBuilder $builder)
    {
    }

    public function display(): string
    {
        $user = AuthService::currentUser();
        $html = $this->builder;
        return $html->tag('dl')->class(['row'])->add(
            $html->tag('dt')->content('First Name :')->class(['col-2']),
            $html->tag('dd')->content($user->getFirstName())->class(['col-10', 'text-start']),
            $html->tag('dt')->content('Last Name :')->class(['col-2']),
            $html->tag('dd')->content($user->getLastName())->class(['col-10', 'text-start']),
            $html->tag('dt')->content('Username :')->class(['col-2']),
            $html->tag('dd')->content($user->getUserName())->class(['col-10', 'text-start']),
            $html->tag('dt')->content('Email :')->class(['col-2']),
            $html->tag('dt')->content($user->getEmail())->class(['col-10', 'text-start']),
            $html->tag('a')->href('/profile/edit')->content('Edit')
        )->generate();
    }
}