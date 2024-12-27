<?php

declare(strict_types=1);

abstract class AbstractSessionStorage
{
    use SessionTrait;

    // private ?string $sessionPath = 'session_dir';

    /**
     * abstract class constructor.
     *
     * @param SessionEnvironment $sessionEnvironment
     */
    public function __construct(protected SessionEnvironment $sessionEnvironment, protected FilesSystemInterface $fileSyst, private SuperGlobalsInterface $globals)
    {
        $this->iniSet();
        // Destroy any existing sessions started with session.auto_start
        if ($this->isSessionStarted()) {
            session_unset();
            session_destroy();
        }
        $this->start();
        $this->cleanSessionPath();
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
    private function startSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_save_path(ROOT_DIR . DS . $this->sessionEnvironment->storagePath() . DS);
            session_start();
        }
    }

    /**
     * Define our session_set_cookie_params method via the $this->options parameters which
     * will be define within our core config directory.
     *
     * @return void
     */
    private function start(): void
    {
        $this->setSessionName($this->sessionEnvironment->getSessionName());
        $cookie_Params = $this->cookiesParams();
        session_set_cookie_params($cookie_Params);
        $this->startSession();
        $s = $_SESSION;
        if ($this->validateSession()) {
            if (! $this->preventSessionHijack()) {
                $_SESSION = [];
                $_SESSION['IPaddress'] = $this->globals->server('remote_addr'); //$_SERVER['REMOTE_ADDR'];
                $_SESSION['userAgent'] = $this->globals->server('http_user_agent'); //$_SERVER['HTTP_USER_AGENT'];
            } elseif (rand(1, 100) <= 5) { // Give a 5% chance of the session id changing on any request
                $this->sessionRegeneration();
            }
        } else {
            $_SESSION = [];
            session_destroy();
            $this->startSession(); // restart session
        }
    }

    /**
     * Override PHP default session runtime configurations.
     *
     * @return void
     */
    private function iniSet(): void
    {
        foreach ($this->sessionEnvironment->getSessionRuntimeConfigurations() as $option) {
            $sessionKey = str_replace('session.', '', $option);
            if ($option && array_key_exists($sessionKey, $this->sessionEnvironment->getConfig())) {
                ini_set($option, $this->sessionEnvironment->getSessionIniValues($sessionKey));
            }
        }
    }

    private function cookiesParams() : array
    {
        $cookies_params = session_get_cookie_params();
        $liftime = $this->sessionEnvironment->getLifetime();
        if ($cookies_params['lifetime'] === 0) {
            $liftime = $cookies_params['lifetime'];
        }
        return [
            'lifetime' => $liftime,
            'path' => $this->sessionEnvironment->getPath(),
            'domain' => $this->sessionEnvironment->getDomain(),
            'secure' => $this->sessionEnvironment->isSecure(),
            'httponly' => $this->sessionEnvironment->isHttpOnly(),
        ];
    }

    private function cleanSessionPath(): void
    {
        $fileList = $this->fileSyst->listAllFiles($this->sessionEnvironment->storagePath());
        if ($fileList && is_array($fileList)) {
            foreach ($fileList as $file) {
                $sess = session_id();
                if (str_replace('sess_', '', $file) !== session_id()) {
                    $this->fileSyst->removeFile($this->sessionEnvironment->storagePath(), $file);
                }
            }
        }
    }
}
