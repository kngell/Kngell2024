<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/paypal/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
   <div class="mt-5 container">
      <span class="float-end">&nbsp;You are not logged in <a href="/login">login</a> </span>
      <span class="float-end"><a href="/paypal/cart">Your cart (4)</a> </span>
      <h1><span class="text-info">SUPER</span> <span class="text-danger">SHOP</span></h1>
      <p class="text-success">The best online store</p>
      <hr>
      <div class="row">
         <form method="POST" action="/paypal/update-quantities">
            <table class="table table-hover table-responsive">
               <thead>
                  <tr>
                     <th>Product</th>
                     <th>Quantity</th>
                     <th class="text-center">Price</th>
                     <th class="text-center">Subtotal</th>
                     <th></th>
                  </tr>
               </thead>
               <tbody>
                  <tr>
                     <td class="col-sm-8 col-md-6">
                        <div class="d-flex border p-3">
                           <img alt="" src="https://icons.iconarchive.com/icons/r34n1m4ted/chanel/72/WATCH-icon.png">
                           <div class="ml-2">
                              <h4>Watch</h4>
                              <h5>by Rolex</h5>
                           </div>
                        </div>
                     </td>
                     <td class="col-sm-1 col-md-1">
                        <input type="number" name="amounts[1]" min="0" class="form-control" value="1">
                     </td>
                     <td class="col-sm-1 col-md-1 text-center"><strong>$1450.55</strong></td>
                     <td class="col-sm-1 col-md-1 text-center"><strong>$1450.55</strong></td>
                     <td class="col-sm-1 col-md-1">
                        <a href="/paypal/from-cart/1" onclick="return confirm('Delete the product from the cart?');"
                           role="button" class="btn btn-danger">
                           Remove
                        </a>
                     </td>
                  </tr>
                  <tr>
                     <td class="col-sm-8 col-md-6">
                        <div class="d-flex border p-3">
                           <img alt=""
                              src="https://icons.iconarchive.com/icons/jonathan-rey/devices-pack-3/72/Smartphone-Android-Jelly-Bean-Samsung-Galaxy-S4-icon.png">
                           <div class="ml-2">
                              <h4>Smartphone</h4>
                              <h5>by Apple</h5>
                           </div>
                        </div>
                     </td>
                     <td class="col-sm-1 col-md-1">
                        <input type="number" name="amounts[2]" min="0" class="form-control" value="1">
                     </td>
                     <td class="col-sm-1 col-md-1 text-center"><strong>$250.45</strong></td>
                     <td class="col-sm-1 col-md-1 text-center"><strong>$250.45</strong></td>
                     <td class="col-sm-1 col-md-1">
                        <a href="/paypal/from-cart/2" onclick="return confirm('Delete the product from the cart?');"
                           role="button" class="btn btn-danger">
                           Remove
                        </a>
                     </td>
                  </tr>
                  <tr>
                     <td class="col-sm-8 col-md-6">
                        <div class="d-flex border p-3">
                           <img alt=""
                              src="https://icons.iconarchive.com/icons/oxygen-icons.org/oxygen/72/Devices-video-television-icon.png">
                           <div class="ml-2">
                              <h4>TV</h4>
                              <h5>by Panasonic</h5>
                           </div>
                        </div>
                     </td>
                     <td class="col-sm-1 col-md-1">
                        <input type="number" name="amounts[3]" min="0" class="form-control" value="2">
                     </td>
                     <td class="col-sm-1 col-md-1 text-center"><strong>$600</strong></td>
                     <td class="col-sm-1 col-md-1 text-center"><strong>$1200</strong></td>
                     <td class="col-sm-1 col-md-1">
                        <a href="/paypal/from-cart/3" onclick="return confirm('Delete the product from the cart?');"
                           role="button" class="btn btn-danger">
                           Remove
                        </a>
                     </td>
                  </tr>
                  <tr>
                     <td></td>
                     <td></td>
                     <td>
                        <button type="submit" class="btn btn-warning">
                           <span>Update&nbsp;quantities</span>
                        </button>
                     </td>
                     <td class="text-right">
                        <h3>Total price:</h3>
                     </td>
                     <td class="text-right">
                        <h3><strong>$2901</strong></h3>
                     </td>
                  </tr>
                  <tr>
                     <td></td>
                     <td></td>
                     <td></td>
                     <td>
                        <a href="products.html" role="button" class="btn btn-info">
                           Continue&nbsp;shopping
                        </a>
                     </td>
                     <td>
                        <a href="/paypal/checkout" role="button" class="btn btn-success">
                           Checkout
                        </a>
                     </td>
                  </tr>
               </tbody>
            </table>
         </form>
      </div>
   </div>
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();