<div class="card order-summary__content">
   <div class="card-body">
      <h4 class="header-title mb-3">Resumé de la commande</h4>
      <div class="table-responsive order-resume">
         <table class="table table-borderless mb-0 align-middle">
            <tbody>
               {{cartSummaryContent}}
            </tbody>
         </table>
      </div>
      {{discountCode}}
      <div class="table-responsive total">
         {{CartSummaryTotal}}
      </div>
   </div>
   <!-- end card-body-->
</div>
{{button}}