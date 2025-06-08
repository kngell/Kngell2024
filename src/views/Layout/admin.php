<?php declare(strict_types=1);
require_once 'inc/admin/head.php';

if ($this->request->get('request_uri') !== '/login'):?>
<!----------------Header-------------------->
<?php require_once 'inc/admin/header.php'; ?>
<!----------------xNavbar-------------------->
<!----------------Sidebar-------------------->
<?php require_once 'inc/admin/sidebar.php'; ?>
<!----------------xSidebar-------------------->
<!----------------Sidenavbar-------------------->
<?= $sideNavComponent ?? ''?>
<!----------------xSidenavbar-------------------->

<?php endif ?>

<!----------------Body----------------------->
<?= $this->content('body'); ?>
<!----------------xBody---------------------->
<?php require_once 'inc/admin/footer.php';
