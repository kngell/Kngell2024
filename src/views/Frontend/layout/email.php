<?php declare(strict_types=1);
require_once 'inc/email/header.php'; ?>
<!----------------Body----------------------->
<?= $this->content('body'); ?>
<!----------------xBody---------------------->
<?php require_once 'inc/email/footer.php';
