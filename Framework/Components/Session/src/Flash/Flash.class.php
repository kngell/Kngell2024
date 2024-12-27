<?php

declare(strict_types=1);

class Flash implements FlashInterface
{
    /** Contains the session object */
    use SessionTrait;

    /** @var string */
    protected const FLASH_KEY = 'flash_message';
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
     * @return void
     * @throws SessionInvalidArgumentException
     */
    public function add(string $message, ?FlashType $type = null) : void
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
            ]
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

    public function getSession() : SessionInterface
    {
        return $this->session;
    }

    private function formatMessage(array $flashMsg) : string
    {
        $flashMsg = ArrayUtils::first($flashMsg);
        $msg = "<div id='message' class='alert alert-" . $flashMsg['type']->value . ' text-center' . "'>";
        $msg .= $flashMsg['message'];
        $msg .= '</div>';
        return $msg;
    }
}