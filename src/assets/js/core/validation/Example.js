// You can output the validation messages to JavaScript
const validationMessages = JSON.parse(
  "<?= json_encode(ValidationMessageService::getAllMessages()); ?>",
);

// Use in your frontend validation
function validateField(field, rule, value) {
  if (rule === "required" && !value) {
    const message = validationMessages.required.replace("%s", field.displayName);
    showError(field, message);
  }
}

<?php
// In your base template or a dedicated script
?>
<script>
window.ValidationMessages = {
    messages: <?= json_encode(ValidationMessageService::getAllMessages()); ?>,
    classes: {
        hint: <?= json_encode(ValidationMessageService::getHintClasses()); ?>,
        error: <?= json_encode(ValidationMessageService::getErrorClasses()); ?>
    }
};
</script>
