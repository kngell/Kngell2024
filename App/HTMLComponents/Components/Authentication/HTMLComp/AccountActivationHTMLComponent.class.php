<?php

declare(strict_types=1);
class AccountActivationHTMLComponent extends AbstractHTMLComponent
{
    private string $section = 'userAccountActivation';
    private bool $accountOk;

    public function __construct(bool $accountOk)
    {
        parent::__construct();
        $this->accountOk = $accountOk;
    }

    public function display(): array
    {
        // $childs = $this->children->all();
        // foreach ($childs as $child) {
        //     [$property,$html] = $child->display();
        //     if (property_exists($this, $property)) {
        //         $this->{$property} = $html;
        //     }
        // }
        return [$this->section => $this->accountActivationResult()];
    }

    private function accountActivationResult() : string
    {
        if ($this->accountOk) {
            return 'You have successfully activated your account';
        }
        return 'Activation failed';
    }
}