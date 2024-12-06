<div class="row transaction-menu">
   <div class="col-sm-6 transaction-item">
      <div class="card h-100" id="orders">
         <div class="card-body">
            <h5 class="card-title"><span class="card-img-wrapper">
                  <img src="../../../../../assets/img/users/account/account_mescommandes.png" alt="Mes commandes"
                     class="img-fluid">
               </span>Mes
               commandes</h5>
            <p class="card-text">Suivre, Retourner ou Acheter à nouveau</p>
         </div>
         {{user_form_orders}}
      </div>
   </div>
   <div class="col-sm-6 transaction-item">
      <div class="card h-100">
         <div class="card-body">
            <h5 class="card-title"><span class="card-img-wrapper">
                  <img src="../../../../../assets/img/users/account/account_donnees-personnelles.png"
                     alt="Mes données personnelles" class="img-fluid">
               </span>Données
               personnels</h5>
            <p class="card-text">Modifier ou supprimer mes données personnels, changer mon
               mot
               de passe</p>
         </div>
         {{user_form_users}}
      </div>
   </div>
   <div class="col-sm-6 transaction-item">
      <div class="card h-100" id="address_book">
         <div class="card-body">
            <h5 class="card-title"><span class="card-img-wrapper">
                  <img src="../../../../../assets/img/users/account/account_adresses.png" alt="Mes adresses"
                     class="img-fluid">
               </span>Mes
               addresses</h5>
            <p class="card-text">Modifier ou ajouter les adresses de livraison et de
               facturation</p>
         </div>
         {{user_form_address_book}}
      </div>
   </div>
   <div class="col-sm-6 transaction-item">
      <div class="card h-100">
         <div class="card-body">
            <h5 class="card-title"><span class="card-img-wrapper">
                  <img src="../../../../../assets/img/users/account/account_mode-de-paiement.png"
                     alt="Mes modes de paiement" class="img-fluid">
               </span>Moyens de
               paiement</h5>
            <p class="card-text">Modifier ou supprimer vos moyens de
               paiement</p>
         </div>
         {{user_form_payments_mode}}
      </div>
   </div>
</div>