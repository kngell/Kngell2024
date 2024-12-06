<section class="container" id="login-container">
    <div class="login-wrapper">

        <form
            action="<?= PROOT . 'login' . DS . 'login'?>"
            method="POST" class="login-frm">
            <h2>Sign in</h2>
            <hr class="horizontal-line">
            {{csrf}}
            <div class="input-box mb-3">
                <input type="email" class="form-control input-box__input" name="email" id="login_email" placeholder=" "
                    autocomplete="nrpl">
                <label for="login_email" class="input-box__label">Email address<span
                        class="text-danger">*</span></label>
                <div class="invalid-feedback"></div>
            </div>
            <div class="input-box">
                <input type="password" name="password" class="form-control input-box__input" id="login_pw"
                    placeholder=" " autocomplete="nrpl">
                <label for="login_pw" class="input-box__label">Password<span class="text-danger">*</span></label>
                <div class="invalid-feedback"></div>
            </div>
            <button id="submit" type="submit" value="submit">Submit</button>
            <p class="options">Not Registered yet ? <a href="{{link}}">Create
                    an account</a>
            </p>
        </form>
    </div>
</section>