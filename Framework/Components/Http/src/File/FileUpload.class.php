<?php

declare(strict_types=1);

class FileUpload extends FileInformation
{
    private const string UPLOAD_DIR_SRC = SRC . 'assets' . DS . 'img' . DS . 'Upload' . DS;
    private readonly string $originalName;
    private readonly string $mimeType;
    private ErrorFile $uploadError;
    private array $class = ['invalid-feedback'];

    public function __construct(string $path, string $originalName, string $mimeType, int $uploadError)
    {
        $this->originalName = $originalName;
        $this->mimeType = $mimeType;
        $this->uploadError = ErrorFile::from($uploadError);
        parent::__construct($path);
    }

    public function getOriginalName(): string
    {
        return $this->originalName($this->originalName);
    }

    public function getMimeType(): string
    {
        if (empty($this->mime)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            return $finfo->file($this->getPathname());
        }
        return $this->mimeType;
    }

    public function getUploadError(): int
    {
        return $this->uploadError->value;
    }

    public function isValid() : bool
    {
        return $this->uploadError->value === UPLOAD_ERR_OK && is_uploaded_file($this->getPathname());
    }

    public function getOriginalExtension() : string
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION);
    }

    public function guessExtensionFromMimeType() : string|null
    {
        $extension = MimeTypeGuessDelegator::getInstance()->guessExtensionByMimeType($this->mimeType);
        return ArrayUtils::first($extension);
    }

    public function move(string $directory, string|null $name = null) : self
    {
        if (! $this->isValid()) {
            throw new FileException($this->getUploadedErrorMessage());
        }
        $index = 1;
        $name = $name ?? $this->getOriginalName();
        $targetFile = $this->getTargetedFile($directory, $name);
        while (file_exists($targetFile->getPathname())) {
            $targetFile = $this->getTargetedFile($directory, $this->originalName($name, $index));
            $index++;
        }
        $manager = new ImageManager($this->getPathname(), $targetFile->getPathname());
        $move = $manager->resize();
        if (! $move) {
            $erroMsg = strip_tags(error_get_last()['message']);
            throw new FileException("Could not move file {$this->getPathname()} to {$targetFile->getPathname()} ({$erroMsg})");
        }
        // @chmod($targetFile->getPathname(), 0666 & ~umask());
        //$targetFile;
        return $this->getTargetedFile(UPLOAD_DIR . DS . 'images' . DS, $targetFile->getBasename());
    }

    public function getUploadedErrorMessage() : string
    {
        $errorMsg = $this->erroMessage($this->uploadError->getErrMsg());
        if ($this->uploadError === UPLOAD_ERR_INI_SIZE) {
            return sprintf($errorMsg, $this->originalName, (string) ini_get('upload_max_filesize'));
        }
        return str_contains($errorMsg, '%s') ? sprintf($errorMsg, $this->originalName) : $errorMsg;
    }

    public function setError(ErrorFile $error) : void
    {
        $this->uploadError = $error;
    }

    public function getError() : ErrorFile
    {
        return $this->uploadError;
    }

    private function copyToSrc(FileInformation $targetFile) : void
    {
        $dir = self::UPLOAD_DIR_SRC;
        if (FileManager::createDir($dir)) {
            copy($targetFile->getPathname(), $dir . $targetFile->getBasename());
        }
    }

    private function originalName(string $originalName, int|null $index = null) : string
    {
        if (! empty($originalName)) {
            $pathInfo = pathinfo($originalName);
            $base = $index ? $pathInfo['filename'] . '_' . $index : $pathInfo['filename'];
            $base = preg_replace("/[^\w-]/", '_', $base);
            return $base . '.' . $pathInfo['extension'];
        }
        return '';
    }

    private function erroMessage(string $errMsg) : string
    {
        $errMsg = nl2br(htmlspecialchars($errMsg));
        return "<div class='" . implode(' ', $this->class) . "'>" . $errMsg . '</div>';
    }
}