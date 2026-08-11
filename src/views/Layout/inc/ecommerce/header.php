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
    <?= $this->content('head'); ?>
    <noscript>
        <div style="padding: 20px; background-color: #ffe0b2; border: 1px solid #ff9800; text-align: center;">
            <p>This website requires JavaScript to function properly. Please enable JavaScript in your browser
                settings.</p>
        </div>
    </noscript>

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