<div class="category__body">
    <form class="category-form">
        <!-- Radios at form level (siblings to tabs and content) -->
        <input type="radio" name="form-tab" id="tab-category-infos" class="radio-tab" checked>
        <input type="radio" name="form-tab" id="tab-content-display" class="radio-tab">
        <input type="radio" name="form-tab" id="tab-price" class="radio-tab">
        <input type="radio" name="form-tab" id="tab-advanced" class="radio-tab" disabled>
        <div class="category-form__tabs">
            <label for="tab-category-infos" class="tab">
                <h6 class="tab__text">Category Informations</h6>
            </label>
            <label for="tab-content-display" class="tab">
                <h6 class="tab__text">Content and display</h6>
            </label>
            <label for="tab-price" class="tab">
                <h6 class="tab__text">Price Range and nevaigation</h6>
            </label>
            <label for="tab-advanced" class="tab tab__disabled">
                <h6 class="tab__text">Advanced (Disabled)</h6>
            </label>
        </div>

        <div class="category-form__content">

            <!-- TAB 1: Category Information -->
            <div class="tab-content category-form__content--infos">
                <div class="category-form__left">
                    <!-- Basic Information section -->
                    <div class="form-section basic-information">
                        <div class="form-section__header">
                            <div class="form-section__header-left">
                                <div class="icon-container">
                                    <svg class="icon cancel" aria-label="Cancel" role="img">
                                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-edit2">
                                        </use>
                                    </svg>
                                </div>
                                <h6 class="title">Basic Information</h6>
                            </div>
                            <span class="form-section__header-right">Required</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-row horizontal">
                                <div class="input-field">
                                    <div class="input-field__body">
                                        <input type="text" class="input-field__input" id="category_name"
                                            placeholder=" " />
                                        <label for="category_name" class="input-field__label">Category
                                            Name</label>
                                    </div>
                                    <div class="input-field__footer">
                                        <span class="input-field__error"></span>
                                    </div>
                                </div>
                                <div class="input-field">
                                    <div class="input-field__body">
                                        <input type="text" class="input-field__input" id="category_icon"
                                            placeholder=" " />
                                        <label for="category_icon" class="input-field__label">Category
                                            Icon</label>
                                    </div>
                                    <div class="input-field__footer">
                                        <span class="input-field__error"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row vertical">
                                <div class="input-field custom-select">
                                    <div class="input-field__body">
                                        <button type="button" class="input-field__custom-select has-right-icon">
                                            <span class="text">Parent Category...</span>
                                            <span class="input-field__icon-right">
                                                <svg class="icon cancel" aria-label="Cancel" role="img">
                                                    <use
                                                        href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                                    </use>
                                                </svg>
                                            </span>
                                        </button>
                                        <div class="input-field__custom-options">
                                            <button type="button" class="search-group">
                                                <div class="search-group__icon-container">
                                                    <svg class="icon cancel" aria-label="Cancel" role="img">
                                                        <use
                                                            href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search">
                                                        </use>
                                                    </svg>
                                                </div>
                                                <input type="text" class="search-group__input-search"
                                                    placeholder="search" />
                                            </button>
                                            <ul class="option-list"></ul>
                                        </div>
                                    </div>
                                    <div class="input-field__footer">
                                        <span class="input-field__helper"></span>
                                        <span class="input-field__footer-counter">0/3</span>
                                    </div>
                                    <input type="hidden" name="product_id" class="input-field__hidden-value" value="">
                                </div>
                                <div class="input-field">
                                    <div class="input-field__body">
                                        <input type="text" class="input-field__input" id="short_description"
                                            placeholder=" " />
                                        <label for="short_description" class="input-field__label">Short
                                            Description</label>
                                    </div>
                                </div>
                                <div class="input-field">
                                    <div class="input-field__body">
                                        <textarea class="input-field__textarea" id="description" placeholder=" "
                                            name="description"></textarea>
                                        <label class="input-field__label" for="description">Description</label>
                                    </div>
                                    <div class="input-field__footer">
                                        <span class="input-field__error" style="display: none;"></span>
                                        <span class="input-field__footer-counter">0/255</span>
                                    </div>
                                </div>
                                <div class="settings">
                                    <div class="toggle-switch">
                                        <span class="label">Show Menu</span>
                                        <label class="toggle">
                                            <input type="checkbox" name="show_menu">
                                            <span class="track"></span>
                                            <span class="knob"></span>
                                        </label>
                                    </div>
                                    <div class="toggle-switch">
                                        <span class="label">Active Status</span>
                                        <label class="toggle">
                                            <input type="checkbox" name="active_status">
                                            <span class="track"></span>
                                            <span class="knob"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO & Social Media section -->
                    <div class="form-section social-media">
                        <div class="form-section__header">
                            <div class="form-section__header-left">
                                <div class="icon-container">
                                    <svg class="icon cancel" aria-label="Cancel" role="img">
                                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-facebook">
                                        </use>
                                    </svg>
                                </div>
                                <h6 class="title">SEO & Social Media</h6>
                            </div>
                            <span class="form-section__header-right hide">Required</span>
                        </div>
                        <div class="form-section__body">
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="text" class="input-field__input" id="meta_title" placeholder=" " />
                                    <label for="meta_title" class="input-field__label">Meta Title</label>
                                </div>
                            </div>
                            <div class="input-field">
                                <div class="input-field__body">
                                    <textarea class="input-field__textarea" id="meta_description" placeholder=" "
                                        name="meta_description"></textarea>
                                    <label class="input-field__label" for="meta_description">Meta
                                        Description</label>
                                </div>
                                <div class="input-field__footer">
                                    <span class="input-field__error" style="display: none;"></span>
                                    <span class="input-field__footer-counter">0/255</span>
                                </div>
                            </div>
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="text" class="input-field__input" id="meta_keyword" placeholder=" " />
                                    <label for="meta_keyword" class="input-field__label">Meta Keyword</label>
                                </div>
                            </div>
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="text" class="input-field__input" id="twitter_card" placeholder=" " />
                                    <label for="twitter_card" class="input-field__label">Twitter Card</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Open Graph section -->
                    <div class="form-section open-graph">
                        <div class="form-section__header">
                            <div class="form-section__header-left">
                                <div class="icon-container">
                                    <svg class="icon cancel" aria-label="Cancel" role="img">
                                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-lock"></use>
                                    </svg>
                                </div>
                                <h6 class="title">Open Graph</h6>
                            </div>
                            <span class="form-section__header-right hide">Required</span>
                        </div>
                        <div class="form-section__body">
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="text" class="input-field__input" id="og_title" placeholder=" " />
                                    <label for="og_title" class="input-field__label">OG Title</label>
                                </div>
                            </div>
                            <div class="input-field">
                                <div class="input-field__body">
                                    <textarea class="input-field__textarea" id="og_description" placeholder=" "
                                        name="og_description"></textarea>
                                    <label class="input-field__label" for="og_description">OG
                                        Description</label>
                                </div>
                                <div class="input-field__footer">
                                    <span class="input-field__error" style="display: none;"></span>
                                    <span class="input-field__footer-counter">0/255</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="category-form__right">
                    <!-- Category Image section -->
                    <div class="form-section category-image">
                        <div class="form-section__header">
                            <div class="form-section__header-left">
                                <div class="icon-container">
                                    <svg class="icon cancel" aria-label="Cancel" role="img">
                                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-image">
                                        </use>
                                    </svg>
                                </div>
                                <h6 class="title">Category Image</h6>
                            </div>
                            <span class="form-section__header-right hide">Required</span>
                        </div>
                        <div class="form-section__body">
                            <div class="upload-single" data-state="empty" data-mode="single">
                                <div class="upload-single__icon">
                                    <svg>
                                        <use href="#icon-upload"></use>
                                    </svg>
                                </div>
                                <div class="upload-single__text">
                                    <span class="upload-single__main-text">Drag & drop or click to upload</span>
                                    <span class="upload-single__hint-text">PNG, JPG, GIF • Max 5MB</span>
                                </div>
                                <input type="file" accept="image/*" name="category_image">
                            </div>
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="text" class="input-field__input" id="image_alt" placeholder=" " />
                                    <label for="image_alt" class="input-field__label">Alt Text</label>
                                </div>
                                <div class="input-field__footer">
                                    <span class="input-field__error"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OG Image section -->
                    <div class="form-section og-image">
                        <div class="form-section__header">
                            <div class="form-section__header-left">
                                <div class="icon-container">
                                    <svg class="icon cancel" aria-label="Cancel" role="img">
                                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-image">
                                        </use>
                                    </svg>
                                </div>
                                <h6 class="title">OG Image</h6>
                            </div>
                            <span class="form-section__header-right hide">Required</span>
                        </div>
                        <div class="form-section__body">
                            <div class="upload-single" data-state="empty" data-mode="single">
                                <div class="upload-single__icon">
                                    <svg>
                                        <use href="#icon-upload"></use>
                                    </svg>
                                </div>
                                <div class="upload-single__text">
                                    <span class="upload-single__main-text">Drag & drop or click to upload</span>
                                    <span class="upload-single__hint-text">PNG, JPG, GIF • Max 5MB</span>
                                </div>
                                <input type="file" accept="image/*" name="og_image">
                            </div>
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="text" class="input-field__input" id="og_alt" placeholder=" " />
                                    <label for="og_alt" class="input-field__label">Alt Text</label>
                                </div>
                                <div class="input-field__footer">
                                    <span class="input-field__error"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Canonical Image section -->
                    <div class="form-section canonical-image">
                        <div class="form-section__header">
                            <div class="form-section__header-left">
                                <div class="icon-container">
                                    <svg class="icon cancel" aria-label="Cancel" role="img">
                                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-image">
                                        </use>
                                    </svg>
                                </div>
                                <h6 class="title">Image Urls</h6>
                            </div>
                            <span class="form-section__header-right hide">Required</span>
                        </div>
                        <div class="form-section__body">

                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="text" class="input-field__input" id="canonical_alt" placeholder=" " />
                                    <label for="canonical_alt" class="input-field__label">Cannonical image
                                        URL</label>
                                </div>
                                <div class="input-field__footer">
                                    <span class="input-field__error"></span>
                                </div>
                            </div>
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="text" class="input-field__input" id="canonical_alt" placeholder=" " />
                                    <label for="canonical_alt" class="input-field__label">OG Image Url</label>
                                </div>
                                <div class="input-field__footer">
                                    <span class="input-field__error"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Content and Display -->
            <div class="tab-content category-form__content--content">
                <div class="category-form__left">
                    <div class="form-section content-display">
                        <div class="form-section__header">
                            <div class="form-section__header-left">
                                <div class="icon-container">
                                    <svg class="icon cancel" aria-label="Cancel" role="img">
                                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-edit2">
                                        </use>
                                    </svg>
                                </div>
                                <h6 class="title">Content and Display</h6>
                            </div>
                            <span class="form-section__header-right hide">Required</span>
                        </div>
                        <div class="form-section__body">
                            <div class="input-field">
                                <div class="input-field__body">
                                    <textarea class="input-field__textarea" id="content" placeholder=" "
                                        name="content"></textarea>
                                    <label class="input-field__label" for="content">Content</label>
                                </div>
                                <div class="input-field__footer">
                                    <span class="input-field__error" style="display: none;"></span>
                                    <span class="input-field__footer-counter">0/255</span>
                                </div>
                            </div>
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="text" class="input-field__input" id="template_name" placeholder=" " />
                                    <label for="template_name" class="input-field__label">Template Name</label>
                                </div>
                            </div>
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="text" class="input-field__input" id="css_class" placeholder=" " />
                                    <label for="css_class" class="input-field__label">CSS Class</label>
                                </div>
                            </div>
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="color" class="input-field__input" id="background_color"
                                        placeholder=" " />
                                    <label for="background_color" class="input-field__label">Background
                                        Color</label>
                                </div>
                            </div>
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="color" class="input-field__input" id="text_color" placeholder=" " />
                                    <label for="text_color" class="input-field__label">Text Color</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="category-form__right">
                    <div class="form-section navigation-visibility">
                        <div class="form-section__header">
                            <div class="form-section__header-left">
                                <div class="icon-container">
                                    <svg class="icon cancel" aria-label="Cancel" role="img">
                                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-edit2">
                                        </use>
                                    </svg>
                                </div>
                                <h6 class="title">Navigation and Visibility</h6>
                            </div>
                            <span class="form-section__header-right hide">Required</span>
                        </div>
                        <div class="form-section__body">
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="text" class="input-field__input" id="custom_url" placeholder=" " />
                                    <label for="custom_url" class="input-field__label">Custom URL</label>
                                </div>
                            </div>
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="text" class="input-field__input" id="redirect_url" placeholder=" " />
                                    <label for="redirect_url" class="input-field__label">Redirect URL</label>
                                </div>
                            </div>
                            <div class="input-field">
                                <div class="input-field__body">
                                    <input type="number" class="input-field__input" id="max_depth" placeholder=" " />
                                    <label for="max_depth" class="input-field__label">Max Depth</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Display Settings -->
            <div class="tab-content category-form__content--settings">
                <div class="category-form__left">
                    <div class="form-section price-range">
                        <div class="form-section__header">
                            <div class="form-section__header-left">
                                <div class="icon-container">
                                    <svg class="icon cancel" aria-label="Cancel" role="img">
                                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-settings">
                                        </use>
                                    </svg>
                                </div>
                                <h6 class="title">Display Settings</h6>
                            </div>
                        </div>
                        <div class="form-section__body">
                            <div class="form-row horizontal">
                                <div class="input-field">
                                    <div class="input-field__body">
                                        <input type="text" class="input-field__input" id="redirect_url"
                                            placeholder=" " />
                                        <label for="redirect_url" class="input-field__label">Redirect
                                            URL</label>
                                    </div>
                                </div>
                                <div class="input-field">
                                    <div class="input-field__body">
                                        <input type="text" class="input-field__input" id="redirect_url"
                                            placeholder=" " />
                                        <label for="redirect_url" class="input-field__label">Redirect
                                            URL</label>
                                    </div>
                                </div>
                            </div>
                            <div class="bracket-range">
                                <div class="bracket-range__card">
                                    <div class="bracket-range__card-header">
                                        <span class="card-title">Bracket</span>
                                        <div class="card-action">
                                            <div class="card-action__add-btn">
                                                <svg class="icon cancel" aria-label="Cancel" role="img">
                                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-plus">
                                                    </use>
                                                </svg>
                                            </div>
                                            <div class="card-action__remove-btn">
                                                <svg class="icon cancel" aria-label="Cancel" role="img">
                                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-minus">
                                                    </use>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bracket-range__card-body">
                                        <div class="form-row">
                                            <div class="input-field">
                                                <div class="input-field__body">
                                                    <input type="text" class="input-field__input" id="max_depth"
                                                        placeholder=" " />
                                                    <label for="max_depth" class="input-field__label">Label</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-row horizontal">
                                            <div class="input-field">
                                                <div class="input-field__body">
                                                    <input type="number" class="input-field__input" id="max_depth"
                                                        placeholder=" " />
                                                    <label for="max_depth" class="input-field__label">Min</label>
                                                </div>
                                            </div>
                                            <div class="input-field">
                                                <div class="input-field__body">
                                                    <input type="number" class="input-field__input" id="max_depth"
                                                        placeholder=" " />
                                                    <label for="max_depth" class="input-field__label">Max</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>