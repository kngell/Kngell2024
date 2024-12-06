<div class="card">
   <div class="card-body">
      <div class="border p-3 mb-3 rounded info-resume">
         <table class="table table-borderless">
            <tr class="border-bottom contact">
               <td>Contact:</td>
               <td class="value contact-email">{{contact_email}}</td>
               <td><a href="#" class="text-highlight" data-bs-toggle="modal"
                     data-bs-target="#modal-box-email">Change</a></td>
            </tr>
            <tr class="border-bottom address">
               <td class="title">Ship to:</td>
               <td class="value ship-to-address">{{address_de_livraion}}</td>
               <td><a href="#" class="text-highlight change-ship__btn" data-bs-toggle="modal"
                     data-bs-target="#modal-box-change-address">Change</a></td>
            </tr>
            <tr class="border-bottom method">
               <td class="title">Shipping Method</td>
               <td class="shipping_method"> <span class="method_title">{{shipping_mode}}
                  </span> &nbsp;<span class="price">{{shipping_price}}</span> </td>
               <td><a href="#" class="text-highlight" data-bs-toggle="modal"
                     data-bs-target="#modal-box-shipping">Change</a></td>
            </tr>
            <tr class="border-bottom bill-address-ckeck" style="display: none;">
               <td class="title">bill to:</td>
               <td class="value bill-to-address">{{addresse_de_facturation}}</td>
               <td><a href="#" class="text-highlight change-bill__btn" data-bs-toggle="modal"
                     data-bs-target="#modal-box-change-address">Change</a></td>
            </tr>
         </table>
      </div>
      {{title}}
      <p class="infos-transaction">Select the address that matches your card or
         payment
         method.</p>
      {{billingFrom}}
   </div>
   <!-- end card-body -->
</div>