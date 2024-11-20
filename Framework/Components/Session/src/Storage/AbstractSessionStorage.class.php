<?php

declare(strict_types=1);

abstract class AbstractSessionStorage
{
    use SessionTrait;

    private ?string $sessionPath = 'session_dir';

    /**
     * abstract class constructor.
     *
     * @param SessionEnvironment $sessionEnvironment
     */
    public function __construct(protected SessionEnvironment $sessionEnvironment, protected FilesSystemInterface $fileSyst)
    {
        if ($sessionEnvironment) {
            $this->sessionEnvironment = $sessionEnvironment;
        }
        $this->iniSet();
        // Destroy any existing sessions started with session.auto_start
        if ($this->isSessionStarted()) {
            session_unset();
            session_destroy();
        }
        $this->start();
    }

    /**
     * Set the name of the session.
     *
     * @param string $sessionName
     * @return void
     */
    public function setSessionName(string $sessionName): void
    {
        session_name($sessionName);
    }

    /**
     * Return the current session name.
     *
     * @return string
     */
    public function getSessionName(): string
    {
        return session_name();
    }

    /**
     * Set the name of the session ID.
     *
     * @param string $sessionID
     * @return void
     */
    public function setSessionID(string $sessionID): void
    {
        session_id($sessionID);
    }

    /**
     * Return the current session ID.
     *
     * @return string
     */
    public function getSessionID(): string
    {
        return session_id();
    }

    /**
     * Override PHP default session runtime configurations.
     *
     * @return void
     */
    public function iniSet(): void
    {
        foreach ($this->sessionEnvironment->getSessionRuntimeConfigurations() as $option) {
            $sessionKey = str_replace('session.', '', $option);
            if ($option && array_key_exists($sessionKey, $this->sessionEnvironment->getConfig())) {
                ini_set($option, $this->sessionEnvironment->getSessionIniValues($sessionKey));
            }
        }
    }

    /**
     * Define our session_set_cookie_params method via the $this->options parameters which
     * will be define within our core config directory.
     *
     * @return void
     */
    public function start(): void
    {
        $this->setSessionName($this->sessionEnvironment->getSessionName());
        session_set_cookie_params((int) $this->sessionEnvironment->getLifetime(), $this->sessionEnvironment->getPath(), $this->sessionEnvironment->getDomain(), $this->sessionEnvironment->isSecure(), $this->sessionEnvironment->isHttpOnly());

        $this->startSession();
        $this->cleanSessionPath();
        if ($this->validateSession()) {
            if (! $this->preventSessionHijack()) {
                $_SESSION = [];
                $_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
            } elseif (rand(1, 100) <= 5) { // Give a 5% chance of the session id changing on any request
                $this->sessionRegeneration();
            }
        } else {
            // $_SESSION = array();
            // session_destroy();
            // $this->startSession(); // restart session
        }
    }

    /**
     * Prevent session within the cli. Even though we can't run sessions within
     * the command line. also we checking we have a session id and its not empty
     * else return false.
     *
     * @return bool
     */
    public function isSessionStarted(): bool
    {
        return php_sapi_name() !== 'cli' && $this->getSessionID() !== '';
    }

    /**
     * Start our session if we haven't already have a php session.
     *
     * @return void
     */
    public function startSession()
    {
        if (session_status() == PHP_SESSION_NONE && session_id() == '') {
            session_save_path(ROOT_DIR . DS . $this->sessionEnvironment->storagePath());
            session_start();
        }
    }

    private function cleanSessionPath(): void
    {
        $fileList = $this->fileSyst->listAllFiles($this->sessionEnvironment->storagePath());
        if ($fileList && is_array($fileList)) {
            foreach ($fileList as $file) {
                if (str_replace('sess_', '', $file) !== session_id()) {
                    $this->fileSyst->removeFile($this->sessionEnvironment->storagePath(), $file);
                }
            }
        }
    }
}