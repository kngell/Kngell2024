  <!-- Start footer -->
  <footer class="dashboard__footer">
      &copy; 2025 ShopAdmin. All rights reserved.
  </footer>

  <!-- Runtime Webpack -->
  <?= $this->js('runtime') ?>
  <?= $this->js('js/librairies/librairy') ?>
  <!-- Mainjs -->
  <?= $this->js('js/backend/main/main')?>
  <!-- Custom -->
  <?= $this->content('footer'); ?>

  </body>

  </html>