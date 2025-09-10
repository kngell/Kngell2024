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
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        display: flex;
        min-height: 100vh;
        background-color: #f5f7fb;
        color: #333;
    }

    .dashboard {
        display: flex;
        width: 100%;
        position: relative;
    }

    /* Sidebar Styles */
    .dashboard__aside {
        position: sticky;
        top: 0;
        align-self: start;
        background-color: #fff;
        box-shadow: 0 3px 4px 0 rgba(0, 0, 0, 0.12);
        padding: 0.5rem 1em;
        min-height: 100vh;
        min-height: 100dvh;
        width: 260px;
        transition: width 0.3s ease;
        z-index: 1000;
    }

    .dashboard__aside.collapsed {
        width: 70px;
        position: relative;
        overflow: visible;
    }

    .dashboard__aside.collapsed .logo-container {
        width: 36px;
    }

    .dashboard__aside.collapsed span {
        display: none;
    }

    .dashboard__aside.collapsed .menu-group__title span {
        display: none;
    }

    .dashboard__aside.collapsed .menu-group__list-item--dropdown-button .icon.arrow-down {
        display: none;
    }

    .dashboard__aside.collapsed .search-box {
        display: none;
    }

    .logo-box {
        display: flex;
        height: 80px;
        padding: 12px 20px;
        justify-content: space-between;
        align-items: center;
    }

    .logo-container {
        width: 120px;
        height: 36px;
        flex-shrink: 0;
        transition: width 0.3s ease;
        background: #3498db;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        border-radius: 4px;
    }

    .icon-container {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        color: #1d1d29;
        background-color: transparent;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .icon-container:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .icon-container.rotate {
        transform: rotate(180deg);
    }

    .icon-container .icon {
        width: 24px;
        height: 24px;
        transition: transform 0.15s ease;
    }

    .search-box {
        width: 100%;
        display: flex;
        padding: 12px 20px;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
    }

    .search-box form {
        display: flex;
        width: 100%;
        position: relative;
    }

    .search-box__btn {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        z-index: 2;
    }

    .search-box__input {
        width: 100%;
        padding: 10px 10px 10px 40px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.3s;
    }

    .search-box__input:focus {
        outline: none;
        border-color: #3498db;
    }

    .menu-group {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .menu-group__title {
        display: flex;
        padding: 30px 20px 8px 20px;
        align-items: center;
        align-self: stretch;
        position: relative;
    }

    .menu-group__title::after {
        content: "";
        height: 1px;
        background-color: #e0e0e0;
        width: 100%;
        position: absolute;
        bottom: 0;
        left: 0;
    }

    .menu-group__title span {
        flex: 1 0 0;
        color: #7f8c8d;
        font-size: 14px;
        font-weight: 500;
    }

    .menu-group__list {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        align-self: stretch;
        width: 100%;
        list-style: none;
    }

    .menu-group__list-item {
        width: 100%;
        position: relative;
        margin-bottom: 4px;
    }

    .menu-group__list-item--link,
    .menu-group__list-item--dropdown-button {
        display: flex;
        padding: 12px 20px;
        align-items: center;
        gap: 12px;
        align-self: stretch;
        border-radius: 6px;
        color: #2c3e50;
        font-size: 16px;
        font-weight: 400;
        text-decoration: none;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .menu-group__list-item--link .icon,
    .menu-group__list-item--dropdown-button .icon {
        color: #7f8c8d;
        flex-shrink: 0;
        width: 20px;
        height: 20px;
    }

    .menu-group__list-item--link .icon.arrow-down,
    .menu-group__list-item--dropdown-button .icon.arrow-down {
        fill: none;
        transition: transform 0.3s ease-in-out;
        margin-left: auto;
    }

    .menu-group__list-item--link .icon.arrow-down.rotated,
    .menu-group__list-item--dropdown-button .icon.arrow-down.rotated {
        transform: rotate(180deg);
    }

    .menu-group__list-item--link span,
    .menu-group__list-item--dropdown-button span {
        flex-grow: 1;
    }

    .menu-group__list-item--link:hover,
    .menu-group__list-item--dropdown-button:hover {
        background-color: rgba(52, 152, 219, 0.1);
        color: #3498db;
    }

    .menu-group__list-item--link:hover .icon,
    .menu-group__list-item--dropdown-button:hover .icon {
        color: #3498db;
    }

    .menu-group__list-item--dropdown-button {
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        cursor: pointer;
    }

    .menu-group__list-item--dropdown-menu {
        display: grid;
        grid-template-rows: 0fr;
        transition: grid-template-rows 0.3s ease-in-out;
        padding-left: 52px;
        position: relative;
        z-index: 1001;
    }

    .dashboard__aside.collapsed .menu-group__list-item--dropdown-menu {
        position: absolute;
        left: 70px;
        top: 0;
        width: 200px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        padding: 10px 0;
        z-index: 1002;
    }

    .menu-group__list-item--dropdown-menu>div {
        overflow: hidden;
    }

    .menu-group__list-item--dropdown-menu.show {
        grid-template-rows: 1fr;
    }

    .dropdown-menu__item {
        list-style: none;
        margin: 4px 0;
    }

    .dropdown-menu__item.active .dropdown-menu__item--link {
        background-color: rgba(52, 152, 219, 0.1);
        color: #3498db;
        font-weight: 600;
        border-radius: 6px;
    }

    .dropdown-menu__item--link {
        display: block;
        padding: 8px 16px;
        color: #7f8c8d;
        text-decoration: none;
        border-radius: 6px;
        transition: all 0.3s ease;
        font-size: 14px;
        white-space: nowrap;
    }

    .dropdown-menu__item--link:hover {
        color: #3498db;
        background-color: rgba(52, 152, 219, 0.1);
    }

    .menu-group__list-item.active .menu-group__list-item--link,
    .menu-group__list-item.active .menu-group__list-item--dropdown-button {
        background-color: rgba(52, 152, 219, 0.1);
        color: #3498db;
        font-weight: 600;
    }

    /* Main Content */
    .main-content {
        flex: 1;
        padding: 30px;
        overflow-y: auto;
        transition: margin-left 0.3s ease;
        margin-left: 260px;
    }

    .main-content.sidebar-collapsed {
        margin-left: 70px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 600;
        color: #2c3e50;
    }

    .toggle-sidebar {
        background: #3498db;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .toggle-sidebar:hover {
        background: #2980b9;
    }

    .card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
    }

    .card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #2c3e50;
    }

    .card-content {
        color: #7f8c8d;
        line-height: 1.6;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
        .dashboard__aside {
            position: fixed;
            transform: translateX(-100%);
        }

        .dashboard__aside.visible {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0 !important;
        }

        .dashboard__aside.collapsed {
            width: 260px;
            transform: translateX(-100%);
        }

        .dashboard__aside.collapsed.visible {
            transform: translateX(0);
        }

        .mobile-toggle {
            display: flex !important;
        }

        .dashboard__aside.collapsed .menu-group__list-item--dropdown-menu {
            position: static;
            left: auto;
            top: auto;
            width: auto;
            background: transparent;
            box-shadow: none;
            padding: 0 0 0 52px;
        }
    }

    @media (min-width: 993px) {
        .dashboard__aside {
            position: sticky;
            transform: translateX(0) !important;
        }

        .main-content.sidebar-expanded {
            margin-left: 260px;
        }

        .main-content.sidebar-collapsed {
            margin-left: 70px;
        }

        .mobile-toggle {
            display: none !important;
        }
    }

    .mobile-toggle {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1001;
        background: #3498db;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        display: none;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
    }

    .content-section {
        margin-top: 30px;
        padding: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .section-title {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #2c3e50;
    }

    .instructions {
        background: #e8f4fc;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
        border-left: 4px solid #3498db;
    }
    </style>
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
    <button class="mobile-toggle" id="mobile-toggle">
        <i class="fas fa-bars"></i>
    </button>