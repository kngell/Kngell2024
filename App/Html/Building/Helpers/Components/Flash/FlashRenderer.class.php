<?php

declare(strict_types=1);

/**
 * Bridges the Flash session service with the FlashMessage component.
 * Single entry point for rendering flashes as HTML or JSON.
 */
class FlashRenderer
{
    public function __construct(
        private readonly FlashInterface $flash,
        private readonly FlashMessage $component,
    ) {
    }

    /**
     * Render all pending flash messages as HTML and clear the session.
     * Always returns a flash-container element (hidden when empty).
     */
    public function render(): string
    {
        $messages = $this->loadMessages(consume: true);

        if (empty($messages)) {
            // ✅ Always render the container, even when empty
            return $this->renderEmptyContainer();
        }

        $html = $this->component
            ->reset()
            ->withMessages($messages)
            ->build()
            ?->generate();

        return $html ?? $this->renderEmptyContainer();
    }

    /**
     * Render WITHOUT consuming (peek). Useful for previewing.
     */
    public function renderPeek(): string
    {
        $messages = $this->loadMessages(consume: false);

        if (empty($messages)) {
            return $this->renderEmptyContainer();
        }

        return $this->component
            ->reset()
            ->withMessages($messages)
            ->build()
            ?->generate() ?? $this->renderEmptyContainer();
    }

    /**
     * Return pending flash messages as JSON (for AJAX responses).
     *
     * @param bool $consume  Whether to clear the session after fetching.
     */
    public function toJson(bool $consume = true): string
    {
        $messages = $this->loadMessages(consume: $consume);

        $payload = array_map(
            fn (FlashMessageDTO $m) => $m->toArray(),
            $messages,
        );

        return json_encode($payload, JSON_THROW_ON_ERROR);
    }

    /**
     * Return pending flash messages as a plain array (for AJAX response merging).
     *
     * @return list<array<string, mixed>>
     */
    public function toArray(bool $consume = true): array
    {
        return array_map(
            fn (FlashMessageDTO $m) => $m->toArray(),
            $this->loadMessages(consume: $consume),
        );
    }

    /**
     * Render an ad-hoc component (without touching the session).
     * Useful for embedding flash markup into AJAX HTML responses.
     *
     * @param list<FlashMessageDTO> $messages
     */
    public function renderMessages(array $messages): string
    {
        if (empty($messages)) {
            return $this->renderEmptyContainer();
        }

        return $this->component
            ->reset()
            ->withMessages($messages)
            ->build()
            ?->generate() ?? $this->renderEmptyContainer();
    }

    /**
     * Render an empty flash container (hidden by default).
     */
    private function renderEmptyContainer(): string
    {
        return '<div class="flash-container" style="display: none;" aria-live="polite" aria-atomic="true"></div>';
    }

    /**
     * @return list<FlashMessageDTO>
     */
    private function loadMessages(bool $consume): array
    {
        $raw = $consume ? $this->flash->get() : $this->flash->peek();

        return array_map(
            fn (array $data) => FlashMessageDTO::fromArray($data),
            $raw,
        );
    }
}