<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
    <!-- Content -->

    <div class="product-show">
        <div class="product-show__header">
            <div class="product-show__media">
                <img src="/uploads/products/123/main.jpg" alt="Product Image" class="product-show__image">
            </div>
            <div class="product-show__info">
                <h1 class="product-show__title"><?= $product->getName() ?? '' ?></h1>
                <p class="product-show__sku">SKU: <?= $product->getSku() ?? '' ?></p>
                <p class="product-show__status status--active">Active</p>
                <div class="product-show__actions">
                    <a href="/admin/products/edit/123" class="btn btn--secondary">Edit</a>
                    <form method="POST" action="/admin/products/delete/123" class="inline-form">
                        <button type="submit" class="btn btn--danger">Delete</button>
                    </form>
                    <a href="/admin/products" class="btn btn--light">Back to list</a>
                </div>
            </div>
        </div>

        <div class="product-show__details">
            <div class="card">
                <h3 class="card__title">General Information</h3>
                <p><strong>Short Description:</strong> Elegant handcrafted leather wallet.</p>
                <p><strong>Description:</strong> Full-grain leather, 4 card slots, 1 cash pocket.</p>
                <p><strong>Category:</strong> Accessories / Wallets</p>
            </div>

            <div class="card">
                <h3 class="card__title">Pricing</h3>
                <ul>
                    <li><strong>Base Price:</strong> €49.00</li>
                    <li><strong>Compare Price:</strong> €59.00</li>
                    <li><strong>Tax Class:</strong> Standard (20%)</li>
                </ul>
            </div>

            <div class="card">
                <h3 class="card__title">Inventory</h3>
                <ul>
                    <li><strong>Stock Quantity:</strong> 38</li>
                    <li><strong>Stock Status:</strong> In Stock</li>
                    <li><strong>Allow Backorders:</strong> No</li>
                    <li><strong>Track Stock:</strong> Yes</li>
                </ul>
            </div>

            <div class="card">
                <h3 class="card__title">Variations</h3>
                <table class="table table--variations">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Modifier</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Color</td>
                            <td>Brown</td>
                            <td>LW-001-BR</td>
                            <td>+0.00</td>
                            <td>20</td>
                            <td>Active</td>
                        </tr>
                        <tr>
                            <td>Color</td>
                            <td>Black</td>
                            <td>LW-001-BL</td>
                            <td>+2.00</td>
                            <td>18</td>
                            <td>Active</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3 class="card__title">Shipping</h3>
                <ul>
                    <li><strong>Physical Product:</strong> Yes</li>
                    <li><strong>Weight:</strong> 0.3 kg</li>
                    <li><strong>Dimensions:</strong> 10 × 2 × 8 cm</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();