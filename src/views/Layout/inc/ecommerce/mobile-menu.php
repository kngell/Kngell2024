<div class="mobile-menu">
   <button class="mobile-menu__close">
      <img src="<?= $this->asset('img/icons/close.svg') ?>" alt="Close menu" class="mobile-menu__close-icon">
   </button>
   <nav class="mobile-menu__nav">
      <ul class="mobile-menu__list">
         <li class="mobile-menu__item">
            <a href="#" class="mobile-menu__link">Home</a>
         </li>
         <li class="mobile-menu__item">
            <a href="#" class="mobile-menu__link">About</a>
         </li>
         <li class="mobile-menu__item">
            <a href="#" class="mobile-menu__link">Contact</a>
         </li>
         <li class="mobile-menu__item">
            <a href="#" class="mobile-menu__link">Blog</a>
         </li>
      </ul>
   </nav>
   <div class="mobile-menu__categories">
      <h3 class="mobile-menu__heading">Categories</h3>
      <ul class="mobile-menu__list">
         <?php foreach ($categories as $category): ?>
         <li class="mobile-menu__item">
            <a href="<?= $category['url'] ?>" class="mobile-menu__link">
               <img src="<?= $this->asset('img/icons/' . $category['icon']) ?>" alt="<?= $category['label'] ?>"
                  class="mobile-menu__icon">
               <?= $category['label'] ?>
            </a>
         </li>
         <?php endforeach; ?>
      </ul>
   </div>
</div>