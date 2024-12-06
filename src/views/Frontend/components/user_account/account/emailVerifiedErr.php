<?php declare(strict_types=1);
$this->setSiteTitle('Feedback'); ?>
<?php $this->start('head'); ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>

<div class="container">
    <div class="row justify-content-center my-2">
        <div class="col-lg-6 mt-4" id="showAlert">
            <h2 class="text-center"><?=$this->view_data?>
            </h2>
        </div>

    </div>
</div>

<?php $this->end(); ?>
<?php $this->start('footer')?>
<?php $this->end();
