  <!-- Start footer -->

  <!-- Librairies -->
  <?= $this->js('js/librairies/adminlib') ?>
  <!-- Common vendor -->
  <?= $this->js('commons/client/commonVendor', 'js') ?>
  <!-- Custom Common Modules  -->
  <?= $this->js('commons/admin/commonCustomModules', 'js') ?>
  <!-- Plugins -->
  <?= $this->js('js/plugins/homeplugins', 'js') ?>
  <!-- Mainjs -->
  <?= $this->js('js/admin/main')?>
  <!-- Custom -->
  <?= $this->content('footer'); ?>
  </body>

  </html>