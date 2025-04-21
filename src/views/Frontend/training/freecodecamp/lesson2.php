<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/freecodecamp/lesson2') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<!-- Content -->
<div class="menu">
   <main>
      <div class="general-title">
         <h1>CAMPER CAFE</h1>
         <p>Est. 2020</p>
      </div>
      <hr>
      <section class="coffee">
         <div class="sub-title">
            <h2>Coffee</h2>
            <img src="https://cdn.freecodecamp.org/curriculum/css-cafe/coffee.jpg" alt="coffee icon">
         </div>
         <div class="articles">
            <article class="article">
               <p>French Vanilla</p>
               <p>3.00</p>
            </article>
            <article class="article">
               <p>Caramel Macchiato</p>
               <p>3.75</p>
            </article>
            <article class="article">
               <p>Pumpkin Spice</p>
               <p>3.50</p>
            </article>
            <article class="article">
               <p>Hazelnut</p>
               <p>4.00</p>
            </article>
            <article class="article">
               <p>Mocha</p>
               <p>4.50</p>
            </article>
         </div>
      </section>
      <section class="dessert">
         <div class="sub-title">
            <h2>Desserts</h2>
            <img src="https://cdn.freecodecamp.org/curriculum/css-cafe/pie.jpg" alt="pie icon">
         </div>
         <div class="articles">
            <article class="article">
               <p>Donut</p>
               <p>1.50</p>
            </article>
            <article class="article">
               <p>Cherry Pie</p>
               <p>2.75</p>
            </article>
            <article class="article">
               <p>Cheesecake</p>
               <p>3.00</p>
            </article>
            <article class="article">
               <p>Cinnamon Roll</p>
               <p>2.50</p>
            </article>
         </div>
      </section>
      <hr>
   </main>
   <footer>
      <div class="end-site">
         <p><a href="https://www.freecodecamp.org">Visit our website</a></p>
         <p>123 Free Code Camp Drive</p>
      </div>

   </footer>
</div>

<footer>

</footer>



<!-- Fin Content -->


<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();