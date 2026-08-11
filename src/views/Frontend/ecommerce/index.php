<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Custom-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>

<?php $this->start('body'); ?>
<main id="main-site" class="main">
    <!-- Hero Section -->
    <section class="hero-section">
        <?= $heroSection ?? '' ?>
    </section>

    <!-- Small Banner Section -->
    <section class="small-banner-section">
        <?= $smallBannerSection ?? '' ?>
    </section>

    <!-- Category Section -->
    <section class="category-section">
        <?= $categorySection ?? '' ?>
    </section>

    <!-- New Arrival Section -->
    <section class="product-section">
        <?= $productSection ?? '' ?>
    </section>

    <!-- Big Banner Section -->
    <section class="big-banner-section">
        <?= $bigBannerSection ?? '' ?>
    </section>

    <!-- Discount Section -->
    <section class="discount-section">
        <?= $discountSection ?? '' ?>
    </section>

    <!-- Summer Banner Section (Dynamic) -->
    <section class="banner-section">
        <?php if (!empty($summerBannerSection)): ?>
        <?php if (is_array($summerBannerSection)): ?>
        <?php foreach ($summerBannerSection as $part): ?>
        <?= $part ?? '' ?>
        <?php endforeach; ?>
        <?php elseif (is_string($summerBannerSection)): ?>
        <?= $summerBannerSection ?>
        <?php endif; ?>
        <?php endif; ?>
    </section>
</main>
<?php $this->end(); ?>

<?php $this->start('footer'); ?>
<!----------Custom--------->
<?= $this->js('path') ?>
<?php $this->end(); ?>