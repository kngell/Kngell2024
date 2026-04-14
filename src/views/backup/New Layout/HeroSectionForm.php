<form class="hero-form">
    <div class="hero-form__left">
        <div class="form-section">
            <div class="form-section__header">
                <div class="header-left">
                    <div class="header-left__icon-container">
                        <svg class="icon cancel" aria-label="Cancel" role="img">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-edit2"></use>
                        </svg>
                    </div>
                    <h6 class="header-left__title">
                        Basic Information
                    </h6>
                </div>
                <span class="header-right">
                    Required
                </span>
            </div>
            <div class="form-section__body">
                <div class="input-field">
                    <div class="input-field__body">
                        <input type="text" class="input-field__input" id="name1" placeholder=" " />
                        <label for="name1" class="input-field__label">
                            Hero Title
                        </label>
                        <span class="input-field__counter">0/255</span>
                    </div>
                    <div class="input-field__footer">
                        <span class="input-field__error">This field is required</span>
                        <span class="input-field__footer-counter">0/255</span>
                    </div>
                </div>
                <div class="input-field">
                    <input type="text" class="input-field__input" id="name2" placeholder=" " />
                    <label for="name2" class="input-field__label">
                        Hero SubTitle
                    </label>
                    <span class="input-field__counter">0/500</span>
                </div>
                <div class="input-field">
                    <input type="text" class="input-field__input" id="name3" placeholder=" " />
                    <label for="name3" class="input-field__label">
                        Page Target
                    </label>
                    <span class="input-field__counter">0/200</span>
                </div>
            </div>
        </div>
        <div class="form-section">
            <div class="form-section__header">
                <div class="header-left">
                    <div class="header-left__icon-container">
                        <svg class="icon cancel" aria-label="Cancel" role="img">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-edit2"></use>
                        </svg>
                    </div>
                    <h6 class="header-left__title">
                        Call to Action
                    </h6>
                </div>

            </div>
            <div class="form-section__body call-to-action">
                <div class="column">
                    <div class="column__title">
                        <p class="column__title--text">
                            Primary Button
                        </p>
                        <div class="column__title--toggle-switch">

                        </div>
                    </div>
                    <div class="column__content">
                        <div class="input-field">
                            <input type="text" class="input-field__input" id="cta1" placeholder=" " />
                            <label for="cta1" class="input-field__label">
                                Button Text
                            </label>
                        </div>
                        <div class="input-field">
                            <input type="text" class="input-field__input" id="cta1" placeholder=" " />
                            <label for="cta1" class="input-field__label">
                                Button Link
                            </label>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="column__title">
                        <p class="column__title--text">
                            Secondary Button
                        </p>
                        <div class="column__title--toggle-switch">
                            <input type="checkbox" id="toggle-1">
                            <label for="toggle-1" class="toggle">
                                <span class="track"></span>
                                <span class="knob"></span>
                            </label>
                        </div>
                    </div>
                    <div class="column__content">
                        <div class="input-field">
                            <input type="text" class="input-field__input" id="cta1" placeholder=" " />
                            <label for="cta1" class="input-field__label">
                                Button Text
                            </label>
                        </div>
                        <div class="input-field">
                            <input type="text" class="input-field__input" id="cta1" placeholder=" " />
                            <label for="cta1" class="input-field__label">
                                Button Link
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="hero-form__right upload">
        <div class="upload-header">
            <div class="header-left">
                <div class="header-left__icon-container">
                    <svg class="icon cancel" aria-label="Cancel" role="img">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-image"></use>
                    </svg>
                </div>
                <h6 class="header-left__title">
                    Media
                </h6>
            </div>
            <span class="header-right">
                Required
            </span>
        </div>
        <div class="upload-body">
            <!-- Single upload example -->
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
                <input type="file" accept="image/*">
            </div>

            <!-- Multiple upload example -->
            <div class="upload-multiple" data-state="empty" data-mode="multiple">
                <div class="upload-multiple__icon">
                    <svg>
                        <use href="#icon-upload"></use>
                    </svg>
                </div>
                <div class="upload-multiple__text">
                    <span class="upload-multiple__main-text">Drag & drop or click to upload</span>
                    <span class="upload-multiple__hint-text">PNG, JPG, GIF • Max 5MB each</span>
                </div>
                <input type="file" multiple accept="image/*">
            </div>
        </div>
    </div>
</form>