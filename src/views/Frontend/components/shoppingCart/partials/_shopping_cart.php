<section id="cart" class="py-3">
   <div class="container w-75">
      <div class="title-wrapper">
         <h4 class="title fw-bold">Panier d'achats</h4>
         <hr class="horizontal-line">
      </div>
      <!-- Shopping cart items -->
      <div class="row">
         {{shopping_cart_items}}
         <!-- Sub-Total section -->
         {{shopping_cart_subTotal}}
         <!-- !Sub-Total section -->
      </div>
      <!-- !Shopping cart items -->
   </div>

</section>