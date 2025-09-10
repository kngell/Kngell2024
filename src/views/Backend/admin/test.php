<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
    <!-- Content -->
    <div class="dashboard">
        <!-- Sidebar Menu -->
        <aside class="dashboard__aside">
            <div class="logo-box">
                <div class="logo-container">
                    <div class="image">Logo</div>
                </div>
                <div class="icon-container toggle-btn" id="resize">
                    <svg class="icon double-arrow-left" aria-label="Double Arrow Left" viewBox="0 0 24 24" width="24"
                        height="24">
                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12l4.58-4.59z" fill="currentColor" />
                    </svg>
                </div>
            </div>

            <div class="search-box">
                <form class="search-box">
                    <button type="submit" class="search-box__btn">
                        <svg class="icon search" viewBox="0 0 24 24" width="20" height="20">
                            <path d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0016 9.5C16 5.91 13.09 3 9.5 3S3 
                            5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 
                            4.23-1.57l.27.28h.79l5 5L20.49 19l-5-5zm-6 
                            0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 
                            14 7.01 14 9.5 11.99 14 9.5 14z" fill="currentColor" />
                        </svg>
                    </button>
                    <input type="text" name="search" id="search-box--input-id" class="search-box__input"
                        placeholder="Search...">
                </form>
            </div>

            <div class="menu-group">
                <div class="menu-group__title">
                    <span>Menu</span>
                </div>
                <ul class="menu-group__list">
                    <li class="menu-group__list-item active">
                        <a href="#home" class="menu-group__list-item--link">
                            <svg class="icon home" aria-label="Home" viewBox="0 0 24 24" width="20" height="20">
                                <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8h5z" fill="currentColor" />
                            </svg>
                            <span>Home</span>
                        </a>
                    </li>

                    <li class="menu-group__list-item">
                        <a href="#dashboard" class="menu-group__list-item--link">
                            <svg class="icon main-dashboard" aria-label="Dashboard" viewBox="0 0 24 24" width="20"
                                height="20">
                                <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 
                                0h8V11h-8v10zm0-18v6h8V3h-8z" fill="currentColor" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="menu-group__list-item">
                        <button class="menu-group__list-item--dropdown-button">
                            <svg class="icon create-new-folder" aria-label="Create New Folder" viewBox="0 0 24 24"
                                width="20" height="20">
                                <path d="M20 6h-8l-2-2H4c-1.11 0-1.99.89-1.99 
                                2L2 18c0 1.11.89 2 2 2h16c1.11 0 
                                2-.89 2-2V8c0-1.11-.89-2-2-2zm-1 
                                8h-3v3h-2v-3h-3v-2h3V9h2v3h3v2z" fill="currentColor" />
                            </svg>
                            <span>Create</span>
                            <svg class="icon arrow-down" aria-label="Arrow down" viewBox="0 0 24 24" width="20"
                                height="20">
                                <path d="M7 10l5 5 5-5z" fill="currentColor" />
                            </svg>
                        </button>
                        <ul class="menu-group__list-item--dropdown-menu">
                            <li class="dropdown-menu__item"><a href="#folder"
                                    class="dropdown-menu__item--link">Folder</a></li>
                            <li class="dropdown-menu__item"><a href="#document"
                                    class="dropdown-menu__item--link">Document</a></li>
                            <li class="dropdown-menu__item"><a href="#project"
                                    class="dropdown-menu__item--link">Project</a></li>
                        </ul>
                    </li>

                    <li class="menu-group__list-item">
                        <button class="menu-group__list-item--dropdown-button">
                            <svg class="icon checklist" aria-label="Checklist" viewBox="0 0 24 24" width="20"
                                height="20">
                                <path d="M19 3h-4.18C14.4 1.84 13.3 
                                1 12 1c-1.3 0-2.4.84-2.82 
                                2H5c-1.1 0-2 .9-2 
                                2v14c0 1.1.9 2 2 
                                2h14c1.1 0 2-.9 
                                2-2V5c0-1.1-.9-2-2-2zm-7 
                                0c.55 0 1 .45 1 
                                1s-.45 1-1 
                                1-1-.45-1-1 
                                .45-1 1-1zm-2 
                                14l-4-4 1.41-1.41L10 
                                14.17l6.59-6.59L18 
                                9l-8 8z" fill="currentColor" />
                            </svg>
                            <span>Todo Lists</span>
                            <svg class="icon arrow-down" aria-label="Arrow down" viewBox="0 0 24 24" width="20"
                                height="20">
                                <path d="M7 10l5 5 5-5z" fill="currentColor" />
                            </svg>
                        </button>
                        <ul class="menu-group__list-item--dropdown-menu">
                            <li class="dropdown-menu__item"><a href="#private"
                                    class="dropdown-menu__item--link">Private</a></li>
                            <li class="dropdown-menu__item"><a href="#work" class="dropdown-menu__item--link">Work</a>
                            </li>
                            <li class="dropdown-menu__item"><a href="#coding"
                                    class="dropdown-menu__item--link">Coding</a></li>
                            <li class="dropdown-menu__item"><a href="#gardening"
                                    class="dropdown-menu__item--link">Gardening</a></li>
                            <li class="dropdown-menu__item"><a href="#school"
                                    class="dropdown-menu__item--link">School</a></li>
                        </ul>
                    </li>

                    <li class="menu-group__list-item">
                        <a href="#calendar" class="menu-group__list-item--link">
                            <svg class="icon calendar" aria-label="Calendar" viewBox="0 0 24 24" width="20" height="20">
                                <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 
                                0-2 .9-2 2v14c0 1.1.9 2 2 
                                2h14c1.1 0 2-.9 
                                2-2V5c0-1.1-.9-2-2-2zm0 
                                16H5V9h14v10zm0-12H5V5h14v2z" fill="currentColor" />
                            </svg>
                            <span>Calendar</span>
                        </a>
                    </li>

                    <li class="menu-group__list-item">
                        <a href="#profile" class="menu-group__list-item--link">
                            <svg class="icon person" aria-label="Person" viewBox="0 0 24 24" width="20" height="20">
                                <path d="M12 12c2.21 0 4-1.79 
                                4-4s-1.79-4-4-4-4 
                                1.79-4 4 1.79 4 4 
                                4zm0 2c-2.67 0-8 
                                1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" fill="currentColor" />
                            </svg>
                            <span>Profile</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1 class="page-title">Dropdowns in Collapsed Sidebar</h1>
            </div>

            <div class="card-grid">
                <div class="card">
                    <h2 class="card-title">Visible Dropdowns</h2>
                    <p class="card-content">Dropdown menus now appear outside the sidebar when it's collapsed, ensuring
                        they remain fully visible.</p>
                </div>
                <div class="card">
                    <h2 class="card-title">Clean Design</h2>
                    <p class="card-content">The dropdowns maintain the same styling and functionality whether the
                        sidebar is expanded or collapsed.</p>
                </div>
                <div class="card">
                    <h2 class="card-title">Responsive Behavior</h2>
                    <p class="card-content">On mobile devices, the dropdowns appear normally since the sidebar expands
                        to full width.</p>
                </div>
            </div>

            <div class="content-section">
                <h2 class="section-title">How It Works</h2>
                <p>When the sidebar is collapsed, dropdown menus are positioned absolutely outside the sidebar
                    container:</p>

                <div class="instructions">
                    <h3>Key CSS Changes:</h3>
                    <ul>
                        <li>Added <code>position: relative</code> and <code>overflow: visible</code> to collapsed
                            sidebar</li>
                        <li>Dropdown menus use <code>position: absolute</code> with <code>left: 70px</code> (sidebar
                            width)</li>
                        <li>Dropdowns have a fixed width and background to appear as floating panels</li>
                        <li>On mobile, dropdowns revert to their normal positioning</li>
                    </ul>
                </div>

                <p>Try collapsing the sidebar and opening the dropdowns to see the effect.</p>
            </div>
        </div>
    </div>

    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();