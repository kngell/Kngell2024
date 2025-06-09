<?php

declare(strict_types=1);
class UserProfileHtmlElement extends AbstractHtml
{
    public function __construct(private HtmlBuilder $builder)
    {
    }

    public function display(): string
    {
        /** @var User */
        $user = App::getInstance()->resolve('current.user');
        $html = $this->builder;
        $form = $this->builder->form();
        return $form->method('post')->action('dashboard/edit-prodile')->autocomplete('off')->add(
            $html->tag('div')->class('row')->add(
                $html->tag('div')->class('col-md-3')->add(
                    $html->tag('img')->src($this->media($user ? $user->getMedia() : '#'))->class('profile-photo w_100_p'),
                    $form->input('file')->class('mt_10')->name('media')
                ),
                $html->tag('div')->class('col-md-9')->add(
                    $html->tag('div')->class('mb-4')->add(
                        $form->label('First Name:')->for('first_name'),
                        $form->input('text')->class('form-control')->name('first_name')->value($user->getFirstName())->id('first_name')
                    ),
                    $html->tag('div')->class('mb-4')->add(
                        $form->label('Last Name:')->for('last_name'),
                        $form->input('text')->class('form-control')->name('last_name')->value($user->getLastName())->id('last_name')
                    ),
                    $html->tag('div')->class('mb-4')->add(
                        $form->label('Email:*')->for('email'),
                        $form->input('email')->class('form-control')->name('email')->value($user->getEmail())->id('email')
                    ),
                    $html->tag('div')->class('mb-4')->add(
                        $form->label('Password:*')->for('password'),
                        $form->input('password')->class('form-control')->name('password')->id('password')
                    ),
                    $html->tag('div')->class('mb-4')->add(
                        $form->label('Confirm Password:*')->for('cpassword'),
                        $form->input('password')->class('form-control')->name('cpassword')->id('cpassword')
                    ),
                    $html->tag('div')->class('mb-4')->add(
                        $form->button('submit')->class('btn btn-primary')->content('Update')
                    )
                )
            )
        )->generate();
    }

    // return $html->tag('dl')->class('row')->add(
    //     $html->tag('dt')->content('First Name :')->class('col-2'),
    //     $html->tag('dd')->content($user->getFirstName())->class('col-10', 'text-start'),
    //     $html->tag('dt')->content('Last Name :')->class('col-2'),
    //     $html->tag('dd')->content($user->getLastName())->class('col-10', 'text-start'),
    //     $html->tag('dt')->content('Username :')->class('col-2'),
    //     $html->tag('dd')->content($user->getUserName())->class('col-10', 'text-start'),
    //     $html->tag('dt')->content('Email :')->class('col-2'),
    //     $html->tag('dt')->content($user->getEmail())->class('col-10', 'text-start'),
    //     $html->tag('a')->href('/profile/edit')->content('Edit'),
    //     $html->tag('a')->href('/profile/index')->content('All Users')
    // )->generate();
}