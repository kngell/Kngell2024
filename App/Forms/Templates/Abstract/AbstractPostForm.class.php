<?php

declare(strict_types=1);

abstract readonly class AbstractPostForm extends AbstractTemplateForm
{
    /**
     * @return AbstractHtmlComponent[]
     */
    protected function formElements(FormBuilder $form) : array
    {
        $array = [];
        $array[] = $form->tag('div')->class(['form-group'])->style(['margin-bottom: 20px'])->add(
            $form->label('Title')->for('title'),
            $form->input('text')->name('title')->id('title')->class(['form-control'])
        );
        $array[] = $form->tag('div')->class(['form-group'])->style(['margin-bottom: 20px'])->add(
            $form->label('Content')->for('editor'),
            $form->textArea()->name('content')->id('editor')->class(['form-control'])->rows(10)
        );
        $array[] = $form->button()->type('submit')->class(['btn', 'btn-primary', 'w-100'])->content('Save');
        return $array;
    }
}