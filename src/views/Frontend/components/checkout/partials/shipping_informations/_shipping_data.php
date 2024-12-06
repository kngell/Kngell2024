<div class="card">
   <div class="card-body">
      <div class="border p-3 mb-3 rounded info-resume">
         <table class="table table-borderless">
            <tr class="border-bottom contact">
               <td class="title">Contact:</td>
               <td class="value contact-email">{{contact_email}}</td>
               <td class="link"><a href="#" class="text-highlight" data-bs-toggle="modal"
                     data-bs-target="#modal-box-email">Change</a></td>
            </tr>
            <tr class="border-bottom address">
               <td class="title">Ship to:</td>
               <td class="value ship-to-address">{{address_de_livraion}}</td>
               <td class="link"><a href="#" class="text-highlight change-ship__btn" data-bs-toggle="modal"
                     data-bs-target="#modal-box-change-address">Change</a></td>
            </tr>
         </table>
      </div>
      {{title}}
      <div class="border mb-3 rounded radio-check-group">
         {{form_shipping_method}}
      </div>
   </div>
   <!-- end card-body -->
</div>