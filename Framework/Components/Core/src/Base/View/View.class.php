<?php

declare(strict_types=1);

class View implements ViewInterface
{
    private string $_head;
    private string $_body;
    private string $_footer;
    private string $_outputBuffer;
    private string $_pageTitle = '';
    private NavbarType $_layout = NavbarType::DEFAULT;
    private string $_token = '';
    private array $properties = [];
    private Request $request;
    private ?string $cachedLayoutPath = null;

    public function __construct(private ViewEnvironment $viewEnv)
    {
    }

    // public function render(string $templatePath, array $context = []): string
    // {
    //     try {
    //         $templatePath = $this->viewEnv->getFile($templatePath);
    //         $html = $this->renderViewContent($templatePath, $context);
    //         return $this->formatOutput($html);
    //     } catch (ViewException $ex) {
    //         throw new ViewException("View Error: {$ex->getMessage()}");
    //     }
    // }

    public function render(string $templatePath, array $context = []): string
    {
        $start = microtime(true);
        $timers = [];

        // Step 1: Get template file
        $t1 = microtime(true);
        $templatePath = $this->viewEnv->getFile($templatePath);
        $timers['get_file'] = (microtime(true) - $t1) * 1000;

        // Step 2: Render view content (this is where the 8.2 seconds is)
        $t2 = microtime(true);
        $html = $this->renderViewContent($templatePath, $context);
        $timers['render_content'] = (microtime(true) - $t2) * 1000;

        // Step 3: Format output
        $t3 = microtime(true);
        $output = $this->formatOutput($html);
        $timers['format_output'] = (microtime(true) - $t3) * 1000;

        $totalTime = (microtime(true) - $start) * 1000;

        // Log breakdown
        error_log(sprintf(
            "[PERFORMANCE] View::render breakdown:\n" .
            "  Get file: %.2f ms\n" .
            "  Render content: %.2f ms\n" .
            "  Format output: %.2f ms\n" .
            '  TOTAL: %.2f ms',
            $timers['get_file'],
            $timers['render_content'],
            $timers['format_output'],
            $totalTime,
        ));

        return $output;
    }

    public function pageTitle(string $title): void
    {
        $this->_pageTitle = $title;
    }

    public function setLayout(NavbarType $layout): void
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
     * @return NavbarType
     */
    public function getLayout(): NavbarType
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

    public function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
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

    private function loadFunctions(): void
    {
        static $loaded = false;
        if (!$loaded) {
            require_once APP . 'Functions' . DS . 'functions.php';
            $loaded = true;
        }
    }

    private function getLayoutPath(): string
    {
        if ($this->cachedLayoutPath === null) {
            $this->cachedLayoutPath = $this->viewEnv->getLayoutPath() . DS . $this->_layout->value . '.php';
        }
        return $this->cachedLayoutPath;
    }

    private function renderViewContent(string $templatePath, array $context): string
    {
        extract($context, EXTR_SKIP);
        require_once APP . 'Functions' . DS . 'functions.php';
        require_once $templatePath;

        if (!isset($this->_layout) || empty($this->_layout)) {
            throw new ViewNotFoundException('Layout not found. Please set a valid layout using setLayout() method.');
        }

        $layoutPath = $this->viewEnv->getLayoutPath() . DS . $this->_layout->value . '.php';
        if (!file_exists($layoutPath)) {
            throw new ViewNotFoundException("Layout file '{$this->_layout}.php' not found in layout path.");
        }

        ob_start();
        require_once $layoutPath;
        return ob_get_clean();
    }

    private function formatOutput(string $html): string
    {
        if ($this->isDevEnv()) {
            return $this->prettyPrintHtml($html);
        }
        return $this->minifyHtml($html);
    }

    private function prettyPrintHtml(string $html): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $formatted = $dom->saveHTML();
        return preg_replace('/^<\?xml encoding="UTF-8">/', '', $formatted);
    }

    private function minifyHtml(string $html): string
    {
        $html = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $html);
        $html = preg_replace('/>\s+</', '><', $html);
        return trim($html);
    }

    private function formatOutputEnhanced(string $html, array $options = []): string
    {
        $defaults = [
            'removeComments' => true,
            'removeWhitespace' => true,
            'prettyPrint' => $this->isDevEnv(),
            'indentSize' => 2,
        ];

        $options = array_merge($defaults, $options);

        if ($options['prettyPrint']) {
            return $this->prettyPrintHtml($html);
        }

        $result = $html;

        if ($options['removeComments']) {
            $result = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $result);
        }

        if ($options['removeWhitespace']) {
            $result = preg_replace('/>\s+</', '><', $result);
        }

        return trim($result);
    }

    private function css(array|string|null $formAsset): string
    {
        if (empty($formAsset)) {
            return '';
        }

        if (is_string($formAsset)) {
            return $this->viewEnv->getCss($formAsset);
        }

        // Try to find 'css' key deeply
        $cssAssets = ArrayUtils::deepGet($formAsset, 'css', []);

        // If not found, try wildcard
        if (empty($cssAssets)) {
            $allCss = ArrayUtils::deepGetAll($formAsset, '*.css');
            $cssAssets = [];
            foreach ($allCss as $css) {
                if (is_array($css)) {
                    $cssAssets = array_merge($cssAssets, array_filter($css, 'is_string'));
                } elseif (is_string($css)) {
                    $cssAssets[] = $css;
                }
            }
        }

        // Ensure it's an array and filter empty strings
        $cssAssets = is_array($cssAssets) ? $cssAssets : [$cssAssets];
        $cssAssets = array_unique(array_filter($cssAssets, function ($item) {
            return is_string($item) && !empty($item);
        }));

        if (empty($cssAssets)) {
            return '';
        }

        return implode('', array_map(
            [$this->viewEnv, 'getCss'],
            $cssAssets,
        ));
    }

    private function js(string|null $path = null, string $flag = 'defer'): string
    {
        return $this->viewEnv->getJs($path, $flag);
    }

    private function asset(string $path): string
    {
        $path = ltrim($path, '/');

        if (!str_starts_with($path, 'assets/')) {
            $path = 'assets/' . $path;
        }
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

    private function isUserLoggedIn(): bool
    {
        $session = App::getInstance()->getSession();
        return $session->exists(CURRENT_USER_SESSION_NAME);
    }

    private function debugOutput(string $html, string $type = 'raw'): string
    {
        if (!$this->isDevEnv()) {
            return $html;
        }

        switch ($type) {
            case 'raw':
                return '<pre>' . htmlspecialchars($html) . '</pre>';
            case 'formatted':
                return '<pre>' . htmlspecialchars($this->prettyPrintHtml($html)) . '</pre>';
            default:
                return $html;
        }
    }
}