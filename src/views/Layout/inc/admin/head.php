<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="cache-control" content="no-cache">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="x-UA-compatible" content="IE=9">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index,follow">
    <meta name="csrftoken" content="<?= $this->token()?>" />
    <meta name="frm_name" content="<?=$this->getPageTitle()?>" />
    <?= $this->getPageTitle()?>
    <link rel="shortcut icon" href="data:," type="image/x-icon" />
    <!-- Main style -->
    <?= $this->css('css/librairies/librairy') ?? '' ?>
    <!-- Main style -->
    <?= $this->css('css/backend/admin/admin') ?? '' ?>
    <!-- CkEditor -->
    <?= $this->css('css/ckeditor/ckeditor', 'css') ?? '' ?>
    <?= $this->content('head'); ?>
    <?php if ($this->isDevEnv()) :?>
    <script async>
    (function() {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsHost = window.location.host;
        const wsUrl = `${protocol}//${wsHost}/ws`;

        let ws; // Declare ws outside to be accessible by connectWebSocket

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

<body id="body" class="dashboard">