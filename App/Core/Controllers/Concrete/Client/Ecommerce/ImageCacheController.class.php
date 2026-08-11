<?php

declare(strict_types=1);

class ImageCacheController extends Controller
{
    public function serve(string $path): Response
    {
        $path = str_replace(['..', './', '\\'], '', $path);
        $fullPath = STORAGE . 'cache/images/' . $path;

        if (!file_exists($fullPath) || !is_file($fullPath)) {
            http_response_code(404);
            echo 'Image not found';
            exit;
        }

        // Get mime type
        $mime = mime_content_type($fullPath);

        // Set aggressive caching headers
        header("Content-Type: $mime");
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

        // Output file
        readfile($fullPath);
        return $this->response();
    }
}