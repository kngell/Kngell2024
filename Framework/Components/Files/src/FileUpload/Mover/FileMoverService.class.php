<?php

declare(strict_types=1);

class FileMoverService implements FileMoverInterface
{
    public function __construct(
        private FileManager $fileManager,
    ) {
    }

    public function moveUploadedFile(
        FileUpload $upload,
        string $targetDirectory,
        ?string $filename = null,
        ?FileProcessorInterface $processor = null,
    ): FileInformation {
        if (!$upload->isValid()) {
            throw new FileException(
                'Cannot move invalid file: ' . $upload->getUploadErrorDescription(),
            );
        }

        $this->fileManager->ensureDirectoryExists($targetDirectory);

        $filename = $filename ?? $upload->getSafeFilename();
        $targetPath = $this->generateUniquePath($targetDirectory, $filename);

        return $this->performMove($upload, $targetPath, $processor);
    }

    public function moveFile(
        FileInformation $source,
        string $targetDirectory,
        ?string $filename = null,
    ): FileInformation {
        if (!$source->isFile()) {
            throw new FileException("Source is not a file: '{$source->getPathname()}'");
        }

        $this->fileManager->ensureDirectoryExists($targetDirectory);

        $filename = $filename ?? $source->getFilename();
        $targetPath = $this->generateUniquePath($targetDirectory, $filename);

        $this->fileManager->move($source->getPathname(), $targetPath);

        return new FileInformation($targetPath);
    }

    public function makeFilePermanent(string $tempPath, string $permanentDirectory): string
    {
        $results = $this->makeFilesPermanent([$tempPath], $permanentDirectory);
        return $results[0] ?? throw new FileException("Failed to make file permanent: {$tempPath}");
    }

    public function makeFilesPermanent(array $tempPaths, string $permanentDirectory): array
    {
        // Normalize the permanent directory path
        $permanentDirectory = rtrim($permanentDirectory, DIRECTORY_SEPARATOR);
        $this->fileManager->ensureDirectoryExists($permanentDirectory);

        $permanentPaths = [];
        $errors = [];

        foreach ($tempPaths as $tempPath) {
            if (!$this->fileManager->exists($tempPath)) {
                $errors[] = "Temporary file not found: {$tempPath}";
                continue;
            }

            $filename = basename($tempPath);
            $permanentPath = $this->generateUniquePath($permanentDirectory, $filename);

            try {
                $this->fileManager->move($tempPath, $permanentPath);
                $permanentPaths[] = $permanentPath;
            } catch (FileException $e) {
                $errors[] = "Failed to move file: {$tempPath} to {$permanentPath} - " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            throw new FileException('Failed to make files permanent: ' . implode(', ', $errors));
        }

        return $permanentPaths;
    }

    public function deletePermanentFile(string $path): void
    {
        if (!$this->fileManager->exists($path)) {
            return;
        }
        try {
            $this->fileManager->delete($path);
        } catch (FileException $e) {
            throw new FileException("Failed to delete file at path: {$path} - " . $e->getMessage());
        }
    }

    private function generateUniquePath(string $directory, string $filename): string
    {
        // Normalize paths
        $directory = rtrim($directory, DIRECTORY_SEPARATOR);
        $filename = ltrim($filename, DIRECTORY_SEPARATOR);

        $pathinfo = pathinfo($filename);
        $baseName = $pathinfo['filename'];
        $extension = $pathinfo['extension'] ?? '';

        $counter = 1;
        $targetPath = $directory . DIRECTORY_SEPARATOR . $filename;

        while ($this->fileManager->exists($targetPath)) {
            $newFilename = $baseName . '_' . $counter;
            if ($extension) {
                $newFilename .= '.' . $extension;
            }
            $targetPath = $directory . DIRECTORY_SEPARATOR . $newFilename;
            $counter++;

            // Safety check to prevent infinite loops
            if ($counter > 1000) {
                throw new FileException("Could not generate unique filename for: {$filename} in {$directory}");
            }
        }

        return $targetPath;
    }

    private function performMove(
        FileUpload $upload,
        string $targetPath,
        ?FileProcessorInterface $processor = null,
    ): FileInformation {
        if ($processor && $processor->supports($upload)) {
            $processedPath = $processor->process($upload, $targetPath);
            if ($processedPath !== null) {
                if (!$this->fileManager->exists($processedPath)) {
                    throw new FileException("Processor claimed to move file but file not found: {$processedPath}");
                }
                return new FileInformation($processedPath);
            }
        }

        $this->fileManager->move($upload->getPathname(), $targetPath);

        return new FileInformation($targetPath);
    }
}