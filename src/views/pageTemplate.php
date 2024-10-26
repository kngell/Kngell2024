<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<link
    href="<?= $this->asset('path', 'css') ?? ''?>"
    rel="stylesheet" type="text/css">
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
    <!-- Content -->


    <!-- Fin Content -->
    <input type="hidden" id="ip_address" style="display:none"
        value="<?=H_visitors::getIP()?>">
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<script type="text/javascript"
    src="<?= $this->asset('pathjs', 'js') ?? ''?>">
</script>
<?php $this->end();
