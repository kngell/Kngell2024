<?php

declare(strict_types=1);

enum ErrorFile: int
{
    case UPLOAD_ERR_INI_SIZE = UPLOAD_ERR_INI_SIZE;
    case UPLOAD_ERR_FORM_SIZE = UPLOAD_ERR_FORM_SIZE;
    case UPLOAD_ERR_PARTIAL = UPLOAD_ERR_PARTIAL;
    case UPLOAD_ERR_NO_FILE = UPLOAD_ERR_NO_FILE;
    case UPLOAD_ERR_CANT_WRITE = UPLOAD_ERR_CANT_WRITE;
    case UPLOAD_ERR_NO_TMP_DIR = UPLOAD_ERR_NO_TMP_DIR;
    case UPLOAD_ERR_EXTENSION = UPLOAD_ERR_EXTENSION;
    case UPLOAD_ERR_OK = UPLOAD_ERR_OK;
    case UPLOAD_ERR_FILE_TYPE = 1000;
    case UPLOAD_ERR_FILE_MAX_SIZE = 1001;
    case UPLOAD_ERR_FILE_MIME = 1002;
    case UPLOAD_ERR_FILE_MOVE = 1003;
    case UPLOAD_ERR_FILE_CREATE = 1004;
    case UPLOAD_ERR_FILE_DELETE = 1005;
    case UPLOAD_ERR_FILE_NOT_FOUND = 1006;

    private const array UPLOAD_ERROR_MESSAGES = [
        UPLOAD_ERR_INI_SIZE => "The file '%s' exceeds your upload_max_filesize init directive (limit is '%d' kbites)",
        UPLOAD_ERR_FORM_SIZE => "The file '%s' exceeds the upload limit defined in your form",
        UPLOAD_ERR_PARTIAL => "The file '%s' was partially uploaded",
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_CANT_WRITE => "The file '%s' could not be written on disk",
        UPLOAD_ERR_NO_TMP_DIR => 'The could not be uploaded. Missing temp directory.',
        UPLOAD_ERR_EXTENSION => 'File upload was stooped by php extension',
        1000 => 'This file type is not supported',
        1001 => 'File size exceeded! You cannot load more than 5MB files.',
        1002 => 'This file type is not supported',
        1003 => 'The file could not be moved',
        1004 => 'The file could not be created',
        1005 => 'The file could not be deleted',
        1006 => 'The file was not found',
    ];

    public function getErrMsg(): string
    {
        return self::UPLOAD_ERROR_MESSAGES[$this->value] ?? 'Unknown error';
    }

    //  public function getMessage(): string
    //  {
    //      return match ($this) {
    //          self::UPLOAD_ERR_INI_SIZE => "The file '%s' exceeds your upload_max_filesize init directive (limit is '%d' kbites)",
    //          self::UPLOAD_ERR_FORM_SIZE => "The file '%s' exceeds the upload limit defined in your form",
    //          self::UPLOAD_ERR_PARTIAL => "The file '%s' was partially uploaded",
    //          self::UPLOAD_ERR_NO_FILE => 'No file was uploaded',
    //          self::UPLOAD_ERR_CANT_WRITE => "The file '%s' could not be written on disk",
    //          self::UPLOAD_ERR_NO_TMP_DIR => 'The could not be uploaded. Missing temp directory.',
    //          self::UPLOAD_ERR_EXTENSION => 'File upload was stooped by php extension',
    //      };
    //  }
}