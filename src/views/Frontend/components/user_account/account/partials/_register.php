<section class="container" id="login-container">
    <div class="login-wrapper">

        <form
            action="<?= PROOT . 'login' . DS . 'register'?>"
            method="POST" class="login-frm">
            <h2>Register</h2>
            <hr class="horizontal-line">
            {{csrf}}
            <div class="input-box mb-3">
                <input type="text" class="form-control input-box__input" name="firstName" id="login_firstName"
                    placeholder=" " autocomplete="nrpl">
                <label for="login_firstName" class="input-box__label">Prenom<span class="text-danger">*</span></label>
                <div class="invalid-feedback"></div>
            </div>
            <div class="input-box mb-3">
                <input type="text" class="form-control input-box__input" name="lastName" id="login_lastName"
                    placeholder=" " autocomplete="nrpl">
                <label for="login_lastName" class="input-box__label">Nom<span class="text-danger">*</span></label>
                <div class="invalid-feedback"></div>
            </div>
            <div class="input-box mb-3">
                <input type="email" class="form-control input-box__input" name="phone" id="login_phone" placeholder=" "
                    autocomplete="nrpl">
                <label for="login_phone" class="input-box__label">Téléphone</label>
                <div class="invalid-feedback"></div>
            </div>
            <div class="input-box mb-3">
                <input type="email" class="form-control input-box__input" name="email" id="login_email" placeholder=" "
                    autocomplete="nrpl">
                <label for="login_email" class="input-box__label">Email address<span
                        class="text-danger">*</span></label>
                <div class="invalid-feedback"></div>
            </div>
            <div class="input-box mb-3">
                <input type="password" name="password" class="form-control input-box__input" id="login_password"
                    placeholder=" " autocomplete="nrpl">
                <label for="login_password" class="input-box__label">Password<span class="text-danger">*</span></label>
                <div class="invalid-feedback"></div>
            </div>
            <div class="input-box mb-3">
                <input type="password" name="cpassword" class="form-control input-box__input" id="login_cpassword"
                    placeholder=" " autocomplete="nrpl">
                <label for="login_cpassword" class="input-box__label">Contirm your Password<span
                        class="text-danger">*</span></label>
                <div class="invalid-feedback"></div>
            </div>
            <div class="mt-2">
                <label class="checkbox d-inline-block me-3" for="terms">
                    <input class="checkbox__input" id="terms" type="checkbox" name="terms">
                    <span class="checkbox__box"></span>
                    I accept Terms and conditions<span class="text-danger">*</span>
                </label>
            </div>
            <button id="submit" type="submit" value="submit">Submit</button>
            <p class="options">Already have an account ? <a href="{{link}}">Sign In</a>
            </p>
        </form>
    </div>
</section>