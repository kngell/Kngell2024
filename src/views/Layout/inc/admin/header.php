<header class="dashboard__header header">
    <div class="header__mobile-menu icon-container">
        <svg class="icon mobile-menu" aria-label="Mobile Menu" role="img">
            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-mobile-menu" class="image">
            </use>
        </svg>
    </div>
    <div class="header__desktop-menu">
        <div class="header__desktop-menu--search-box">
            <form action="" method="post" class="search-form">
                <button type="submit" class="search-form__btn">
                    <svg class="icon search">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search" alt="search" class="search">
                        </use>
                    </svg>
                </button>
                <input type="text" name="search" id="search-form--input-id" class="search-form__input"
                    placeholder="Search...">
            </form>
        </div>
        <div class="header__desktop-menu--icon-box">
            <div class="icon-container calendar">
                <svg class="icon calendar__icon" aria-label="Calendar" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-calendar" class="image">
                    </use>
                </svg>
            </div>
            <div class="icon-container notification">
                <svg class="icon notification__icon" aria-label="Notification" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-notification" class="image">
                    </use>
                </svg>
                <div class="icon-badge notification_badge">
                    <span>3</span>
                </div>
            </div>
            <div class="icon-container email">
                <svg class="icon email__icon" aria-label="Email" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-email" class="image">
                    </use>
                </svg>
                <div class="icon-badge email__badge">
                    <span>64</span>
                </div>
            </div>
            <div class="icon-container img">
                <svg class="icon img__icon" aria-label="Image" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-img" class="image">
                    </use>
                </svg>
            </div>
        </div>
        <div class="header__desktop-menu--user-box">
            <div class="user-avatar">
                <div class="user-avatar__photo"></div>
                <div class="user-avatar__notification">
                    <svg class="icon avatar-notif__icon" aria-label="Avatar Notification" role="img">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-avatar-notif" class="image">
                        </use>
                    </svg>
                </div>
            </div>
            <div class="user-details">
                <span class="user-details__name">Jay Hargudson</span>
                <span class="user-details__position">Manager</span>
            </div>
            <div class="icon-container dropdown">
                <svg class="icon arrow-down" aria-label="Arrow Down" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down" class="image">
                    </use>
                </svg>
            </div>
        </div>
    </div>
</header>