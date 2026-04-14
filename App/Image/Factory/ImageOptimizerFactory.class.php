<?php

declare(strict_types=1);

class ImageOptimizerFactory
{
    public function __construct(
        private FileMetadataService $metadataService,
        private ImageCacheFactory $imageCacheFactory,
        private array $defaultBreakpoints = [640, 1080, 1920, 2560],
    ) {
    }

    public function create(array $config = []): ImageOptimizer
    {
        $imageCache = $this->imageCacheFactory->create('images');

        $optimizer = new ImageOptimizer(
            $this->metadataService,
            $imageCache,
            $config['breakpoints'] ?? $this->defaultBreakpoints,
        );

        if ($config['use_gd'] ?? true) {
            $optimizer->addFileManipulator(new GdManipulator());
        }

        if ($config['use_imagick'] ?? false) {
            // $optimizer->addFileManipulator(new ImagickManipulator());
        }

        return $optimizer;
    }

    /**
     * Create optimizer with a specific cache name.
     */
    public function createWithCache(string $cacheName, array $config = []): ImageOptimizer
    {
        $imageCache = $this->imageCacheFactory->create($cacheName);

        $optimizer = new ImageOptimizer(
            $this->metadataService,
            $imageCache,
            $config['breakpoints'] ?? $this->defaultBreakpoints,
        );

        if ($config['use_gd'] ?? true) {
            $optimizer->addFileManipulator(new GdManipulator());
        }

        return $optimizer;
    }
}