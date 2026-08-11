<?php

declare(strict_types=1);

class SocialLinksService
{
    private array $links = [];
    private string $configPath;

    public function __construct(?string $configPath = null)
    {
        $this->configPath = $configPath ?? APP . 'Config' . DS . 'social.php';
        $this->loadLinks();
    }

    public function getActiveLinks(): array
    {
        return array_filter($this->links, fn ($link) => $link['active'] ?? true);
    }

    public function getLink(string $platform): ?array
    {
        return $this->links[$platform] ?? null;
    }

    private function loadLinks(): void
    {
        if (file_exists($this->configPath)) {
            $this->links = require $this->configPath;
        } else {
            $this->links = $this->getDefaultLinks();
        }
    }

    private function getDefaultLinks(): array
    {
        return [
            'twitter' => [
                'name' => 'Twitter',
                'url' => 'https://twitter.com/yourbrand',
                'icon' => 'icon-twitter',
                'icon_class' => ['twitter'],
                'active' => true,
            ],
            'facebook' => [
                'name' => 'Facebook',
                'url' => 'https://facebook.com/yourbrand',
                'icon' => 'icon-facebook',
                'icon_class' => ['facebook'],
                'active' => true,
            ],
            'tiktok' => [
                'name' => 'Facebook',
                'url' => 'https://tiktok.com/yourbrand',
                'icon' => 'icon-tiktok',
                'icon_class' => ['tiktok'],
                'active' => true,
            ],
            // 'instagram' => [
            //     'name' => 'Instagram',
            //     'url' => 'https://instagram.com/yourbrand',
            //     'icon' => 'icon-instagram',
            //     'icon_class' => ['instagram'],
            //     'active' => true,
            // ],
            'youtube' => [
                'name' => 'YouTube',
                'url' => 'https://youtube.com/yourbrand',
                'icon' => 'icon-youtube',
                'icon_class' => ['youtube'],
                'active' => true, // Disabled until ready
            ],
        ];
    }
}