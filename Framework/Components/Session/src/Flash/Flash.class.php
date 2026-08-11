<?php

declare(strict_types=1);

class Flash implements FlashInterface
{
    use SessionTrait;

    protected const FLASH_KEY = 'flash_message';
    protected const INPUT_KEY = 'old_input';
    protected const string FLAG_KEY = 'flag_key';
    protected const DEFAULT_DURATION = 5000;

    protected string $flashKey;
    protected ?SessionInterface $session;

    public function __construct(?SessionInterface $session = null, ?string $flashKey = null)
    {
        $this->session = $session;
        $this->flashKey = $flashKey ?? self::FLASH_KEY;
    }

    public function getSessionObject(object $session): self
    {
        $this->session = $session;
        return $this;
    }

    // ───────────────────────────────────────────────────────────
    // Flash messages
    // ───────────────────────────────────────────────────────────

    /**
     * Add a flash message. Multiple messages are appended (queue).
     *
     * @param string         $message
     * @param FlashType|null $type
     * @param array          $options {
     *
     *     @var string|null $title         Optional bold title.
     *     @var int|null    $duration      Auto-dismiss in ms. null/0 = sticky.
     *     @var bool        $dismissible   Show close button. Default true.
     *     @var bool        $showProgress  Show countdown bar. Default true if duration > 0.
     *     @var array       $extra         Arbitrary metadata.
     * }
     */
    public function add(string $message, ?FlashType $type = null, array|FlashOptions $options = []): void
    {
        if ($options instanceof FlashOptions) {
            $options = $options->toArray();
        }
        $type = $type ?? FlashType::SUCCESS;

        // Sensible defaults per type
        $defaultDuration = match ($type) {
            FlashType::DANGER => null,                    // errors stick by default
            FlashType::WARNING => self::DEFAULT_DURATION,
            default => self::DEFAULT_DURATION,
        };

        $duration = $options['duration'] ?? $defaultDuration;
        $showProgress = ($options['showProgress'] ?? true) && !empty($duration);

        $payload = [
            'type' => $type->value,
            'message' => $message,
            'title' => $options['title'] ?? null,
            'duration' => $duration,
            'dismissible' => $options['dismissible'] ?? true,
            'showProgress' => $showProgress,
            'extra' => $options['extra'] ?? [],
            'created_at' => time(),
        ];

        // Append to queue (preserves multiple flashes across one request)
        $current = $this->session->exists($this->flashKey)
            ? (array) $this->session->get($this->flashKey)
            : [];

        $current[] = $payload;

        $this->session->set($this->flashKey, $current);
    }

    public function addFlag(FlashFlagKey $flag): void
    {
        $flags = $this->session->exists(self::FLAG_KEY)
            ? (array) $this->session->get(self::FLAG_KEY)
            : [];

        $flagValue = $flag->value;
        if (!in_array($flagValue, $flags, true)) {
            $flags[] = $flagValue;
            $this->session->set(self::FLAG_KEY, $flags);
        }
    }

    public function hasFlag(FlashFlagKey $flag): bool
    {
        if (!$this->session->exists(self::FLAG_KEY)) {
            return false;
        }

        $flags = (array) $this->session->get(self::FLAG_KEY);
        return in_array($flag->value, $flags, true);
    }

    public function getAllFlags(): array
    {
        if (!$this->session->exists(self::FLAG_KEY)) {
            return [];
        }
        return (array) $this->session->get(self::FLAG_KEY);
    }

    public function consumeFlag(FlashFlagKey $flag): bool
    {
        if (!$this->session->exists(self::FLAG_KEY)) {
            return false;
        }

        $flags = (array) $this->session->get(self::FLAG_KEY);
        $flagValue = $flag->value;
        $found = in_array($flagValue, $flags, true);

        if ($found) {
            $flags = array_values(array_filter($flags, function ($f) use ($flagValue) {
                return $f !== $flagValue;
            }));

            if (empty($flags)) {
                $this->session->delete(self::FLAG_KEY);
            } else {
                $this->session->set(self::FLAG_KEY, $flags);
            }
        }

        return $found;
    }

    /**
     * Consume all flash messages (returns and clears them).
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(): array
    {
        if (!$this->session->exists($this->flashKey)) {
            return [];
        }

        $messages = (array) $this->session->flush($this->flashKey);

        // Backward compatibility: support legacy single-message structure
        // (when only ['message' => ..., 'type' => ...] was stored)
        if (isset($messages['message']) && isset($messages['type'])) {
            $messages = [$this->normalizeLegacyMessage($messages)];
        }

        return array_values($messages);
    }

    /**
     * Peek at flash messages without consuming.
     *
     * @return array<int, array<string, mixed>>
     */
    public function peek(): array
    {
        if (!$this->session->exists($this->flashKey)) {
            return [];
        }

        $messages = (array) $this->session->get($this->flashKey);

        if (isset($messages['message']) && isset($messages['type'])) {
            $messages = [$this->normalizeLegacyMessage($messages)];
        }

        return array_values($messages);
    }

    public function has(): bool
    {
        return $this->session->exists($this->flashKey)
            && !empty($this->session->get($this->flashKey));
    }

    // ───────────────────────────────────────────────────────────
    // Generic per-key data (unchanged)
    // ───────────────────────────────────────────────────────────

    public function addData(string $key, array $data = []): void
    {
        if (!empty($data)) {
            $this->session->set('data_' . $this->normalizeKey($key) . '_flash_data', $data);
        }
    }

    public function peekData(string $key): ?array
    {
        $uniqueKey = 'data_' . md5(trim($key));
        return $this->session->get($uniqueKey . '_flash_data');
    }

    public function getData(string $key): ?array
    {
        return $this->flush('data_' . $this->normalizeKey($key) . '_flash_data');
    }

    public function removeData(string $key): void
    {
        $uniqueKey = 'data_' . md5(trim($key));
        $this->session->delete($uniqueKey . '_flash_data');
    }

    public function hasData(string $key): bool
    {
        $uniqueKey = 'data_' . md5(trim($key));
        return $this->session->exists($uniqueKey . '_flash_data');
    }

    public function addFormData(string $formAction, array $postData = [], array $formErrors = [], array $fileData = []): void
    {
        $formKey = 'form_' . $this->normalizeKey($formAction);

        if (!empty($postData)) {
            $this->session->set($formKey . '_values', $postData);
        }
        if (!empty($formErrors)) {
            $this->session->set($formKey . '_errors', $formErrors);
        }
        if (!empty($fileData)) {
            $this->session->set($formKey . '_files', $fileData);
        }
    }

    public function getFormData(string $formAction): array
    {
        $formKey = 'form_' . $this->normalizeKey($formAction);

        return [
            'values' => $this->flush($formKey . '_values') ?? [],
            'errors' => $this->flush($formKey . '_errors') ?? [],
            'files' => $this->flush($formKey . '_files') ?? [],
        ];
    }

    public function flush(?string $key = null): array
    {
        $targetKey = $key ?? self::INPUT_KEY;
        $data = $this->session->flush($targetKey);
        return is_array($data) ? $data : [];
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    // ───────────────────────────────────────────────────────────
    // Form data (unchanged)
    // ───────────────────────────────────────────────────────────
    private function normalizeKey(string $key): string
    {
        return md5(trim($key, " \t\n\r\0\x0B/" . DS));
    }

    // ───────────────────────────────────────────────────────────
    // Internal helpers
    // ───────────────────────────────────────────────────────────
    private function normalizeLegacyMessage(array $legacy): array
    {
        $type = $legacy['type'] instanceof FlashType
            ? $legacy['type']->value
            : (string) $legacy['type'];

        $duration = match ($type) {
            FlashType::DANGER->value => null,
            default => self::DEFAULT_DURATION,
        };

        return [
            'type' => $type,
            'message' => (string) $legacy['message'],
            'title' => null,
            'duration' => $duration,
            'dismissible' => true,
            'showProgress' => $duration !== null,
            'extra' => [],
            'created_at' => time(),
        ];
    }
}