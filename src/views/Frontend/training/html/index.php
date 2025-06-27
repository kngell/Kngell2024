<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/html/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>

<!-- Content -->
<div class="container">
    <header class="header">
        <!-- NAVIGATION -->
        <nav class="navbar">
            <a href="index.html"><img class="logo-md" src="../../../../assets/img/training/html-css/quill.svg"
                    alt="quill logo"></a>
            <ul class="navbar__connexion-btn">
                <li><a class="btn btn-primary-outline" href="login.html">Login</a></li>
                <li><a class="btn btn-primary" href="register.html">Register</a></li>
            </ul>
        </nav>

        <!-- HERO SECTION -->
        <section class="hero-section mb100">
            <h1 class="hero-heading mb10"><span class="highlight highlight-secondary">Mindful living</span> for the <br>
                digital
                world 📱</h1>
            <p class="hero-paragraph mb50">The ultimate bullet journal app to document, reflect, and embrace your
                personal
                journey</p>
            <ul class="hero-section__btns-container">
                <li><a class="btn btn-primary" href="register.html">Get started</a></li>
                <li><a class="btn btn-primary-outline" href="#features">Learn more</a></li>
            </ul>
        </section>
    </header>
    <main id="main-site">
        <!-- FEATURES SECTION -->
        <section class="features-section mb300">
            <h2 class="features-section__heading2 mb80">Finally, a <span class="highlight highlight-tertiary">simple and
                    easy</span>
                to use journal app</h2>
            <div>
                <h3 class="features-section__heading3 mb20">Write all your journals in a few clicks</h3>
                <p>Unlock the simplicity of online journaling - say goodbye to the hassle of pen and paper and hello to
                    effortless journaling with just a few clicks.</p>
                <img class="features-section__img" src="../../../../assets/img/training/html-css/features-1.png"
                    alt="simple online journaling">
            </div>
            <div>
                <h3 class="features-section__heading3 mb20">Beautifully displayed and neatly organised</h3>
                <p>Not just words on a screen - our journals are beautifully displayed and neatly organised.</p>
                <img class="features-section__img" src="../../../../assets/img/training/html-css/features-2.png"
                    alt="beautifully displayed journals">
            </div>
            <div>
                <h3 class="features-section__heading3 mb20">Create and reflect from anywhere in the world</h3>
                <p>Whether you're at home or exploring the far corners of the world, create and reflect on your
                    experiences,
                    thoughts, and emotions from anywhere on the globe.</p>
                <img class="features-section__img mb20" src="../../../../assets/img/training/html-css/features-3.png"
                    alt="create journals anywhere">
            </div>
        </section>


        <!-- CTA SECTION -->
        <section class="cta-section mb300">
            <div class="cta-card">
                <h2 class="cta-heading mb30">Get started now</h2>
                <a class="btn btn-secondary" href="register.html">Get started</a>
            </div>
        </section>
    </main>
    <!-- FOOTER -->
    <footer class="footer">
        <a href="index.html"><img class="logo-sm" src="../../../../assets/img/training/html-css/quill.svg"
                alt="quill logo"></a>
        <ul class="footer__social-icons-container">
            <li><a href="https://www.instagram.com/">
                    <im class="social__icon" g src="../../../../assets/img/training/html-css/instagram.svg"
                        alt="instagram logo">
                </a>
            </li>
            <li><a href="https://www.facebook.com/"><img class="social__icon"
                        src="../../../../assets/img/training/html-css/facebook.svg" alt="facebook logo"></a>
            </li>
            <li><a href="https://www.tiktok.com/"><img class="social__icon"
                        src="../../../../assets/img/training/html-css/tiktok.svg" alt="tiktok logo"></a></li>
            <li><a href="https://www.linkedin.com/"><img class="social__icon"
                        src="../../../../assets/img/training/html-css/linkedin.svg" alt="linkedin logo"></a>
            </li>
            <li><a href="https://www.youtube.com/"><img class="social__icon"
                        src="../../../../assets/img/training/html-css/youtube.svg" alt="youtube logo"></a></li>
        </ul>
        <ul class="footer__links-container">
            <li><a href="index.html">Home</a></li>
            <li><a href="login.html">Login</a></li>
            <li><a href="register.html">Register</a></li>
        </ul>
        <p class="small-text">Made with ♥︎ in London</p>
    </footer>
</div>

<!-- Fin Content -->

<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/frontend/training/html/main') ?>

<?php $this->end();