<footer class="footer">
    <div class="container footer__container">
        <div class="footer__container--info">
            <div class="about">
                <a href="#" class="logo-container about__logo">
                    <svg class="logo">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-logo" alt="Logo" class="image">
                        </use>
                    </svg>
                </a>
                <p class="about__text">We are a residential interior design firm located in Portland. Our
                    boutique-studio offers more than</p>
            </div>
            <nav class="footer-navigation">
                <div class="footer-navigation__container">
                    <h5 class="navigation-title">Services</h5>
                    <ul class="navigation-list">
                        <li class="navigation-list__item">
                            <a href="" class="navigation-list__item--link">
                                Bonus program
                            </a>
                        </li>
                        <li class="navigation-list__item">
                            <a href="" class="navigation-list__item--link">
                                Gift cards
                            </a>
                        </li>
                        <li class="navigation-list__item">
                            <a href="" class="navigation-list__item--link">
                                Credit and payment
                            </a>
                        </li>
                        <li class="navigation-list__item">
                            <a href="" class="navigation-list__item--link">
                                Service contracts
                            </a>
                        </li>
                        <li class="navigation-list__item">
                            <a href="" class="navigation-list__item--link">
                                Non-cash account
                            </a>
                        </li>
                        <li class="navigation-list__item">
                            <a href="" class="navigation-list__item--link">
                                Payment
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="footer-navigation__container">
                    <h5 class="navigation-title">Assistance to the buyer</h5>
                    <ul class="navigation-list">
                        <li class="navigation-list__item">
                            <a href="" class="navigation-list__item--link">
                                Find an order
                            </a>
                        </li>
                        <li class="navigation-list__item">
                            <a href="" class="navigation-list__item--link">
                                Terms of delivery
                            </a>
                        </li>
                        <li class="navigation-list__item">
                            <a href="" class="navigation-list__item--link">
                                Exchange and return of goods
                            </a>
                        </li>
                        <li class="navigation-list__item">
                            <a href="" class="navigation-list__item--link">
                                Guarantee
                            </a>
                        </li>
                        <li class="navigation-list__item">
                            <a href="" class="navigation-list__item--link">
                                Frequently asked questions
                            </a>
                        </li>
                        <li class="navigation-list__item">
                            <a href="" class="navigation-list__item--link">
                                Terms of use of the site
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
        <div class="footer__container--socials">
            <div class="social-container">
                <svg class="icon twitter" aria-label="Twitter" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-twitter">
                    </use>
                </svg>
            </div>
            <div class="social-container">
                <svg class="icon twitter" aria-label="Facebook" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-facebook">
                    </use>
                </svg>
            </div>
            <div class="social-container">
                <svg class="icon twitter" aria-label="TikTok" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-tiktok">
                    </use>
                </svg>
            </div>
            <div class="social-container">
                <svg class="icon twitter" aria-label="Youtube" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-youtube">
                    </use>
                </svg>
            </div>
        </div>
    </div>
</footer>
<?= $this->js('runtime') ?>
<?= $this->js('js/librairies/librairy') ?>
<?= $this->js('js/frontend/main/main')?>
</body>

</html>