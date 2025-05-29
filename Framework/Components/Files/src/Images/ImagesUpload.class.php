<?php

//Todo : renamming user files
declare(strict_types=1);

final class ImagesUpload implements FileUploadInterface
{
    private const array ALLOWED_TYPE = ['JPG', 'PNG', 'JPEG', 'GIF', 'BMP', 'WEBP', 'TIFF'];
    private const string IMG_UPLOAD_DIR = SRC . 'Upload' . DS . 'images' . DS; //UPLOAD_DIR;
    private const int MAX_IMG_SIZE = 5242880; // 5MB
    private const string FILE_NAME = 'uploadedFiles';
    /**
     * @var FileMap
     */
    private FileMap $files;

    private array $imgErrors = [];
    private string|null $mediaPaths = null;
    private string|null $name;

    public function __construct(Request $request)
    {
        $this->files = $request->getFiles();
        $this->name = $this->files->getName();
    }

    public function proceed(bool $uploadRequired = false): void
    {
        try {
            if ($this->files->hasFile($this->name)) {
                $files = $this->files->all();
                $this->validate($files, $uploadRequired);
                if (empty($this->imgErrors) && ! empty($files)) {
                    $this->save($files);
                }
            } else {
                $this->imgErrors[self::FILE_NAME][] = 'Cannot upload image! Is file_uploads enabled in php.ini?';
            }
        } catch (Throwable $th) {
            throw $th;
        }
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
            $target = $fileUpload->move(self::IMG_UPLOAD_DIR);
            $paths[] = str_replace(SRC, SCRIPT . DS, $target->getPathname());
        }
        $this->mediaPaths = serialize($paths);
    }

    private function validate(array &$files, bool $uploadRequired) : void
    {
        /** @var FileUpload $fileUpload */
        foreach ($files as $fileInput => $fileUpload) {
            if (empty($fileUpload->getOriginalName())) {
                unset($files[$fileInput]);
                continue;
            }
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