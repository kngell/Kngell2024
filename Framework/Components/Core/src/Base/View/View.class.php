<?php

declare(strict_types=1);

class View implements ViewInterface
{
    private ViewEnvironment $viewEnv;
    private string $_head;
    private string $_body;
    private string $_footer;
    private string $_outputBuffer;
    private string $_html;
    private string $_pageTitle = '';
    private string $_layout = 'default';
    private string $_token = '';
    private array $properties = [];
    private Request $request;
    // private string $favicon;

    public function __construct(ViewEnvironment $viewEnv)
    {
        $this->viewEnv = $viewEnv;
    }

    // public function __destruct()
    // {
    //     unset($this->viewEnv);
    //     unset($this->_head);
    //     unset($this->_body);
    //     unset($this->_footer);
    //     unset($this->_footer);
    //     unset($this->_html);
    //     unset($this->_outputBuffer);
    //     unset($this->_layout);
    //     unset($this->request);
    // }

    public function render(string $templatePath, array $context = []): string
    {
        $templatePath = $this->viewEnv->getFile($templatePath);
        if (! $templatePath) {
            throw new ViewException('This view does not exist');
        }
        try {
            return $this->renderViewContent($templatePath, $context);
        } catch (ViewException $ex) {
            throw $ex;
        }
    }

    public function pageTitle(string $title) : void
    {
        $this->_pageTitle = $title;
    }

    public function setLayout(string $layout) : void
    {
        $this->_layout = $layout;
    }

    public function getPageTitle() : string
    {
        if (! empty($this->_pageTitle)) {
            return '<title>' . $this->_pageTitle . '</title>';
        }
        return '';
    }

    // public function favicon() : string
    // {
    //     if (isset($this->favicon)) {
    //         return $this->favicon;
    //     }
    //     return '';
    // }

    public function getPath() : string
    {
        return $this->viewEnv->getAppPath();
    }

    public function addProperties(array $props) : void
    {
        foreach ($props as $name => $prop) {
            $this->properties[$name] = $prop;
        }
    }

    /**
     * @return string
     */
    public function getLayout(): string
    {
        return $this->_layout;
    }

    public function setToken(TokenInterface $token) : void
    {
        $this->_token = $token->getCsrfHash(8, $this->_pageTitle);
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    private function token() : string
    {
        return $this->_token;
    }

    private function renderViewContent(string $templatePath, $context) : string
    {
        extract($context, EXTR_SKIP);
        require_once APP . 'Functions' . DS . 'functions.php';
        require_once $templatePath;
        $layout = $this->viewEnv->getLayoutPath() . DS . $this->_layout . '.php';
        if (! empty($layout)) {
            $this->start('html');
            require_once $layout;
            $this->end();
        }
        return $this->content('html');
    }

    private function css(string|null $path = null) : string
    {
        return $this->viewEnv->getCss($path);
    }

    private function js(string|null $path = null) : string
    {
        return $this->viewEnv->getJs($path);
    }

    /**
     * Generate URL for assets like images, SVGs, etc.
     *
     * @param string $path Relative path to the asset
     * @return string Full URL to the asset
     */
    private function asset(string $path) : string
    {
        // Remove leading slash if present
        $path = ltrim($path, '/');

        // Check if path already contains the assets directory
        if (! str_starts_with($path, 'assets/')) {
            $path = 'assets/' . $path;
        }

        // Return full URL to the asset
        return HOST . '/public/' . $path;
    }

    private function start(string $type) : void
    {
        $this->_outputBuffer = $type;
        ob_start();
    }

    private function end() : void
    {
        isset($this->_outputBuffer) ? $this->{'_' . $this->_outputBuffer} = ob_get_clean() : '';
    }

    private function content(string $type) : bool|string
    {
        return match ($type) {
            'head' => $this->_head ?? '',
            'body' => $this->_body ?? '',
            'footer' => $this->_footer ?? '',
            'html' => $this->_html ?? '',
            default => throw new ViewException('no content to display')
        };
    }

    private function getContentOverview(string $content):string
    {
        return substr(strip_tags($this->htmlDecode($content)), 0, 200) . '...';
    }

    private function htmlDecode(string|null $str) : string
    {
        return ! empty($str) ? htmlspecialchars_decode(html_entity_decode($str), ENT_QUOTES) : '';
    }

    private function isUserLoggedIn() : bool
    {
        $session = App::getInstance()->getSession();
        return $session->exists(CURRENT_USER_SESSION_NAME);
    }
}
