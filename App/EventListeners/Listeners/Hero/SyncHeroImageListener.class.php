<?php

declare(strict_types=1);

class SyncHeroImageListener implements EventListenerInterface
{
    public function __construct(
        private HeroModel $model,
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        $payload = $event->getParams();

        // --------------------------------------------------
        // 1. Validate & extract payload
        // --------------------------------------------------
        $heroId = (int) ($payload['data']['id'] ?? 0);
        if ($heroId <= 0) {
            throw new InvalidArgumentException(
                'SyncHeroImageListener requires a valid id.',
            );
        }

        $currentUrls = $this->sanitizeUrls(
            $payload['data']['media']['image_url'] ?? [],
        );

        $heroName = trim($payload['data']['form_data']['name'] ?? '');
        if ($heroName === '') {
            $heroName = 'Hero';
        }

        // --------------------------------------------------
        // 2. Fetch existing records and build lookup map
        // --------------------------------------------------
        $existingRecords = $this->model
            ->all(['hero_id' => $heroId])
            ->asArray();

        $existingMap = [];
        foreach ($existingRecords as $record) {
            $existingMap[$record['image_url']] = $record;
        }

        // --------------------------------------------------
        // 3. Delete orphaned URLs (in DB but not in submission)
        // --------------------------------------------------
        $urlsInDb = array_keys($existingMap);
        $urlsToDelete = array_diff($urlsInDb, $currentUrls);
        $deleteResult = null;

        if (!empty($urlsToDelete)) {
            $deleteResult = $this->model->delete([
                'hero_id' => $heroId,
                'image_url' => $urlsToDelete,
            ]);

            if (!$deleteResult->isSuccess()) {
                throw new RuntimeException(
                    "Failed to remove orphan images for Hero {$heroId}.",
                );
            }
        }

        // --------------------------------------------------
        // 4. Upsert current URLs
        // --------------------------------------------------
        if (empty($currentUrls)) {
            return $deleteResult;
        }

        $toSync = [];
        foreach ($currentUrls as $index => $url) {
            $sortOrder = $index + 1;
            $existing = $existingMap[$url] ?? null;

            $row = [
                'hero_id' => $heroId,
                'image_url' => $url,
                'sort_order' => $sortOrder,
                'alt_text' => $existing['alt_text']
                    ?? "{$heroName} image {$sortOrder}",
            ];

            if ($existing !== null) {
                $row['id'] = $existing['id'];
            }

            $toSync[] = $row;
        }

        $saveResult = $this->model->save($toSync);

        if (!$saveResult->isSuccess()) {
            throw new RuntimeException(
                "Failed to synchronize gallery images for product {$heroId}.",
            );
        }

        return $saveResult->hasChanged() ? $saveResult : $deleteResult;
    }

    private function sanitizeUrls(mixed $urls): array
    {
        if (!is_array($urls)) {
            return [];
        }

        return array_values(
            array_filter(
                $urls,
                fn (mixed $url): bool => is_string($url) && trim($url) !== '',
            ),
        );
    }
}