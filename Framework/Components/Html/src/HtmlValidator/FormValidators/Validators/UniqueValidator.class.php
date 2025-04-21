<?php

declare(strict_types=1);

class UniqueValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = 'This %s already exist';

    public function __construct(private string $display, private mixed $inputValue, private mixed $ruleValue, private Model $md)
    {
    }

    public function validate(): string|bool
    {
        $ignore = false;
        if (! empty($this->inputValue)) {
            $ruleValue = explode('|', $this->ruleValue);
            if (count($ruleValue) === 2 && $ruleValue[1] === 'ignore_current') {
                $ignore = $this->CurrenUserCheck();
            }
            $user = $this->md->all(['email', $this->inputValue]);
            if ($user->rowCount() >= 1 && ! $ignore) {
                return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
            }
        }
        return true;
    }

    private function CurrenUserCheck() : bool
    {
        /** @var User */
        $user = AuthService::currentUser();
        if ($this->inputValue === $user->getEmail()) {
            return true;
        }
        return false;
    }
}