<?php

declare(strict_types=1);

class FooterAboutService
{
    private const CACHE_KEY = 'footer_about';
    private const CACHE_TTL = 86400; // 24 hours

    private HtmlSectionCacheManager $cache;

    public function __construct(
        private FooterAboutModel $model,
        private FooterAboutCacheManagerFactory $cacheFactory,
    ) {
        $this->cache = $cacheFactory->create();
    }

    // ─── Single Entity ──────────────────────────────────────────
    public function getFooterAbout(): array
    {
        return $this->cache->remember(self::CACHE_KEY, function () {
            return $this->loadFromDatabase();
        }, self::CACHE_TTL);
    }

    public function getFooterAboutById(int $id): array
    {
        $cacheKey = $this->getAboutCacheKey($id);
        return $this->cache->remember($cacheKey, function () use ($id) {
            return $this->loadByIdFromDatabase($id);
        }, self::CACHE_TTL);
    }

    public function create(array $data): ?string
    {
        $result = $this->model->insert($data);

        if ($result->isSuccess()) {
            $this->clearCache();
            return $result->getLastInsertId();
        }

        return null;
    }

    public function update(array $data): bool
    {
        if (!isset($data['id'])) {
            return false;
        }

        $result = $this->model->update($data);

        if ($result->isSuccess()) {
            $this->invalidateAboutCache($data['id']);
            return true;
        }

        return false;
    }

    public function delete(int $id): bool
    {
        $result = $this->model->delete($id);

        if ($result->isSuccess()) {
            $this->clearCache();
            return true;
        }

        return false;
    }

    public function toggleActive(int $id, bool $isActive): array
    {
        $result = $this->model->update(['id' => $id, 'is_active' => $isActive]);

        if (!$result->isSuccess()) {
            return [];
        }

        $this->invalidateAboutCache($id);
        return $this->getFooterAbout();
    }

    // ─── Multiple Entities ─────────────────────────────────────

    public function getAll(array $conditions = [], ?int $limit = null, ?int $offset = null): array
    {
        $cacheKey = $this->getListCacheKey($conditions, $limit, $offset);

        return $this->cache->remember($cacheKey, function () use ($conditions, $limit, $offset) {
            return $this->loadListFromDatabase($conditions, $limit, $offset);
        }, self::CACHE_TTL);
    }

    public function getActive(): array
    {
        return $this->getAll(['is_active' => true]);
    }

    // ─── Counts ─────────────────────────────────────────────────

    public function getActiveCount(): int
    {
        return $this->model->count(['is_active' => true]) ?? 0;
    }

    public function getTotalCount(): int
    {
        return $this->model->count([]) ?? 0;
    }

    public function exists(): bool
    {
        $data = $this->getFooterAbout();
        return !empty($data);
    }

    public function clearCache(): void
    {
        $this->cache->invalidateAllPages(self::CACHE_KEY);
        $this->cache->invalidateAllPages('footer_about_*');
        $this->cache->invalidateAllPages('footer_about_list_*');
    }

    // ─── Private Methods ───────────────────────────────────────

    private function invalidateAboutCache(int $id): void
    {
        $cacheKey = $this->getAboutCacheKey($id);
        $this->cache->invalidateAllPages($cacheKey);
        $this->invalidateStructureCache();
    }

    private function invalidateStructureCache(): void
    {
        $this->cache->invalidateAllPages(self::CACHE_KEY);
    }

    private function getAboutCacheKey(int $id): string
    {
        return "footer_about_{$id}";
    }

    private function getListCacheKey(array $conditions, ?int $limit, ?int $offset): string
    {
        $hash = md5(json_encode($conditions) . $limit . $offset);
        return "footer_about_list_{$hash}";
    }

    private function loadFromDatabase(): array
    {
        $result = $this->model->one();

        if (!$result->isSuccess()) {
            return [];
        }

        return $result->asArray();
    }

    private function loadByIdFromDatabase(int $id): array
    {
        $result = $this->model->find($id);

        if (!$result->isSuccess()) {
            return [];
        }

        return $result->asArray();
    }

    private function loadListFromDatabase(array $conditions, ?int $limit, ?int $offset): array
    {
        $result = $this->model->all([
            'conditions' => $conditions,
            'limit' => $limit,
            'offset' => $offset,
        ]);

        if (!$result->isSuccess()) {
            return [];
        }

        return $result->asArray();
    }
}