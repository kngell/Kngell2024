<?php

declare(strict_types=1);
class BrowserLogger
{
    private static array $messages = [];

    public static function log(string $message): void
    {
        self::$messages[] = $message;
        error_log($message);
    }

    public static function getMessages(): array
    {
        return self::$messages;
    }

    public static function display(): void
    {
        if (!empty(self::$messages)) {
            echo "<div style='background: #1e1e1e; color: #00ff00; padding: 20px; margin: 20px 0; border: 2px solid #ff00ff; font-family: monospace; font-size: 14px; border-radius: 5px;'>";
            echo "<strong style='color: #ffff00;'>🛠️ DEBUG LOG</strong><br><br>";
            foreach (self::$messages as $message) {
                // Add some color coding
                if (str_contains($message, '===')) {
                    echo "<strong style='color: #ff00ff;'>" . htmlspecialchars($message) . '</strong><br>';
                } elseif (str_starts_with($message, 'Field:')) {
                    echo "<span style='color: #00ffff;'>" . htmlspecialchars($message) . '</span><br>';
                } elseif (str_starts_with($message, '  ')) {
                    echo "<span style='color: #90ee90; margin-left: 20px;'>" . htmlspecialchars($message) . '</span><br>';
                } else {
                    echo htmlspecialchars($message) . '<br>';
                }
            }
            echo '</div>';
        }

        // Clear messages after displaying (optional)
        self::$messages = [];
    }

    public static function clear(): void
    {
        self::$messages = [];
    }
}