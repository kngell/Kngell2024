<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/freecodecamp/lesson1') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<!-- Content -->
<main>
   <h1>CatPhotoApp</h1>
   <section>
      <h2>Cat Photos</h2>
      <p>Everyone loves <a href="https://cdn.freecodecamp.org/curriculum/cat-photo-app/running-cats.jpg"
            target="_blank">cute</a> cats
         online!</p>
      <p>See more <a href="https://freecatphotoapp.com" target="_blank">cat photos</a> in our gallery.</p>
      <a href="https://freecatphotoapp.com"><img
            src="https://cdn.freecodecamp.org/curriculum/cat-photo-app/relaxing-cat.jpg"
            alt="A cute orange cat lying on its back."></a>

   </section>
   <section>
      <h2>Cat Lists</h2>
      <h3>Things cats love:</h3>
      <ul>
         <li>cat nip</li>
         <li>laser pointers</li>
         <li>lasagna</li>
      </ul>
      <figure>
         <img src="https://cdn.freecodecamp.org/curriculum/cat-photo-app/lasagna.jpg"
            alt="A slice of lasagna on a plate.">
      </figure>
      <h3>Top 3 things cats hate:</h3>
      <ol>
         <li>flea treatment</li>
         <li>thunder</li>
         <li>other cats</li>
         <figure>
            <img src="https://cdn.freecodecamp.org/curriculum/cat-photo-app/cats.jpg"
               alt="Five cats looking around a field.">
         </figure>
      </ol>
   </section>
   <section>
      <h2>Cat Form</h2>
      <form action="https://freecatphotoapp.com/submit-cat-photo">
         <fieldset>
            <legend>Is your cat an indoor or outdoor cat?</legend>
            <label><input type="radio" name="indoor-outdoor" value="indoor">Indoor</label>
            <label><input type="radio" name="indoor-outdoor" value="outdoor">Outdoor</label>
         </fieldset>
         <fieldset>
            <legend>What's your cat's personality?</legend>
            <input type="checkbox" name="personality" value="Loving" id="loving">
            <label for="loving">Loving</label>
            <input type="checkbox" name="personality" value="Lazy" id="lazy">
            <label for="lazy">Lazy</label>
            <input id="energetic" type="checkbox" name="personality" value="energetic"> <label
               for="energetic">Energetic</label>
         </fieldset>
         <input type="text" name="catphotourl" placeholder="cat photo URL" required>
         <button type="submit">Submit</button>
      </form>
   </section>
</main>
<footer>
   <p>No Copyright - <a href="https://www.freecodecamp.org">freeCodeCamp.org</a></p>
</footer>



<!-- Fin Content -->


<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();