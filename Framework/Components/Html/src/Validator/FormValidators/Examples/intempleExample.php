<?php
// Output CSS classes for error messages
$errorClasses = ValidationMessageService::getErrorClasses();
?>
<div class="<?= implode(' ', $errorClasses) ?>">Error message here</div>