<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= $this->getPageTitle()?>
    <link rel="shortcut icon" href="data:," />
    <!-- css/librairies/librairy -->
    <?= $this->css('css/librairies/librairy') ?? '' ?>
    <!-- Main style -->
    <?= $this->css('css/frontend/main/main') ?? '' ?>
    <noscript>
        <div style="padding: 20px; background-color: #ffe0b2; border: 1px solid #ff9800; text-align: center;">
            <p>This website requires JavaScript to function properly. Please enable JavaScript in your browser
                settings.</p>
        </div>
    </noscript>
    <?php if ($this->isDevEnv()) :?>
    <script async>
    (function() {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsHost = window.location.host;
        const wsUrl = `${protocol}//${wsHost}/ws`;

        let ws;

        function connectWebSocket() {
            ws = new WebSocket(wsUrl);

            ws.onopen = function() {
                console.log('[Custom Reload] WebSocket connection opened.');
            };

            ws.onmessage = function(event) {
                const data = JSON.parse(event.data);
                if (data.type === 'full-reload') {
                    console.log(`[Custom Reload] Full reload triggered by server. Reason: ${data.reason}`);
                    // The page will reload, so no need for explicit ws.close() here.
                    window.location.reload(true); // Forces a full reload
                } else if (data.type === 'css-update') {
                    console.log(`[Custom Reload] CSS update triggered by server. Reason: ${data.reason}`);
                    const links = document.querySelectorAll("link[rel='stylesheet']");
                    links.forEach((link) => {
                        const url = new URL(link.href);
                        url.searchParams.set("reload", Date.now());
                        link.href = url.href;
                    });
                }
                // Add any other message types if you later decide to handle them differently
            };

            ws.onclose = function(event) {
                console.log('[Custom Reload] WebSocket connection closed.', event.code, event.reason);
                // Attempt to reconnect after a short delay if the closure wasn't intentional (e.g., manual reload)
                // This is crucial for situations where the server might restart or if the reload somehow fails
                if (!event.wasClean && event.code !== 1000) { // Code 1000 is normal closure
                    console.log('[Custom Reload] Attempting to reconnect...');
                    setTimeout(connectWebSocket, 3000); // Try reconnecting after 3 seconds
                }
            };

            ws.onerror = function(error) {
                console.error('[Custom Reload] WebSocket error:', error);
                ws.close(); // Close on error to trigger onclose and reconnection attempt
            };
        }

        connectWebSocket(); // Initial connection
    })();
    </script>
    <?php endif; ?>
</head>

<body class="page-body">
    <!-- Main Header -->
    <header class="header">
        <div class="header-top">
            <!-- Header Top -->
            <?= $headerTop ?? '' ?>
        </div>
        <div class="header-bottom">
            <?= $headerBottom ?? '' ?>
        </div>
    </header>