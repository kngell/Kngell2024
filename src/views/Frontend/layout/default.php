<?php declare(strict_types=1);
require_once 'inc/default/header.php'; ?>
<!----------------Navbar-------------------->
<?= $navComponent ?? ''?>
<!----------------xNavbar-------------------->
<?= $flashMsgComponent ?? '' ?>
<!----------------Body----------------------->
<?= $this->content('body'); ?>
<!----------------xBody---------------------->
<!----------------Modals-------------------->
<?= $authenticationComponent ?? '' ?>
<!----------------xModals-------------------->

<?php require_once 'inc/default/footer.php';