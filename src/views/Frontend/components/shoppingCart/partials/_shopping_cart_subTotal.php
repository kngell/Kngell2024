<div class="col-sm-3 sub_total" id="sub_total">
   <div class="card sub-total mt-2 border">
      <div class="card-header">
         <p class="nb-item"><span class="cart_nb_elt">{{nb_items}}</span>&nbsp;<span>produits</span>
         </p>
         <p class="title"><i class="fas fa-check"></i> &nbsp; Your ordre
            is
            eligible for FREE Delivery</p>

      </div>
      <div class="card-body">
         <div class="total-ht">
            <span class="title">Total HT:</span>
            <span class="deal-price" id="deal-price">{{totalHT}}</span>
            </span>
         </div>
         <p class="transition">Taxes : </p>
         <ul class="cart-resume">
            {{taxResumeHtml}}
         </ul>
         <div class="total-ttc">
            <span class="title">Total TTC:</span>
            <span class="total-price" id="total-price">{{totalTTC}}</span>
            </span>
         </div>
      </div>
      <div class="card-footer proceed">
         {{proceedTobuyform}}
      </div>
   </div>
</div>