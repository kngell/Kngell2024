<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Error') ?></title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace;
        background: #1a1a2e;
        color: #e0e0e0;
        padding: 20px;
        line-height: 1.6;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .header {
        background: #e74c3c;
        color: white;
        padding: 20px 30px;
        border-radius: 8px 8px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header h1 {
        font-size: 1.3em;
        word-break: break-word;
    }

    .header .code {
        font-size: 2em;
        font-weight: bold;
        opacity: 0.8;
    }

    .section {
        background: #16213e;
        padding: 20px 30px;
        border-bottom: 1px solid #0f3460;
    }

    .section:last-child {
        border-radius: 0 0 8px 8px;
    }

    .section h2 {
        color: #e94560;
        margin-bottom: 12px;
        font-size: 1.1em;
    }

    .location {
        color: #888;
        font-size: 0.9em;
        margin-top: 8px;
    }

    pre {
        background: #0a0a1a;
        padding: 15px;
        border-radius: 4px;
        overflow-x: auto;
        font-size: 0.85em;
        line-height: 1.6;
        color: #ccc;
    }

    .chain-item {
        padding: 10px 15px;
        border-left: 3px solid #e94560;
        margin-bottom: 10px;
    }

    .resolution-stack {
        list-style: decimal;
        padding-left: 20px;
    }

    .resolution-stack li {
        padding: 4px 0;
        font-family: monospace;
    }

    .resolution-stack li.failed {
        color: #e94560;
        font-weight: bold;
    }
    </style>
</head>

<body>