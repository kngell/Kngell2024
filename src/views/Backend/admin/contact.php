<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/pages/contact') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main contact" id="main">
    <!-- Content -->
    <div class="contact__header">
        <div class="title">
            <h4 class="title__text">Contact</h4>
            <nav class="title__breadcrumbs">
                <ul class="breadcrumbs-list">
                    <li class="breadcrumbs-list__item">
                        <a href="#" class="breadcrumbs-list__item--link">Dashboard</a>
                    </li>
                    <li class="breadcrumbs-list__item">
                        <a href="#" class="breadcrumbs-list__item--link active">Contact</a>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="user-action">
            <button class="btn btn--outlined btn--md-compact btn--icon-left">
                <span class="btn__icon">
                    <svg class="icon cancel" aria-label="Cancel" role="img">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-close"></use>
                    </svg>
                </span>
                <span class="btn__label">Cancel</span>
            </button>
            <button class="btn btn--primary btn--md-compact btn--icon-left">
                <span class="btn__icon">
                    <svg class="icon plus" aria-label="Plus" role="img">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-plus"></use>
                    </svg>
                </span>
                <span class="btn__label">Add Contact</span>
            </button>
        </div>
    </div>
    <!-- Fin Content -->
    <div class="contact__body">
        <div class="frm-wrapper">
            <form action="" class="frm-wrapper__form">
                <div class="input-box">
                    <input type="text" class="input-box__input" id="fname" name="first_name"
                        placeholder="Type first name here..." required>
                    <label for="fname" class="input-box__label">First Name</label>
                    <span class="input-box__hint-text"></span>
                </div>
                <div class="input-box">
                    <input type="text" class="input-box__input" id="lname" name="last_name"
                        placeholder="Type last name here..." required>
                    <label for="lname" class="input-box__label">Last Name</label>
                    <span class="input-box__hint-text"></span>
                </div>
                <div class="input-box span-all">
                    <input type="email" class="input-box__input" id="email" name="email"
                        placeholder="Type email here..." required>
                    <label for="email" class="input-box__label">Email</label>
                    <span class="input-box__hint-text"></span>
                </div>
                <fieldset class="radio-group span-all">
                    <legend class="radio-group__legend">QueryType</legend>
                    <div class="input-box input-box--pill-style">
                        <input type="radio" class="input-box__input" id="general" name="query" value="General Enquiry"
                            required>
                        <label for="general" class="input-box__label">General Enquiry</label>
                    </div>
                    <div class="input-box input-box--pill-style">
                        <input type="radio" class="input-box__input" id="support" name="query" value="Support request"
                            required>
                        <label for="support" class="input-box__label">Support request</label>
                    </div>
                </fieldset>

                <div class="input-box span-all">
                    <textarea class="input-box__textarea" id="message" name="message"
                        placeholder="Type your message here..." required></textarea>
                    <label for="message" class="input-box__label">Message</label>
                    <span class="input-box__hint-text"></span>
                </div>
                <div class="input-box span-all">
                    <input type="checkbox" class="input-box__input" id="consent" name="consent" required>
                    <label for="consent" class="input-box__label">
                        <span class="input-box__label--required">I consent to my data being processed</span>
                    </label>
                    <span class="input-box__hint-text"></span>
                </div>
                <button class="btn btn--primary btn--md span-all">Submit</button>
            </form>
        </div>

    </div>

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();