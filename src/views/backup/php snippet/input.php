<!-- Normal Input -->
<div class="input-box">
    <input type="text" class="input-box__input" id="product-name" placeholder="Type product name here...">
    <label for="product-name" class="input-box__label">Product Name</label>
    <span class="input-box__hint-text"></span>
</div>

<!-- Checkbox with icon in label -->
<div class="input-box">
    <input type="checkbox" id="consent" class="input-box__input" required>
    <label for="consent" class="input-box__label">
        I consent to my data being processed
        <span class="input-box__suffix-icon">
            <svg class="icon info" aria-label="Information" role="img">
                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-info"></use>
            </svg>
        </span>
    </label>
    <span class="input-box__hint-text"></span>
</div>

<!-- Radio with icon in label -->
<div class="input-box input-box--pill-style">
    <input type="radio" id="support" name="query" class="input-box__input" value="Support request" required>
    <label for="support" class="input-box__label">
        Support request
        <span class="input-box__suffix-icon">
            <svg class="icon help" aria-label="Help" role="img">
                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-help"></use>
            </svg>
        </span>
    </label>
</div>

<div class="header-cell-product">
    <!-- Checkbox for select all -->
    <div class="header-cell-product__checkbox">
        <input type="checkbox" id="select-all" aria-labelledby="select-all-label" />
        <label for="select-all" id="select-all-label">Select all products</label>
    </div>

    <!-- Separate dropdown for advanced selection -->
    <div class="header-cell-product__dropdown-container">
        <button class="header-cell-product__dropdown-btn" aria-expanded="false" aria-controls="advanced-selection">
            <span>Filter selection</span>
            <svg class="icon arrow-down" aria-hidden="true">
                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down"></use>
            </svg>
        </button>

        <div id="advanced-selection" class="header-cell-product__dropdown" hidden>
            <!-- Advanced selection options -->
        </div>
    </div>

    <span class="header-cell-product__hint-text"></span>
</div>

<div class="header-cell-product">
    <span id="select-all-label" class="visually-hidden">Select all products</span>
    <input type="checkbox" id="select-all" aria-labelledby="select-all-label" class="header-cell-product__input" />

    <label for="select-all" class="header-cell-product__label">
        products
        <span class="header-cell-product__suffix-icon">
            <svg class="icon arrow-down" aria-label="Arrow Down" role="img">
                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down"></use>
            </svg>
        </span>
    </label>
    <span class="header-cell-product__hint-text"></span>
</div>

<div class="header-cell-product">
    <div class="header-cell-product__top-row">
        <!-- Checkbox for select all -->
        <div class="checkbox-box">
            <span id="select-all-label" class="visually-hidden">Select all products</span>
            <input type="checkbox" id="select-all" class="checkbox-box__input" aria-labelledby="select-all-label" />
            <label for="select-all" class="checkbox-box__label" id="select-all-label">products</label>
        </div>
        <!-- Separate dropdown for advanced selection -->
        <div class="dropdown-container">
            <button class="dropdown-container__btn" aria-expanded="false" aria-controls="advanced-selection">
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
    <span class="header-cell-product__hint-text"></span>
</div>