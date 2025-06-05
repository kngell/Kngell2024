<?php declare(strict_types=1);
require_once 'inc/admin/head.php'; ?>

<!----------------Header-------------------->
<?php require_once 'inc/admin/header.php'; ?>
<!----------------xNavbar-------------------->
<!----------------Sidenavbar-------------------->
<?= $sideNavComponent ?? ''?>
<!----------------xSidenavbar-------------------->
<!----------------Body----------------------->
<?= $this->content('body'); ?>
<!----------------xBody---------------------->
<?php require_once 'inc/admin/footer.php';