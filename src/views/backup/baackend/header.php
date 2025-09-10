<header class="dashboard__header">
    <div class="header-left">
        <h5 class="header-left__title">Add Product</h5>
        <button class="header-left__mobile-toggle">
            <svg class="icon double-arrow-left" aria-label="Double Arrow Left" role="img">
                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-hamburger-menu" class="image">
                </use>
            </svg>
        </button>
    </div>

    <div class="header-right">

        <form class="search-form">
            <button type="submit" class="search-form__btn">
                <svg class="icon search">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search" alt="search" class="search">
                    </use>
                </svg>
            </button>
            <input type="text" name="search" id="search-form--input-id" class="search-form__input"
                placeholder="Search...">
        </form>

        <div class="user-action">
            <button class="user-action__notification">
                <svg class="icon notification" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                    <rect class="bg" />
                    <path class="bell"
                        d="M10 21H14C14 22.1 13.1 23 12 23C10.9 23 10 22.1 10 21ZM21 19V20H3V19L5 17V11C5 7.9 7 5.2 10 4.3V4C10 2.9 10.9 2 12 2C13.1 2 14 2.9 14 4V4.3C17 5.2 19 7.9 19 11V17L21 19ZM17 11C17 8.2 14.8 6 12 6C9.2 6 7 8.2 7 11V18H17V11Z" />
                    <circle class="dot" cx="18" cy="7" r="3" />
                </svg>
            </button>
            <div class="user-action__settings">
                <button class="icon-container">
                    <svg class="icon light" aria-label="Dashboard" role="img">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-light" class="image">
                        </use>
                    </svg>
                </button>
            </div>
            <div class="user-action__submenu">
                <img src="../../../../assets/img/ecommerce/portrait.png" alt="Portrait" class="image">

            </div>
        </div>
    </div>

</header>