<?php

declare(strict_types=1);

class View implements ViewInterface
{
    private string $_head;
    private string $_body;
    private string $_footer;
    private string $_outputBuffer;
    private string $_pageTitle = '';
    private string $_layout = 'default';
    private string $_token = '';
    private array $properties = [];
    private Request $request;

    public function __construct(private ViewEnvironment $viewEnv)
    {
    }

    public function render(string $templatePath, array $context = []): string
    {
        try {
            $templatePath = $this->viewEnv->getFile($templatePath);
            return $this->renderViewContent($templatePath, $context);
        } catch (ViewException $ex) {
            throw new ViewException("View Error: {$ex->getMessage()}");
        }
    }

    public function pageTitle(string $title): void
    {
        $this->_pageTitle = $title;
    }

    public function setLayout(string $layout): void
    {
        $this->_layout = $layout;
    }

    public function getPageTitle(): string
    {
        if (!empty($this->_pageTitle)) {
            return '<title>' . $this->_pageTitle . '</title>';
        }
        return '';
    }

    public function getPath(): string
    {
        return $this->viewEnv->getAppPath();
    }

    public function addProperties(array $props): void
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

    public function setToken(TokenInterface $token): void
    {
        $this->_token = $token->getCsrfHash(8, $this->_pageTitle);
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    private function isDevEnv(): bool
    {
        if (isset($_ENV['NODE_ENV']) && $_ENV['NODE_ENV'] === 'development') {
            return true;
        }
        return false;
    }

    private function token(): string
    {
        return $this->_token;
    }

    private function renderViewContent(string $templatePath, array $context): string
    {
        // Extract context variables
        extract($context, EXTR_SKIP);

        // Make view methods available in templates
        $css = fn ($path = null) => $this->css($path);
        $js = fn ($path = null, $flag = 'defer') => $this->js($path, $flag);
        $asset = fn ($path) => $this->asset($path);
        $token = fn () => $this->token();
        $start = fn ($type) => $this->start($type);
        $end = fn () => $this->end();
        $content = fn ($type) => $this->content($type);
        $getPageTitle = fn () => $this->getPageTitle();
        $isUserLoggedIn = fn () => $this->isUserLoggedIn();
        $isDevEnv = fn () => $this->isDevEnv();

        // Include helper functions
        require_once APP . 'Functions' . DS . 'functions.php';

        // FIRST: Execute the template to capture head/body/footer sections
        // This populates $this->_head, $this->_body, $this->_footer
        require_once $templatePath;

        if (!isset($this->_layout) || empty($this->_layout)) {
            throw new ViewNotFoundException('Layout not found. Please set a valid layout using setLayout() method.');
        }

        $layoutPath = $this->viewEnv->getLayoutPath() . DS . $this->_layout . '.php';
        if (!file_exists($layoutPath)) {
            throw new ViewNotFoundException("Layout file '{$this->_layout}.php' not found in layout path.");
        }
        ob_start();
        require_once $layoutPath;
        return ob_get_clean();
    }

    private function css(string|null $path = null): string
    {
        return $this->viewEnv->getCss($path);
    }

    private function js(string|null $path = null, string $flag = 'defer'): string
    {
        return $this->viewEnv->getJs($path, $flag);
    }

    /**
     * Generate URL for assets like images, SVGs, etc.
     *
     * @param string $path Relative path to the asset
     *
     * @return string Full URL to the asset
     */
    private function asset(string $path): string
    {
        // Remove leading slash if present
        $path = ltrim($path, '/');

        // Check if path already contains the assets directory
        if (!str_starts_with($path, 'assets/')) {
            $path = 'assets/' . $path;
        }

        // Return full URL to the asset
        return HOST . '/public/' . $path;
    }

    private function start(string $type): void
    {
        $this->_outputBuffer = $type;
        ob_start();
    }

    private function end(): void
    {
        isset($this->_outputBuffer) ? $this->{'_' . $this->_outputBuffer} = ob_get_clean() : '';
    }

    private function content(string $type): bool|string
    {
        return match ($type) {
            'head' => $this->_head ?? '',
            'body' => $this->_body ?? '',
            'footer' => $this->_footer ?? '',
            default => throw new ViewException('no content to display')
        };
    }

    private function getContentOverview(string $content): string
    {
        return substr(strip_tags($this->htmlDecode($content)), 0, 200) . '...';
    }

    private function htmlDecode(string|null $str): string
    {
        return !empty($str) ? htmlspecialchars_decode(html_entity_decode($str), ENT_QUOTES) : '';
    }

    private function isUserLoggedIn(): bool
    {
        $session = App::getInstance()->getSession();
        return $session->exists(CURRENT_USER_SESSION_NAME);
    }
}