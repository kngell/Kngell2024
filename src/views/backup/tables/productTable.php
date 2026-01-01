<table class="table" summary="Product list with stock, price and status information" aria-describedby="table-desc">
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
                <div class="header-cell">
                    <div class="header-cell__top-row">
                        <!-- Checkbox for select all -->
                        <div class="checkbox-box">
                            <span id="select-all-label" class="visually-hidden">Select all products</span>
                            <input type="checkbox" id="select-all" class="checkbox-box__input"
                                aria-labelledby="select-all-label" />
                            <label for="select-all" class="checkbox-box__label">products</label>
                        </div>
                        <!-- Separate dropdown for advanced selection -->
                        <div class="dropdown-container">
                            <button class="dropdown-container__btn" aria-expanded="false"
                                aria-controls="advanced-selection">
                                <svg class="icon arrow-down" aria-hidden="true">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                    </use>
                                </svg>
                            </button>

                            <div id="advanced-selection" class="dropdown-container__dropdown" hidden>
                                <!-- Advanced selection options -->
                            </div>
                        </div>
                    </div>
                    <span class="header-cell__hint-text"></span>
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
                    <div class="header-cell__top-row">
                        <span>Stock</span>
                        <div class="dropdown-container">
                            <button class="dropdown-container__btn" aria-expanded="false"
                                aria-controls="advanced-selection">
                                <svg class="icon arrow-down" aria-hidden="true">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                    </use>
                                </svg>
                            </button>

                            <div id="advanced-selection" class="dropdown-container__dropdown" hidden>
                                <!-- Advanced selection options -->
                            </div>
                        </div>
                    </div>
                    <span class="header-cell__hint-text"></span>
                </div>

            </th>
            <th scope="col" class="table__head--row-cell">
                <div class="header-cell">
                    <div class="header-cell__top-row">
                        <!-- Header label -->
                        <span>Price</span>
                        <!-- Separate dropdown for advanced selection -->
                        <div class="dropdown-container">
                            <button class="dropdown-container__btn" aria-expanded="false"
                                aria-controls="advanced-selection">
                                <svg class="icon arrow-down" aria-hidden="true">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                    </use>
                                </svg>
                            </button>

                            <div id="advanced-selection" class="dropdown-container__dropdown" hidden>
                                <!-- Advanced selection options -->
                            </div>
                        </div>
                    </div>
                    <span class="header-cell__hint-text"></span>
                </div>
            </th>
            <th scope="col" class="table__head--row-cell">
                <div class="header-cell">
                    <div class="header-cell__top-row">
                        <!-- Header label -->
                        <span>Status</span>
                        <!-- Separate dropdown for advanced selection -->
                        <div class="dropdown-container">
                            <button class="dropdown-container__btn" aria-expanded="false"
                                aria-controls="advanced-selection">
                                <svg class="icon arrow-down" aria-hidden="true">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                    </use>
                                </svg>
                            </button>

                            <div id="advanced-selection" class="dropdown-container__dropdown" hidden>
                                <!-- Advanced selection options -->
                            </div>
                        </div>
                    </div>
                    <span class="header-cell__hint-text"></span>
                </div>
            </th>
            <th scope="col" class="table__head--row-cell">

                <div class="header-cell">
                    <div class="header-cell__top-row">
                        <!-- Header label -->
                        <span>Added</span>
                        <!-- Separate dropdown for advanced selection -->
                        <div class="dropdown-container">
                            <button class="dropdown-container__btn" aria-expanded="false"
                                aria-controls="advanced-selection">
                                <svg class="icon arrow-down" aria-hidden="true">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                    </use>
                                </svg>
                            </button>

                            <div id="advanced-selection" class="dropdown-container__dropdown" hidden>
                                <!-- Advanced selection options -->
                            </div>
                        </div>
                    </div>
                    <span class="header-cell__hint-text"></span>
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
                <div class="body-cell-product body-cell-product--checkbox">
                    <input type="checkbox" id="product-1" name="products[]" value="1">
                    <label for="product-1" class="body-cell-product__label">
                        <span class="img-container">
                            <img src="../../../assets/img/ecommerce/ipad9.png" alt="" class="image">
                        </span>

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
                <div class="body-cell-product body-cell-product--checkbox">
                    <input type="checkbox" id="product-2" name="products[]" value="2">
                    <label for="product-2" class="body-cell-product__label">
                        <span class="img-container">
                            <img src="../../../assets/img/ecommerce/camera.png" alt="" class="image">
                        </span>

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