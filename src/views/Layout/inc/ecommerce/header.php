<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <?= $this->getPageTitle()?>
   <!-- Main style -->
   <?= $this->css('css/librairies/frontlib') ?? '' ?>
   <!-- Plugins css -->
   <?= $this->css('css/plugins/homeplugins') ?? '' ?>
   <!-- Main style -->
   <?= $this->css('css/main/main') ?? '' ?>
</head>

<body>
   <header>
      <div class="container">
         <nav class="navbar navbar-expand-lg bg-body-tertiary bg-light py-3 fixed-top">
            <div class="container-fluid">

               <div class="col">
                  <a class="navbar-brand" href="#"><img src="#" alt="Logo"></a>
                  <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                     data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                     aria-expanded="false" aria-label="Toggle navigation">
                     <span class="navbar-toggler-icon"></span>
                  </button>
               </div>
               <div class="col">
                  <div class="collapse navbar-collapse" id="navbarSupportedContent">
                     <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                           <a class="nav-link" href="#">Home</a>
                        </li>
                        <li class="nav-item">
                           <a class="nav-link" href="#">Shop</a>
                        </li>
                        <li class="nav-item">
                           <a class="nav-link" href="#">Blog</a>
                        </li>
                        <li class="nav-item">
                           <a class="nav-link" href="#">Contact Us</a>
                        </li>
                        <li class="nav-item">
                           <i class="fa-solid fa-bag-shopping"></i>
                           <i class="fa-solid fa-user-large"></i>
                        </li>
                     </ul>

                  </div>
               </div>



            </div>
         </nav>
      </div>
   </header>