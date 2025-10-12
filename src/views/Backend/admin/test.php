<?php declare(strict_types=1);
$this->start('head'); ?>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    color: #333;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem;
}

.container {
    max-width: 800px;
    width: 100%;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    margin: 2rem 0;
}

h1 {
    text-align: center;
    margin-bottom: 2rem;
    color: #2c3e50;
    font-weight: 600;
}

h2 {
    color: #3498db;
    margin: 1.5rem 0 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #eee;
}

.description {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.input-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

@media (max-width: 768px) {
    .input-grid {
        grid-template-columns: 1fr;
    }
}

/* Fixed Input Box Mixin Implementation */
.input-box {
    width: 100%;
    font-size: 1.6rem;
    margin-bottom: 1.6rem;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    position: relative;
}

.input-box__label {
    color: #666;
    position: absolute;
    top: 50%;
    left: 1.2rem;
    transform: translateY(-50%);
    background-color: transparent;
    padding: 0;
    pointer-events: none;
    transform-origin: left center;
    z-index: 1;
    transition: top 0.2s ease, transform 0.2s ease, font-size 0.2s ease, color 0.2s ease, left 0.2s ease;
}

.input-box__input {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    flex: 1 0 0;
    width: 100%;
    font: inherit;
    padding: 0.8rem 1.2rem;
    border: 1px solid #e0e2e7;
    border-radius: 0.8rem;
    font-size: inherit;
    background-color: #f9f9fc;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    z-index: 0;
}

.input-box__input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
}

.input-box__input input {
    width: 100%;
    font: inherit;
    border: none;
    background: transparent;
    outline: none;
}

.input-box__input input::placeholder {
    opacity: 0;
    transition: opacity 0.2s ease;
}

.input-box__input input:focus::placeholder {
    opacity: 1;
    color: #aaa;
}

.input-box__prefix,
.input-box__suffix {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
}

/* Floating label logic */
.input-box:focus-within .input-box__label,
.input-box__input input:not(:placeholder-shown)+.input-box__label,
.input-box__input input[value]:not([value=""])+.input-box__label,
.input-box__input:has(input:not(:placeholder-shown))~.input-box__label,
.input-box__input:has(input[value]:not([value=""]))~.input-box__label {
    top: 0;
    transform: translateY(-50%);
    font-size: 1.2rem;
    color: #333;
    background-color: #f9f9fc;
    padding: 0 0.4rem;
    left: 1.2rem;
    z-index: 2;
    border-radius: 4px;
}

/* Adjust label position when prefix exists */
.input-box:has(.input-box__prefix) .input-box__label {
    left: calc(1.2rem + 1.6rem + 0.8rem);
}

.input-box:has(.input-box__prefix):focus-within .input-box__label,
.input-box:has(.input-box__prefix) .input-box__input input:not(:placeholder-shown)+.input-box__label,
.input-box:has(.input-box__prefix) .input-box__input input[value]:not([value=""])+.input-box__label,
.input-box:has(.input-box__prefix) .input-box__input:has(input:not(:placeholder-shown))~.input-box__label,
.input-box:has(.input-box__prefix) .input-box__input:has(input[value]:not([value=""]))~.input-box__label {
    left: 1.2rem;
}

.input-box__hint-text {
    font-size: 1.3rem;
    color: red;
    display: none;
    margin-top: 0.8rem;
    padding-left: 1.2rem;
}

.input-box.has-error .input-box__input {
    border-color: red;
}

.input-box.has-error .input-box__input:focus {
    box-shadow: 0 0 0 2px rgba(255, 0, 0, 0.1);
}

.input-box.has-error .input-box__hint-text {
    display: block;
}

/* Non-floating variant */
.input-box--non-floating .input-box__label {
    position: relative;
    order: -1;
    margin-bottom: 0.4rem;
    left: 0;
    transform: none;
}

.input-box--non-floating .input-box__input {
    margin-bottom: 0.8rem;
}

.input-box--non-floating .input-box__input input::placeholder {
    opacity: 1;
    color: #aaa;
}

/* Example styles */
.example {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.code {
    background: #2d2d2d;
    color: #f8f8f2;
    padding: 1rem;
    border-radius: 5px;
    font-family: 'Fira Code', monospace;
    overflow-x: auto;
    margin: 1rem 0;
    font-size: 0.9rem;
}

.visual-example {
    border: 1px solid #e0e2e7;
    border-radius: 8px;
    padding: 1.5rem;
    margin: 1rem 0;
    background: white;
}

.toggle-button {
    background: #3498db;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 0.5rem;
    font-size: 0.9rem;
}

.toggle-button:hover {
    background: #2980b9;
}
</style>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
    <!-- Content -->


    <div class="container">
        <div class="description">
            <p>This implementation fixes the floating label and placeholder visibility issues. The solution includes:
            </p>
            <ul>
                <li>Placeholder only visible when typing (not by default)</li>
                <li>Label positioning that accounts for prefix icons</li>
                <li>Proper icon size parameter support</li>
                <li>Support for both direct and nested input structures</li>
            </ul>
        </div>

        <div class="input-grid">
            <div>
                <h2>Direct Input Examples</h2>

                <div class="example">
                    <h3>Basic Floating Input</h3>
                    <div class="visual-example">
                        <div class="input-box">
                            <input type="text" class="input-box__input" id="username" placeholder="Enter your username">
                            <label for="username" class="input-box__label">Username</label>
                            <span class="input-box__hint-text"></span>
                        </div>
                    </div>
                    <button class="toggle-button" onclick="toggleError('username')">Toggle Error State</button>
                </div>

                <div class="example">
                    <h3>Floating Input with Value</h3>
                    <div class="visual-example">
                        <div class="input-box">
                            <input type="text" class="input-box__input" id="email" placeholder="Enter your email"
                                value="john.doe@example.com">
                            <label for="email" class="input-box__label">Email Address</label>
                            <span class="input-box__hint-text"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h2>Nested Input Examples</h2>

                <div class="example">
                    <h3>With Prefix Icon</h3>
                    <div class="visual-example">
                        <div class="input-box">
                            <div class="input-box__input">
                                <span class="input-box__prefix"><i class="fas fa-user"></i></span>
                                <input type="text" id="name" placeholder="Enter your full name">
                            </div>
                            <label for="name" class="input-box__label">Full Name</label>
                            <span class="input-box__hint-text"></span>
                        </div>
                    </div>
                    <button class="toggle-button" onclick="toggleError('name')">Toggle Error State</button>
                </div>

                <div class="example">
                    <h3>With Prefix and Suffix Icons</h3>
                    <div class="visual-example">
                        <div class="input-box">
                            <div class="input-box__input">
                                <span class="input-box__prefix"><i class="fas fa-key"></i></span>
                                <input type="password" id="password" placeholder="Enter your password">
                                <span class="input-box__suffix"><i class="fas fa-eye"></i></span>
                            </div>
                            <label for="password" class="input-box__label">Password</label>
                            <span class="input-box__hint-text"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h2>Non-Floating Variant</h2>
        <div class="example">
            <div class="visual-example">
                <div class="input-box input-box--non-floating">
                    <input type="text" class="input-box__input" id="simple-input" placeholder="Enter some text">
                    <label for="simple-input" class="input-box__label">Simple Input</label>
                    <span class="input-box__hint-text"></span>
                </div>
            </div>
        </div>

        <h2>Implementation Code</h2>
        <div class="code">
            // SCSS Mixin with fixes
            @mixin input-box(
            $input-box: input-box,
            $floating: true,
            $border: true,
            $padding: 0.8rem 1.2rem,
            $border-color: #e0e2e7,
            $radius: 0.8rem,
            $focus-color: #007bff,
            $label-color: #666,
            $label-floating-color: #333,
            $font-size: 1.6rem,
            $label-float-size: 1.2rem,
            $background: #f9f9fc,
            $gap-label-input: 0.4rem,
            $gap-input-hint: 0.8rem,
            $gap-typing-area: 0.8rem,
            $icon-size: 1.6rem, // New parameter for icon size
            $label-text-style: null,
            $placeholder-text-style: null,
            $with-multiple-label-children: false
            ) {
            // ... (full implementation as provided in the answer)
            }
        </div>
    </div>




    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<script>
function toggleError(fieldId) {
    const inputBox = document.querySelector(`#${fieldId}`).closest('.input-box');
    inputBox.classList.toggle('has-error');

    const hintText = inputBox.querySelector('.input-box__hint-text');
    if (inputBox.classList.contains('has-error')) {
        hintText.textContent = 'This field is required';
    } else {
        hintText.textContent = '';
    }
}

// Add interaction to the password visibility toggle
document.querySelector('.fa-eye').closest('.input-box__suffix').addEventListener('click', function() {
    const passwordInput = this.closest('.input-box__input').querySelector('input');
    const eyeIcon = this.querySelector('i');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
});
</script>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();