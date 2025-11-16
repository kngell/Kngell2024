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
        $array[] = $form->tag('div')->class('form-group')->style(['margin-bottom: 20px'])->add(
            $form->label('Title')->for('title'),
            $form->input('text')->name('title')->id('title')->class('form-control')
        );
        $array[] = $form->tag('div')->class('form-group')->style(['margin-bottom: 20px'])->add(
            $form->label('Date')->for('date'),
            $form->input('date')->name('created_at')->id('date')->class('form-control')
        );
        $array[] = $form->tag('div')->class('form-group')->style(['margin-bottom: 20px'])->add(
            $form->label('Content')->for('editor'),
            $form->textArea()->name('content')->id('editor')->class('form-control')->rows(10)
        );
        $array[] = $this->dropzone($form);
        $array[] = $form->button()->type('submit')->class('btn', 'btn-primary', 'w-100')->content('Save');
        return $array;
    }

    private function dropzone(FormBuilder $form) :AbstractHtmlComponent
    {
        return $form->tag('section')->class('dropzone-container')->add(
            $form->tag('div')->class('dropzone')->id('dropzone')->add(
                $form->label('Upload your files:')->for('dropzone__input')->class('dropzone__label'),
                $form->input('file')->name('uploadedFiles')->id('dropzone__input')->class('dropzone__input')->multiple(true),
                $form->tag('div')->class('dropzone__before-upload')->add(
                    $form->tag('div')->class('dropzone__before-upload--icon')->add(
                        $form->tag('i')->class('fa-solid fa-cloud-arrow-up')
                    ),
                    $form->tag('div')->class('dropzone__before-upload--text')->add(
                        $form->tag('h4')->class('dropzone-heading')->content('Drag and drop your files here or <br>click to upload'),
                        $form->tag('p')->class('dropzone-paragraph')->content('Supports&nbsp;:&nbsp;jpg,&nbsp;jpeg,&nbsp;png,&nbsp;gif,&nbsp;bmp,&nbsp;webp,&nbsp;tiff')
                    )
                ),
                $form->tag('div')->class('dropzone__after-upload')->add(
                    $form->tag('div')->class('dropzone__after-upload--btn-clear')->content('&times;'),
                    $form->tag('img')->src('#')->alt('')->class('dropzone__after-upload--img-preview')
                )
            )
        );
    }
}
