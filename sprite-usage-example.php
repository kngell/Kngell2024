<?php
// Before (current approach)
?>
<img src="<?= $this->asset('img/icons/phone.svg') ?>" alt="Phones" class="icon">

<?php
// After (sprite approach)
?>
<svg class="icon">
   <use href="<?= $this->asset('img/icons-sprite.svg#icon-phone') ?>"></use>
</svg>

<?php
// Or if sprite is inlined in HTML head:
?>
<svg class="icon">
   <use href="#icon-phone"></use>
</svg>

<?php
// Complete category card example:
?>
<div class="category-body__card">
   <div class="category-body__card--icon-wrapper">
      <svg class="icon">
         <use href="<?= $this->asset('img/icons-sprite.svg#icon-phone') ?>"></use>
      </svg>
   </div>
   <div class="category-body__card--icon-label">Phones</div>
</div>