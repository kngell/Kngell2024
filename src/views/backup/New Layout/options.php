<div class="options">
    <div class="options-box selected" data-option="light" role="button">
        <input type="radio" id="theme-light" style="display: none" name="theme_preference" value="light" checked>
        <span class="options-box__title">Light Mode</span>
        <span class="options-box__description">Light theme for bright environments</span>
    </div>

    <div class="options-box" data-option="dark" role="button">
        <input type="radio" id="theme-dark" style="display: none" name="theme_preference" value="dark">
        <span class="options-box__title">Dark Mode</span>
        <span class="options-box__description">Dark theme for reduced eye strain</span>
    </div>
    <!-- Hidden input to store the value for server submission -->
    <input type="hidden" name="theme_preference" value="light">
</div>