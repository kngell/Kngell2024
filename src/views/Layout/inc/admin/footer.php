  <!-- Start footer -->
  <footer class="admin-footer u-padding-20">
     <div class="k-container">
        <div class="-k-offset-12 k-offset-md-3 k-offset-lg-2">
           <p>&copy;2025 / K&rsquo;nGELL / Designed &amp; developped by <a href="#"
                 class="k-btn k-btn-secondary k-btn-link u-transform-none">K&rsquo;nGELL IT &amp; Technology</a></p>
        </div>
     </div>
  </footer>
  <!-- Runtime Webpack -->
  <?= $this->js('runtime') ?>
  <!-- Librairies -->
  <?= $this->js('commons/frontend/vendors') ?>
  <?= $this->js('js/librairies/backend/lib') ?>
  <!-- Common vendor -->
  <?= $this->js('commons/client/commonVendor') ?>
  <!-- Custom Common Modules  -->
  <?= $this->js('commons/admin/commonCustomModules', 'js') ?>
  <!-- Plugins -->
  <?= $this->js('js/plugins/homeplugins', 'js') ?>
  <!-- Mainjs -->
  <?= $this->js('js/backend/main/main')?>
  <!-- Custom -->
  <?= $this->content('footer'); ?>
  </body>

  </html>