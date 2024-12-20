<?php

declare(strict_types=1);

class FormBuilderTodo extends AbstractForm
{
    private Token $token;
    private FormBuilder $form;

    public function __construct(Token $token)
    {
        $this->token = $token;
    }

    public function makeForm(): string
    {
        return $this->form->makeForm();
    }

    /**
     * @return FormBuilder
     */
    public function form() : FormBuilder
    {
        $this->form = new FormBuilder($this->token);
        return $this->form;
    }

    public function input(string $type) : AbstractInput
    {
        return match (strtolower($type)) {
            'text' => new TextType(),
            'radio' => new RadioType(),
            'hidden' => new HiddenType(),
            'email' => new EmailType()
        };
    }

    public function label(string|null $message = null) : LabelElement
    {
        return new LabelElement($message);
    }

    public function textArea() : TextAreaElement
    {
        return new TextAreaElement();
    }

    public function select() : SelectElement
    {
        return new SelectElement($this->token);
    }

    public function button() : ButtonElement
    {
        return new ButtonElement();
    }

    public function htmlTag(string $tag) : CustomHtmlElement
    {
        return new CustomHtmlElement($tag);
    }

    public function wrapper(string $tag) : FormElementWrapper
    {
        return new FormElementWrapper($tag);
    }
}