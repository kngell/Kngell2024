<div class="table-wrapper" role="region" aria-label="Product list" tabindex="0">
    <table class="table" aria-describedby="table-desc">
        <caption class="visually-hidden" id="table-desc">
            This table lists products with their SKU, category, stock, price, status,
            date added and actions.
        </caption>

        <!--
      4 Column Types (visual):
        --start   : First column — checkbox + image + text (always <th scope="row">)
        --normal  : Standard data — text with ellipsis
        --badge   : Status/tag display
        --action  : Icon button actions
    -->
        <colgroup>
            <col class="table__col table__col--start">
            <col class="table__col table__col--normal">
            <col class="table__col table__col--normal">
            <col class="table__col table__col--normal">
            <col class="table__col table__col--normal">
            <col class="table__col table__col--badge">
            <col class="table__col table__col--normal">
            <col class="table__col table__col--action">
        </colgroup>

        <!-- ================================================================ -->
        <!-- THEAD -->
        <!-- ================================================================ -->
        <thead class="table__head">
            <tr class="table__head--row">

                <!-- START header -->
                <th class="table__head--row-cell table__cell--start" scope="col">
                    <div class="header-cell">
                        <div class="header-cell__top-row">
                            <div class="checkbox-box">
                                <input type="checkbox" class="checkbox-box__input" id="select-all"
                                    aria-label="Select all products">
                                <label class="checkbox-box__label" for="select-all">
                                    Products
                                </label>
                            </div>
                            <div class="dropdown-container">
                                <button class="dropdown-container__btn" aria-expanded="false"
                                    aria-controls="dropdown-select" aria-label="Product column options" type="button">
                                    <svg class="icon arrow-down" aria-hidden="true">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-arrow-down"></use>
                                    </svg>
                                </button>
                                <div class="dropdown-container__dropdown" hidden id="dropdown-select"></div>
                            </div>
                        </div>
                        <span class="header-cell__hint-text"></span>
                    </div>
                </th>

                <!-- NORMAL headers -->
                <th class="table__head--row-cell table__cell--normal" scope="col">
                    <span class="header-cell">SKU</span>
                </th>

                <th class="table__head--row-cell table__cell--normal" scope="col">
                    <span class="header-cell">Category</span>
                </th>

                <th class="table__head--row-cell table__cell--normal" scope="col" aria-sort="none">
                    <div class="header-cell">
                        <div class="header-cell__top-row">
                            <span>Stock</span>
                            <div class="dropdown-container">
                                <button class="dropdown-container__btn" aria-expanded="false"
                                    aria-controls="dropdown-stock" aria-label="Sort or filter by stock" type="button">
                                    <svg class="icon arrow-down" aria-hidden="true">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-arrow-down"></use>
                                    </svg>
                                </button>
                                <div class="dropdown-container__dropdown" hidden id="dropdown-stock"></div>
                            </div>
                        </div>
                        <span class="header-cell__hint-text"></span>
                    </div>
                </th>

                <th class="table__head--row-cell table__cell--normal" scope="col" aria-sort="none">
                    <div class="header-cell">
                        <div class="header-cell__top-row">
                            <span>Price</span>
                            <div class="dropdown-container">
                                <button class="dropdown-container__btn" aria-expanded="false"
                                    aria-controls="dropdown-price" aria-label="Sort or filter by price" type="button">
                                    <svg class="icon arrow-down" aria-hidden="true">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-arrow-down"></use>
                                    </svg>
                                </button>
                                <div class="dropdown-container__dropdown" hidden id="dropdown-price"></div>
                            </div>
                        </div>
                        <span class="header-cell__hint-text"></span>
                    </div>
                </th>

                <!-- BADGE header -->
                <th class="table__head--row-cell table__cell--badge" scope="col" aria-sort="none">
                    <div class="header-cell">
                        <div class="header-cell__top-row">
                            <span>Status</span>
                            <div class="dropdown-container">
                                <button class="dropdown-container__btn" aria-expanded="false"
                                    aria-controls="dropdown-status" aria-label="Sort or filter by status" type="button">
                                    <svg class="icon arrow-down" aria-hidden="true">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-arrow-down"></use>
                                    </svg>
                                </button>
                                <div class="dropdown-container__dropdown" hidden id="dropdown-status"></div>
                            </div>
                        </div>
                        <span class="header-cell__hint-text"></span>
                    </div>
                </th>

                <!-- NORMAL header -->
                <th class="table__head--row-cell table__cell--normal" scope="col" aria-sort="none">
                    <div class="header-cell">
                        <div class="header-cell__top-row">
                            <span>Added</span>
                            <div class="dropdown-container">
                                <button class="dropdown-container__btn" aria-expanded="false"
                                    aria-controls="dropdown-added" aria-label="Sort or filter by date added"
                                    type="button">
                                    <svg class="icon arrow-down" aria-hidden="true">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-arrow-down"></use>
                                    </svg>
                                </button>
                                <div class="dropdown-container__dropdown" hidden id="dropdown-added"></div>
                            </div>
                        </div>
                        <span class="header-cell__hint-text"></span>
                    </div>
                </th>

                <!-- ACTION header -->
                <th class="table__head--row-cell table__cell--action" scope="col">
                    <span class="header-cell">Action</span>
                </th>
            </tr>
        </thead>

        <!-- ================================================================ -->
        <!-- TBODY -->
        <!-- ================================================================ -->
        <tbody class="table__body">

            <!-- Row 1: With product image, selected -->
            <tr class="table__body--row row--selected">

                <!--
          START cell:
          - Rendered by RowHeaderCellRenderer → <th scope="row">
          - Visual style: .table__cell--start
          - Contains: checkbox + image + text
        -->
                <th class="table__body--row-cell table__cell--start" scope="row">
                    <div class="body-cell-start body-cell-start--checkbox">
                        <input type="checkbox" id="select_row1" name="products[]"
                            value="b0615b1e-a645-4908-be3d-0ee17de4c972" checked>
                        <label class="body-cell-start__label" for="select_row1">
                            <span class="img-container">
                                <img class="image" src="/uploads/images/card2_69c997565785e.png"
                                    alt="Product with main image" loading="lazy" width="44" height="44">
                            </span>
                            <span class="text-container">
                                <span class="text-container__name">Product with main image</span>
                                <span class="text-container__variant">1 Variant</span>
                            </span>
                        </label>
                    </div>
                </th>

                <!-- NORMAL: SKU (with --sku modifier for accent color) -->
                <td class="table__body--row-cell table__cell--normal">
                    <div class="body-cell body-cell--sku">
                        <span>PriceTest41</span>
                    </div>
                </td>

                <!-- NORMAL: Category -->
                <td class="table__body--row-cell table__cell--normal">
                    <div class="body-cell">
                        <span>Kids' Clothing</span>
                    </div>
                </td>

                <!-- NORMAL: Stock -->
                <td class="table__body--row-cell table__cell--normal">
                    <div class="body-cell">
                        <span>0</span>
                    </div>
                </td>

                <!-- NORMAL: Price -->
                <td class="table__body--row-cell table__cell--normal">
                    <div class="body-cell">
                        <span>150,00 €</span>
                    </div>
                </td>

                <!-- BADGE: Status -->
                <td class="table__body--row-cell table__cell--badge">
                    <div class="body-cell-badge">
                        <span class="badge badge--warning">Active</span>
                    </div>
                </td>

                <!-- NORMAL: Added -->
                <td class="table__body--row-cell table__cell--normal">
                    <div class="body-cell">
                        <span><time datetime="2025-12-18">2025-12-18</time></span>
                    </div>
                </td>

                <!-- ACTION -->
                <td class="table__body--row-cell table__cell--action">
                    <div class="body-cell-action">
                        <form action="product-show/index" method="POST">
                            <input type="hidden" name="csrfToken" value="...">
                            <input type="hidden" name="public_id" value="b0615b1e-a645-4908-be3d-0ee17de4c972">
                            <button class="action-btn" type="submit">
                                <svg class="icon eye" aria-hidden="true">
                                    <use href="/public/assets/img/icons-sprite.svg#icon-eye"></use>
                                </svg>
                                <span class="visually-hidden">View Product with main image</span>
                            </button>
                        </form>
                        <form action="admin/product-edit" method="GET">
                            <input type="hidden" name="public_id" value="b0615b1e-a645-4908-be3d-0ee17de4c972">
                            <button class="action-btn" type="submit">
                                <svg class="icon edit" aria-hidden="true">
                                    <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                                </svg>
                                <span class="visually-hidden">Edit Product with main image</span>
                            </button>
                        </form>
                        <form action="admin/confirm-product-deletion" method="POST">
                            <input type="hidden" name="csrfToken" value="...">
                            <input type="hidden" name="public_id" value="b0615b1e-a645-4908-be3d-0ee17de4c972">
                            <button class="action-btn modal-open-btn" data-action="open-delete-modal" type="button">
                                <svg class="icon trash" aria-hidden="true">
                                    <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                                </svg>
                                <span class="visually-hidden">Delete Product with main image</span>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            <!-- Row 2: Without image, selected -->
            <tr class="table__body--row row--selected">

                <!-- START: No image variant -->
                <th class="table__body--row-cell table__cell--start" scope="row">
                    <div class="body-cell-start body-cell-start--checkbox">
                        <input type="checkbox" id="select_row2" name="products[]"
                            value="9b15ba35-3b51-425f-9ead-3f8497fed5ee" checked>
                        <label class="body-cell-start__label" for="select_row2">
                            <span class="img-container"></span>
                            <span class="text-container">
                                <span class="text-container__name">PriceTest4</span>
                                <span class="text-container__variant">No variants</span>
                            </span>
                        </label>
                    </div>
                </th>

                <td class="table__body--row-cell table__cell--normal">
                    <div class="body-cell body-cell--sku">
                        <span>PriceTest4111</span>
                    </div>
                </td>

                <td class="table__body--row-cell table__cell--normal">
                    <div class="body-cell">
                        <span>Kids' Clothing</span>
                    </div>
                </td>

                <td class="table__body--row-cell table__cell--normal">
                    <div class="body-cell">
                        <span>0</span>
                    </div>
                </td>

                <td class="table__body--row-cell table__cell--normal">
                    <div class="body-cell">
                        <span>—</span>
                    </div>
                </td>

                <td class="table__body--row-cell table__cell--badge">
                    <div class="body-cell-badge">
                        <span class="badge badge--warning">Active</span>
                    </div>
                </td>

                <td class="table__body--row-cell table__cell--normal">
                    <div class="body-cell">
                        <span><time datetime="2025-12-18">2025-12-18</time></span>
                    </div>
                </td>

                <td class="table__body--row-cell table__cell--action">
                    <div class="body-cell-action">
                        <form action="product-show/index" method="POST">
                            <input type="hidden" name="csrfToken" value="...">
                            <input type="hidden" name="public_id" value="9b15ba35-3b51-425f-9ead-3f8497fed5ee">
                            <button class="action-btn" type="submit">
                                <svg class="icon eye" aria-hidden="true">
                                    <use href="/public/assets/img/icons-sprite.svg#icon-eye"></use>
                                </svg>
                                <span class="visually-hidden">View PriceTest4</span>
                            </button>
                        </form>
                        <form action="admin/product-edit" method="GET">
                            <input type="hidden" name="public_id" value="9b15ba35-3b51-425f-9ead-3f8497fed5ee">
                            <button class="action-btn" type="submit">
                                <svg class="icon edit" aria-hidden="true">
                                    <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                                </svg>
                                <span class="visually-hidden">Edit PriceTest4</span>
                            </button>
                        </form>
                        <form action="admin/confirm-product-deletion" method="POST">
                            <input type="hidden" name="csrfToken" value="...">
                            <input type="hidden" name="public_id" value="9b15ba35-3b51-425f-9ead-3f8497fed5ee">
                            <button class="action-btn modal-open-btn" data-action="open-delete-modal" type="button">
                                <svg class="icon trash" aria-hidden="true">
                                    <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                                </svg>
                                <span class="visually-hidden">Delete PriceTest4</span>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

        </tbody>
    </table>
</div>