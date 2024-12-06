<section id="wishlist" class="py-3" style="<?=$this->display ?? 'display:none;'?>">
    <div class="container-fluid w-75">
        <div class="title-wrapper">
            <h4 class="title fw-bold">Whishlist</h4>
            <hr class="horizontal-line">
        </div>
        <!-- Shopping cart items -->
        <div class="row">
            <div class="col-sm-9" id="wishlist-items">
                <!-- Wishlist item -->
                <?php if ($this->user_cart && $this->user_cart[0]) :?>
                <?php $products = array_filter($this->user_cart[0], function ($item) {
                    return $item->c_content == 'wishlist';
                })?>
                <?php if (count($products) > 0) :?>
                <?php foreach ($products as $product):?>
                <div class="row cart-row">
                    <div class="col-sm-2 cart-row__img">
                        <img src="<?=str_starts_with($product->p_media[0], IMG) ? unserialize($product->p_media) : IMG . unserialize($product->p_media)[0]?>"
                            alt="cart1" class="img-fluid" style="height:120px;">
                    </div>
                    <div class="col-sm-8 cart-row__details">
                        <h5 class="title"><?=$product->htmlDecode($product->p_title)?>
                        </h5>
                        <small>By <?=$product->categorie?></small>
                        <!-- Rating section -->
                        <div class="rating">
                            <div class="rating__star text-warning font-size-12">
                                <span><i class="fas fa fa-star"></i></span>
                                <span><i class="fas fa fa-star"></i></span>
                                <span><i class="fas fa fa-star"></i></span>
                                <span><i class="fas fa fa-star"></i></span>
                                <span><i class="fa-solid fa-star-half-stroke"></i></span>
                            </div>
                            <a href="#" class="px-2 rating__text">20,534 ratings</a>
                        </div>
                        <!-- !Rating section -->
                        <!-- Produt quantity -->
                        <div class="cart-qty">
                            <form action="#" class="delete-cart-item-frm<?=$product->pdtID?>">
                                <input type="hidden" name="itemId" value="<?=$product->pdtID?>">
                                <input type="hidden" name="userId" value="1">
                                <?=FH::csrfInput('csrftoken', $this->token->generate_token(8, 'delete-cart-item-frm' . $product->pdtID ?? 1)); ?>
                                <button type="submit"
                                    class="btn font-baloo pl-0 pr-3 border-right deleteBtn">Supprimer</button>
                                <button type="button" class="button save-add">Add to cart</button>
                            </form>
                        </div>
                        <!-- !Produt quantity -->
                    </div>
                    <div class="col-sm-2 text-right cart-row__price">
                        <div class="price_wrapper">
                            <span
                                class="product_price"><?=$product->get_money()->getAmount($product->p_regularPrice * $product->itemQty)?></span>
                        </div>
                    </div>
                </div>
                <?php  endforeach; ?>
                <?php endif; ?>
                <?php endif; ?>
                <!-- !Wishlist item -->
            </div>
        </div>



        <!-- !Shopping cart items -->
    </div>

</section>