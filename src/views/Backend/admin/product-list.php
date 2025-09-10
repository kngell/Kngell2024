<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main product-list" id="main">
    <!-- Content -->

    <div class="product-list__title">
        <div class="title">
            <h4 class="title__text">Product</h4>
            <nav class="title__breadcrumbs">
                <ul class="breadcrumbs-list">
                    <li class="breadcrumbs-list__item">
                        <a href="#" class="breadcrumbs-list__item--link">Dashboard</a>
                    </li>
                    <li class="breadcrumbs-list__item">
                        <a href="#" class="breadcrumbs-list__item--link active">Product List</a>
                    </li>
                </ul>
            </nav>

        </div>
        <div class="user-action">
            <button class="btn btn--secondary btn--md-compact btn--icon-left">
                <span class="btn__icon">
                    <svg class="icon export" aria-label="Export" role="img">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-export"></use>
                    </svg>
                </span>
                <span class="btn__label">Export</span>
            </button>
            <button class="btn btn--primary btn--md-compact btn--icon-left">
                <span class="btn__icon">
                    <svg class="icon plus" aria-label="Plus" role="img">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-plus"></use>
                    </svg>
                </span>
                <span class="btn__label">Add Product</span>
            </button>
        </div>
    </div>
    <div class="product-list__search-and-filter">
        <form class="search-form">
            <button type="submit" class="search-form__btn">
                <svg class="icon search" aria-label="Search" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search">
                    </use>
                </svg>
            </button>
            <input type="text" name="search" id="search-form--input-id" class="search-form__input"
                placeholder="Search product. . .">
        </form>
        <div class="right">
            <button class="right__date-picker">
                <span class="icon-container">
                    <svg class="icon calendar" aria-label="Calendar" role="img">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-calendar">
                        </use>
                    </svg>
                </span>
                <span class="icon-text">Select Dates</span>
            </button>
            <button class="right__filter">
                <span class="icon-container">
                    <svg class="icon slider" aria-label="Slider" role="img">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-slider">
                        </use>
                    </svg>
                </span>
                <span class="icon-text">Filters</span>
            </button>
        </div>
    </div>
    <div class="product-list__table-wrapper">
        <table class="table" summary="Product list with stock, price and status information"
            aria-describedby="table-desc">
            <caption class="visually-hidden" id="table-desc">
                This table lists products with their SKU, category, stock, price, status, date added and actions.
                Each product row starts with a checkbox followed by an image and product name.
            </caption>

            <colgroup>
                <col class="table__col table__col--product">
                <col class="table__col table__col--sku">
                <col class="table__col table__col--category">
                <col class="table__col table__col--stock">
                <col class="table__col table__col--price">
                <col class="table__col table__col--status">
                <col class="table__col table__col--added">
                <col class="table__col table__col--action">
            </colgroup>

            <thead class="table__head">
                <tr class="table__head--row">
                    <th scope="col" class="table__head--row-cell">
                        <div class="header-cell-product">
                            <span id="select-all-label" class="visually-hidden">Select all products</span>
                            <input type="checkbox" id="select-all" aria-labelledby="select-all-label">
                            <label for="select-all" class="header-cell-product__label">
                                products</label>
                            <span class="icon-container">
                                <svg class="icon arrow-down" aria-label="Arrow Down" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down"></use>
                                </svg>
                            </span>
                        </div>
                    </th>
                    <th scope="col" class="table__head--row-cell">
                        <span class="header-cell">SKU</span>
                    </th>
                    <th scope="col" class="table__head--row-cell">
                        <span class="header-cell">Category</span>
                    </th>
                    <th scope="col" class="table__head--row-cell">
                        <div class="header-cell">
                            <span>Stock</span>
                            <span class="icon-container">
                                <svg class="icon arrow-down" aria-label="Arrow Down" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down"></use>
                                </svg>
                            </span>
                        </div>

                    </th>
                    <th scope="col" class="table__head--row-cell">
                        <div class="header-cell">
                            <span>Price</span>
                            <span class="icon-container">
                                <svg class="icon arrow-down" aria-label="Arrow Down" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down"></use>
                                </svg>
                            </span>
                        </div>

                    </th>
                    <th scope="col" class="table__head--row-cell">
                        <div class="header-cell">
                            <span>Status</span>
                            <span class="icon-container">
                                <svg class="icon arrow-down" aria-label="Arrow Down" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down"></use>
                                </svg>
                            </span>
                        </div>

                    </th>
                    <th scope="col" class="table__head--row-cell">
                        <div class="header-cell">
                            <span>Added</span>
                            <span class="icon-container">
                                <svg class="icon arrow-down" aria-label="Arrow Down" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down"></use>
                                </svg>
                            </span>
                        </div>

                    </th>
                    <th scope="col" class="table__head--row-cell" aria-label="Actions">
                        <span class="header-cell">Action</span>
                    </th>
                </tr>
            </thead>

            <tbody class="table__body" aria-describedby="table-desc">
                <tr class="table__body--row">
                    <th scope="row" class="table__body--row-cell">
                        <div class="body-cell-product">
                            <input type="checkbox" id="product-1" name="products[]" value="1">
                            <label for="product-1" class="body-cell-product__label">
                                <div class="img-container">
                                    <img src="../../../assets/img/ecommerce/ipad9.png" alt="" class="image">
                                </div>

                                <ul class="text-container">
                                    <li class="text-container__name">Product A</li>
                                    <li class="text-container__variant">2 Variants</li>
                                </ul>

                            </label>
                        </div>
                    </th>
                    <td class="table__body--row-cell">
                        <div class="body-cell sku">
                            <span>SKU-001</span>
                        </div>
                    </td>
                    <td class="table__body--row-cell">
                        <div class="body-cell">
                            <span>Category A</span>
                        </div>
                    </td>
                    <td class="table__body--row-cell">
                        <div class="body-cell"><span>120</span></div>
                    </td>
                    <td class="table__body--row-cell">
                        <div class="body-cell"><span>$25.00</span></div>
                    </td>
                    <td class="table__body--row-cell">
                        <div class="body-cell">
                            <span>
                                <span class="badge badge--warning">low stock</span>
                            </span>
                        </div>
                    </td>
                    <td class="table__body--row-cell">
                        <div class="body-cell"><span>2025-08-15</span></div>
                    </td>
                    <td class="table__body--row-cell">
                        <form action="" method="post" class="body-cell-action">
                            <button class="icon-container">
                                <svg class="icon eye" aria-label="Eye" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-eye"></use>
                                </svg>
                                <span class="visually-hidden">View Product A</span>
                            </button>
                            <button class="icon-container">
                                <svg class="icon edit" aria-label="Edit" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-edit"></use>
                                </svg>
                                <span class="visually-hidden">Edit Product A</span>
                            </button>
                            <button class="icon-container">
                                <svg class="icon trash" aria-label="Trash" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-trash"></use>
                                </svg>
                                <span class="visually-hidden">Delete Product A</span>
                            </button>
                        </form>
                    </td>
                </tr>

                <tr class="table__body--row">
                    <th scope="row" class="table__body--row-cell">
                        <div class="body-cell-product">
                            <input type="checkbox" id="product-2" name="products[]" value="2">
                            <label for="product-2" class="body-cell-product__label">
                                <div class="img-container">
                                    <img src="../../../assets/img/ecommerce/camera.png" alt="" class="image">
                                </div>

                                <ul class="text-container">
                                    <li class="text-container__name">Product B</li>
                                    <li class="text-container__variant">1 Variant</li>
                                </ul>
                            </label>
                        </div>
                    </th>
                    <td class="table__body--row-cell">
                        <div class="body-cell sku">
                            <span>SKU-002</span>
                        </div>
                    </td>
                    <td class="table__body--row-cell">
                        <div class="body-cell">
                            <span>Category B</span>
                        </div>
                    </td>
                    <td class="table__body--row-cell">
                        <div class="body-cell"><span>95</span></div>
                    </td>
                    <td class="table__body--row-cell">
                        <div class="body-cell"><span>$30.00</span></div>
                    </td>
                    <td class="table__body--row-cell">
                        <div class="body-cell"> <span>
                                <span class="badge badge--warning">low stock</span>
                            </span></div>
                    </td>
                    <td class="table__body--row-cell">
                        <div class="body-cell"><span>2025-08-20</span></div>
                    </td>
                    <td class="table__body--row-cell">
                        <form action="" method="post" class="body-cell-action">
                            <button class="icon-container">
                                <svg class="icon eye" aria-label="Eye" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-eye"></use>
                                </svg>
                                <span class="visually-hidden">View Product A</span>
                            </button>
                            <button class="icon-container">
                                <svg class="icon edit" aria-label="Edit" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-edit"></use>
                                </svg>
                                <span class="visually-hidden">Edit Product A</span>
                            </button>
                            <button class="icon-container">
                                <svg class="icon trash" aria-label="Trash" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-trash"></use>
                                </svg>
                                <span class="visually-hidden">Delete Product A</span>
                            </button>
                        </form>
                    </td>
                </tr>

            </tbody>
        </table>


        <div class="pagination">
            <div class="pagination__info">
                Showing <span class="pagination__current">1-10</span> of <span class="pagination__total">50</span>
                products
            </div>

            <nav class="pagination__nav" aria-label="Product pagination">
                <button class="pagination__btn pagination__btn--prev" aria-label="Previous page" disabled>
                    <svg class="icon arrow-left" aria-hidden="true">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-left"></use>
                    </svg>
                </button>

                <div class="pagination__pages">
                    <button class="pagination__page pagination__page--active" aria-label="Page 1"
                        aria-current="page">1</button>
                    <button class="pagination__page" aria-label="Page 2">2</button>
                    <button class="pagination__page" aria-label="Page 3">3</button>
                    <span class="pagination__ellipsis">...</span>
                    <button class="pagination__page" aria-label="Page 5">5</button>
                </div>

                <button class="pagination__btn pagination__btn--next" aria-label="Next page">
                    <svg class="icon arrow-right" aria-hidden="true">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-right"></use>
                    </svg>
                </button>
            </nav>

            <div class="pagination__per-page">
                <label for="per-page" class="pagination__per-page-label">Items per page:</label>
                <select id="per-page" class="pagination__select">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
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