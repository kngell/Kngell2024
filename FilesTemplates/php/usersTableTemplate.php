<div class="table-container">
   <table class="table">
      <thead>
         <tr>
            <!-- <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Actions</th> -->
            {{columns}}
         </tr>
      </thead>
      <tbody>
         <?php foreach ($users as $user): ?>
         <tr>
            <td><?= $user->getId(); ?></td>
            <td><?= $user->getName(); ?></td>
            <td><?= $user->getEmail(); ?></td>
            <td>
               <a href="/user/edit/<?= $user->getId(); ?>">Edit</a>
               <a href="/user/delete/<?= $user->getId(); ?>">Delete</a>
            </td>
         </tr>
         <?php endforeach; ?>
         {{rows}}
      </tbody>
   </table>
</div>

<div class="table-container">
   <section class="table-header"></section>
</div>