<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <h1>Internal server Error</h1>
   <section>
      <div class="container">
         <article class="p-4">
            <?php
            if(isset($exception)) {
                $errfile = $exception->getFile();
                $errline = $exception->getLine();
                $errcode = $code;
                $errstr = $exception->getMessage();
                $errTrace = $exception->getTrace();
                $errDebugBacktrace = debug_backtrace();
                $errType = get_class($exception);
            }
?>
            <h1 class="uk-article-title"><span class="text-danger"><i class="bi bi-exclamation-circle-fill"></i></span>
               Application Error
            </h1>
            <p class="ion-21">The application encountered the following error below.</p>

            <h2 class="uk-heading-line"><span>Error Details</span></h2>
            <ul class="uk-list uk-list-collapse">
               <li><strong>Uncaught Exception:</strong> <span
                     class="uk-text-danger uk-text-bolder"><?= $errType ?? null; ?></span></li>
               <li><strong>Code:</strong> <span class="uk-text-danger uk-text-bolder"><?= $errcode ?? null; ?></span>
               </li>
               <li><strong>File:</strong> <span class="uk-text-danger uk-text-bolder"><?= $errfile ?? null; ?></span>
               </li>
               <?php if (isset($snippet)) : ?>
               <li><strong>Error:</strong> <span class="uk-text-danger uk-text-bolder"><?= $snippet ?? null; ?></span>
               </li>
               <?php endif; ?>
               <?php if (isset($srcCode)) : ?>
               <li><strong>Source:</strong> <span class="uk-text-danger uk-text-bolder"><?= $srcCode ?? null; ?></span>
               </li>
               <?php endif; ?>
               <li><strong>Line:</strong> <span class="uk-text-danger uk-text-bolder"><?= $errline ?? null; ?></span>
               </li>
               <li><strong>Message:</strong> <span
                     class="uk-text-danger uk-text-bolder"><?= htmlentities($errstr ?? '') ?? null; ?></span></li>

            </ul>
            <ul class="uk-subnav uk-subnav-pill" uk-switcher>
               <li><a class="uk-text-capitalize uk-text-bolder" href="#">StackTrace
                     <span>(<?php if(isset($errTrace)) {
                         if(count($errTrace) > 0) {
                             echo count($errTrace);
                         } else {
                             echo 0;
                         }
                     } ?>)</span></a></li>
               <li><a class="uk-text-capitalize uk-text-bolder" href="#"><?= $errType ?? null; ?></a></li>
               <li><a class="uk-text-capitalize uk-text-bolder" href="#">Debug Backtrace
                     <span>(<?php
                     if(isset($errDebugBacktrace)) {
                         if(count($errDebugBacktrace) > 0) {
                             echo count($errDebugBacktrace);
                         } else {
                             echo 0;
                         }
                     }
?>)</span></a>
               </li>
            </ul>

            <ul class="uk-switcher uk-margin">
               <li>
                  <pre
                     class="uk-text-bolder uk-dark uk-background-muted"><?=isset($exception) ? "\n" . $exception->getTraceAsString() : ''; ?></pre>
                  <a class="uk-text-bolder" href="#" onClick="window.history.go(-1)">Go Back</a>
               </li>
               <li>
                  <pre class="uk-dark uk-background-muted">
                       <?php
          $out = '';
if(isset($stacktrace)) {
    foreach ($stacktrace as $strace) {
        if ($strace['file'] == $errfile) {
            $out = $strace['code'];
        }
    }
    echo $out;
}

?>
                    </pre>

               </li>
               <li>
                  <pre class="uk-text-bolder uk-dark uk-background-muted">
                    <?= debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
?>
                    </pre>
               </li>
            </ul>
         </article>
      </div>
   </section>
   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();