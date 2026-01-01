<?php

declare(strict_types=1);
class CustomLoggerWithJS extends CustomLogger
{
    public function flushBrowserLogs(): void
    {
        parent::flushBrowserLogs();

        // Add JavaScript after the logs
        if (!empty(self::$browserLogs) && !headers_sent()) {
            echo $this->getLoggerJS();
        }
    }

    private function getLoggerJS(): string
    {
        return <<<'JAVASCRIPT'
        <script>
        function toggleLogger() {
            const container = document.getElementById('custom-logger-container');
            const content = document.getElementById('logger-content');
            if (content.style.display === 'none') {
                content.style.display = 'block';
                container.style.maxHeight = '300px';
            } else {
                content.style.display = 'none';
                container.style.maxHeight = '40px';
            }
        }
        
        function clearLogger() {
            const content = document.getElementById('logger-content');
            content.innerHTML = '<div style="color: #666; padding: 10px; text-align: center;">Logs cleared</div>';
        }
        
        // Auto-scroll to bottom
        window.addEventListener('load', function() {
            const container = document.getElementById('custom-logger-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'L') {
                toggleLogger();
                e.preventDefault();
            }
        });
        </script>
        JAVASCRIPT;
    }
}