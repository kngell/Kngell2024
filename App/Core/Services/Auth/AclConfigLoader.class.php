<?php

declare(strict_types=1);

final class AclConfigLoader
{
    private const CACHE_KEY = 'acl_config';
    private const CACHE_TTL = 3600;

    private array $config = [];
    private ?string $configPath = null;
    private CacheInterface $cache;

    public function __construct(
        CacheInterface $cache,
        ?string $configPath = null,
    ) {
        $this->cache = $cache;
        $this->configPath = $configPath ?? APP . 'acl.json';
        $this->load();
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getRoleConfig(string $role): array
    {
        return $this->config[$role] ?? [];
    }

    public function getDeniedRules(string $role): array
    {
        return $this->config[$role]['denied'] ?? [];
    }

    public function getAllowedControllersForRole(string $role): array
    {
        $config = $this->getRoleConfig($role);
        unset($config['denied']);
        return $config;
    }

    public function reload(): void
    {
        $this->cache->delete(self::CACHE_KEY);
        $this->load();
    }

    public function hasRole(string $role): bool
    {
        return isset($this->config[$role]);
    }

    public function getAvailableRoles(): array
    {
        return array_keys($this->config);
    }

    private function load(): void
    {
        $cached = $this->cache->get(self::CACHE_KEY);

        if ($cached !== null) {
            $this->config = $cached;
            return;
        }
        $this->config = (new JsonFile($this->configPath))->getContentAsArray();

        if (empty($this->config)) {
            throw new RuntimeException("ACL config file not found: {$this->configPath}");
        }

        $this->cache->set(self::CACHE_KEY, $this->config, self::CACHE_TTL);
    }
}