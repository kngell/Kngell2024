<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/pratique/dairy') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<div class="dairy-layout">
   <header class="header">
      <div class="dairy-container">
         <nav class="nav">
            <div class="nav-brand">
               <a href="/pratique/dairy" class="nav-brand__logo">
                  <svg viewBox="0 0 60.7863869853 60.7863869853" class="nav-brand__logo--img">
                     <path style="fill: currentColor;" class="uuid-f1de6254-0820-4376-87ff-f875521b7fef"
                        d="m45.589790239,30.3931934927c8.3928407749,0,15.1965967463-6.8037559715,15.1965967463-15.1965967463S53.9826310139,0,45.589790239,0H15.196554313C6.8037135382,0,0,6.8037559715,0,15.1965967463v30.3931934927c0,8.3928407749,6.8037135382,15.1965967463,15.196554313,15.1965967463h30.393235926c8.3928407749,0,15.1965967463-6.8037559715,15.1965967463-15.1965967463s-6.8037559715-15.1965967463-15.1965967463-15.1965967463Z" />

                  </svg>
                  <h4 class="nav-brand__logo--text">My Journal</h4>
               </a>
            </div>
            <form action="#" class="nav-form">
               <button class="btn btn-submit">
                  <svg viewBox="0 0 44.4901230052 44.4901230053" class="btn-submit--img">
                     <g style=" fill: none;
                        stroke:currentColor;
                        stroke-linecap: round;
                        stroke-linejoin: round;
                        stroke-width: 2px;">
                        <circle cx="22.2450615026" cy="22.2450615026" r="21.2450615026" />
                        <line x1="22.2450615026" y1="13.4699274037" x2="22.2450615026" y2="31.0201956015" />
                        <line x1="31.0201956015" y1="22.2450658041" x2="13.4699274037" y2="22.2450572011" />
                     </g>
                  </svg>
                  <p class="btn-submit--text">New entry</p>
               </button>
            </form>

         </nav>

      </div>

   </header>
   <main id="main-site" class="main">
      <div class="dairy-container">
         <div class="headings">
            <h1 class="headings__h1">Entries</h1>
            <button class="btn btn-heading">week1</button>
         </div>

         <div class="card">
            <div class="card-header">
               <div class="card-header__img-container">
                  <img src="../../../../assets/img/pexels-canva-studio-3153199.jpg" alt=""
                     class="card-header__img-container--img">
               </div>
            </div>
            <div class="card-body">
               <h4>week</h4>
               <h2 class="card-title">lorem title1</h2>
               <p class="card-body__img-container--text">Lorem ipsum, dolor sit amet consectetur adipisicing elit.
                  Ducimus, iure!
                  Minima ea quia fugiat sed, quisquam amet sunt! Assumenda aperiam adipisci deserunt corrupti, repellat
                  quam eius. Provident ad atque iusto?
                  Eligendi, repudiandae quia culpa at sed dolor vitae quisquam et. Libero at dicta vitae ab, quas quam

               </p>
            </div>
         </div>
      </div>
   </main>
   <footer class="footer">
      <div class="div">
         Footer
      </div>
   </footer>
</div>

<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/frontend/training/dairy/main') ?>

<?php $this->end();