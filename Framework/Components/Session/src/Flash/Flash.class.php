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
    public function __construct(?Object $session = null, ?string $flashKey = null)
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
     * @inheritdoc
     *
     * @param string $message
     * @param string $type
     * @return void
     */
    public function add(string $message, ?string $type = null) : void
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
            return $this->session->flush($this->flashKey);
        }
    }
}