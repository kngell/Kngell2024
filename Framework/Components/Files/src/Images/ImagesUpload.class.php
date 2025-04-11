<?php

declare(strict_types=1);

final class ImagesUpload implements FileUploadInterface
{
    private const array ALLOWED_TYPE = ['JPG', 'PNG', 'JPEG', 'GIF', 'BMP', 'WEBP', 'TIFF'];
    private const string IMG_UPLOAD_DIR = UPLOAD_DIR . 'images' . DS;
    private const int MAX_IMG_SIZE = 5242880; // 5MB
    /**
     * @var FileMap
     */
    private FileMap $files;

    private array $imgErrors = [];
    private string|null $mediaPaths = null;

    public function __construct(Request $request)
    {
        $this->files = $request->getFiles();
    }

    public function proceed(bool $uploadRequired = false): array
    {
        if ($this->files->hasFile('image')) {
            $files = $this->files->all();
            $this->validate($files, $uploadRequired);
            if (empty($this->imgErrors)) {
                $this->save($files);
            }
        } else {
            $this->imgErrors['image'][] = 'Cannot upload image! Is file_uploads enabled in php.ini?';
        }
        return [$this->imgErrors, $this->mediaPaths];
    }

    /**
     * @return string
     */
    public function getMediaPaths(): string|null
    {
        return $this->mediaPaths;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->imgErrors;
    }

    /**
     * @param array $files
     */
    private function save(array $files) : void
    {
        $paths = [];
        foreach ($files as $fileUpload) {
            $target = $fileUpload->move(self::IMG_UPLOAD_DIR, $fileUpload->getOriginalName());
            $paths[] = $target->getPathname();
        }
        $this->mediaPaths = serialize($paths);
    }

    private function validate(array $files, bool $uploadRequired) : void
    {
        /** @var FileUpload $fileUpload */
        foreach ($files as $fileInput => $fileUpload) {
            if (! in_array(strtoupper($fileUpload->getOriginalExtension()), self::ALLOWED_TYPE)) {
                $fileUpload->SetError(ErrorFile::UPLOAD_ERR_FILE_TYPE);
            }
            if ($fileUpload->getSize() > self::MAX_IMG_SIZE) {
                $fileUpload->SetError(ErrorFile::UPLOAD_ERR_FILE_MAX_SIZE);
            }
            if ($fileUpload->getError() !== ErrorFile::UPLOAD_ERR_OK) {
                if ($fileUpload->getError() === ErrorFile::UPLOAD_ERR_NO_FILE && ! $uploadRequired) {
                    continue;
                } else {
                    $this->imgErrors[$fileInput][] = $fileUpload->getUploadedErrorMessage();
                }
            }
        }
    }
}