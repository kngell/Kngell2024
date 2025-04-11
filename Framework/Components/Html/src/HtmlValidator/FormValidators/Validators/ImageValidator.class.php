<?php

declare(strict_types=1);

class ImageValidator extends AbstractValidator
{
    private const array ERROR_MESSAGE = [
        UPLOAD_ERR_INI_SIZE => "The file '%s' exceeds your upload_max_filesize init directive (limit is '%d' kbis",
        UPLOAD_ERR_FORM_SIZE => "The file '%s' exceeds the upload limit defined in your form",
        UPLOAD_ERR_PARTIAL => "The file '%s' was partially uploaded",
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_CANT_WRITE => "The file '%s' could not be written on disk",
        UPLOAD_ERR_NO_TMP_DIR => 'The could not be uploaded. Missing temp directory.',
        UPLOAD_ERR_EXTENSION => 'File upload was stooped by php extension',
    ];

    public function __construct(private string $display, private mixed $inputValue, private mixed $ruleValue)
    {
    }

    public function validate(): string|bool
    {
        if (! preg_match('/^[A-Za-z0-9_-]*$/', $this->inputValue)) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }
        return true;
    }
}