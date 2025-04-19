<?php declare(strict_types=1);
require_once 'inc/default/header.php'; ?>
<header class="header" id="header">
   <!----------------Navbar-------------------->
   <?= $navComponent ?? ''?>
   <!----------------xNavbar-------------------->
</header>
<!----------------Body----------------------->
<?= $this->content('body'); ?>
<!----------------xBody---------------------->
<?php require_once 'inc/default/footer.php';