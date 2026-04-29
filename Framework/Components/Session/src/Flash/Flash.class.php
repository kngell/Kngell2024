<?php

declare(strict_types=1);

class Flash implements FlashInterface
{
    /** Contains the session object */
    use SessionTrait;

    /** @var string */
    protected const FLASH_KEY = 'flash_message';
    protected const INPUT_KEY = 'old_input';

    /** @var string */
    protected string $flashKey;

    /** @var ?SessionInterface */
    protected ?SessionInterface $session;

    /**
     * Class constructor method which accepts a single default argument
     * which allows the user to specifies their own flash key as a option
     * else if not present will use the default set by the framework.
     *
     * @param object|null $session
     * @param null|string $flashKey
     */
    public function __construct(?SessionInterface $session = null, ?string $flashKey = null)
    {
        $this->session = $session;
        if ($flashKey != null) {
            $this->flashKey = $flashKey;
        } else {
            $this->flashKey = self::FLASH_KEY;
        }
    }

    /**
     * @param object $session
     *
     * @return self
     */
    public function getSessionObject(object $session): self
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @param string $message
     * @param null|FlashType $type
     *
     * @throws SessionInvalidArgumentException
     *
     * @return void
     */
    public function add(string $message, ?FlashType $type = null): void
    {
        /* Apply default constants to flash type */
        if ($type === null) {
            $type = FlashType::SUCCESS;
        }
        if ($this->session->exists($this->flashKey)) {
            $this->session->set($this->flashKey, []);
        }
        $this->session->setArray(
            $this->flashKey,
            [
                'message' => $message,
                'type' => $type,
            ],
        );
    }

    /**
     * @inheritdoc
     *
     * @return mixed
     */
    public function get()
    {
        if ($this->session->exists($this->flashKey)) {
            return $this->formatMessage($this->session->flush($this->flashKey));
        }
    }

    public function addData(string $key, array $data = []): void
    {
        $uniqueKey = 'data_' . md5(trim($key));
        if (!empty($data)) {
            $this->session->set($uniqueKey . '_flash_data', $data);
        }
    }

    public function peekData(string $key): ?array
    {
        $uniqueKey = 'data_' . md5(trim($key));
        return $this->session->get($uniqueKey . '_flash_data');
    }

    public function getData(string $key): ?array
    {
        $uniqueKey = 'data_' . md5(trim($key));
        return $this->session->flush($uniqueKey . '_flash_data');
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

    public function addFormData(
        string $formAction,
        array $postData = [],
        array $formErrors = [],
        array $fileData = [],
    ): void {
        $formAction = $this->normalizeFormAction($formAction);
        $formKey = 'form_' . md5(ltrim($formAction, DS));

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

    public function flush(?string $key = null): array
    {
        $input = $this->session->flush(self::INPUT_KEY);

        if ($key !== null && is_array($input)) {
            return $input[$key] ?? null;
        }
        return $input;
    }

    public function getFormData(string $formAction): array
    {
        $formAction = $this->normalizeFormAction($formAction);
        $formKey = 'form_' . md5($formAction);

        $values = $this->peekData($formKey . '_values') ?? [];
        $errors = $this->peekData($formKey . '_errors') ?? [];
        $files = $this->peekData($formKey . '_files') ?? [];
        return [
            'values' => $values,
            'errors' => $errors,
            'files' => $files,
        ];
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    private function normalizeFormAction(string $formAction): string
    {
        return rtrim($formAction, DS);
    }

    private function formatMessage(array $flashMsg): string
    {
        $flashMsg = ArrayUtils::first($flashMsg);
        $msg = "<div id='message' class='alert alert-" . $flashMsg['type']->value . ' alert-dismissible fade show text-center' . "' role='alert'>";
        $msg .= $flashMsg['message'];
        $msg .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        $msg .= '</div>';
        return $msg;
    }
}