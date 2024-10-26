  <!-- Start footer -->
  <!-- Comments -->
  <?= $comments ?? ''?>
  <!-- End Comments -->
  <footer id="footer" class="mt-5 pt-5 pb-3">
     <div class="container">
        <div class="row mx-auto mt-5">
           <div class="footer-item col-lg-3 col-md-6 col-12">
              <h5 class="pb-2">About Us</h5>
              <img src="../../../../../assets/img/logo2.png" alt="" class="pb-3">
              <p>Lorem ipsum dolor sit, amet consectetur adipisicing
                 elit.
                 Quaerat ipsum quisquam libero?
              </p>
           </div>
           <div class="footer-item col-lg-3 col-md-6 col-12 mb-3">
              <h5 class="pb-2">Newsletter</h5>
              <form class="form-row">
                 <div class="col">
                    <input type="text" class="form-control" placeholder="Email *">
                 </div>
                 <div class="col">
                    <button type="submit" class="btn btn-primary mb-2">Subscribe</button>
                 </div>
              </form>
              <p>Lorem ipsum dolor sit, amet consectetur adipisicing
                 elit.
                 Quaerat ipsum quisquam libero?
              </p>
           </div>
           <div class="footer-item col-lg-3 col-md-6 col-12 mb-3">
              <h5 class="pb-2">Information</h5>
              <div class="d-flex flex-column flex-wrap">
                 <a href="#" class="font-rale font-size-14 text-white-50 pb-1">About Us</a>
                 <a href="#" class="font-rale font-size-14 text-white-50 pb-1">Delevery informations</a>
                 <a href="#" class="font-rale font-size-14 text-white-50 pb-1">Privacy policy</a>
                 <a href="#" class="font-rale font-size-14 text-white-50 pb-1">Terms & conditions</a>
              </div>
           </div>
           <div class="footer-item col-lg-3 col-md-6 col-12 mb-3">
              <h5 class="pb-2">Recent news</h5>
              <div class="row">
                 <img src="<?=ImageManager::asset_img('insta/1.jpg')?>" alt="" class="img-fluid w-25 h-100 m-2 p-0">
                 <img src="<?=ImageManager::asset_img('insta/2.jpg')?>" alt="" class="img-fluid w-25 h-100 m-2 p-0">
                 <img src="<?=ImageManager::asset_img('insta/3.jpg')?>" alt="" class="img-fluid w-25 h-100 m-2 p-0">
                 <img src="<?=ImageManager::asset_img('insta/4.jpg')?>" alt="" class="img-fluid w-25 h-100 m-2 p-0">
                 <img src="<?=ImageManager::asset_img('insta/5.jpg')?>" alt="" class="img-fluid w-25 h-100 m-2 p-0">
              </div>
           </div>
        </div>
     </div>
     <!-- CopyRights -->
     <div class="copyright">
        <div class="container">
           <div class="row mx-auto">
              <div class="col-lg-3 col-md-6 col-12 mb-4">
                 <img src="../../../../../assets/img/payment.png" alt="">
              </div>
              <div class="c-copy col-lg-4 col-md-6 col-12 text-nowrap mb-2">
                 <p class="font-rale font-size-14">&copy; Copyrights 2021 Design by <a href="#" class="color-second">
                       Kngell</a>
                 </p>
              </div>
              <?= $socialsMedia ?? ''?>

           </div>
        </div>


     </div>
  </footer>
  <!-- End footer -->
  <?= $jv_script_from_php ?? ''?>
  <!-- Librairies -->
  <?= $this->asset('js/librairies/frontlib', 'js') ?? '' ?>
  <!-- Common vendor -->
  <?= $this->asset('commons/client/commonVendor', 'js') ?? '' ?>
  <!-- Custom Common Modules  -->
  <?= $this->asset('commons/client/commonCustomModules', 'js') ?? '' ?>
  <!-- Plugins -->
  <?= $this->asset('js/plugins/homeplugins', 'js') ?? '' ?>
  <!-- Mainjs -->
  <?= $this->asset('js/client/main/main', 'js') ?? '' ?>
  <!-- Custom -->
  <?= $this->content('footer'); ?>
  </body>

  </html>