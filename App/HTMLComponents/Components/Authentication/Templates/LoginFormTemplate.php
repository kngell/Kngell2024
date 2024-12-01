<div class="form-wrapper rounded bg-light" id="">
    <p class="hint-text">Connectez-vous avec votre compte social média</p>
    <div class="social-btn clearfix mb-3">
        <a href="javascript:void(0)" class="btn btn-primary  float-start" id="fblink"><i class="fab fa-facebook"></i>
            Facebook</a>
        <a href="#" class="btn btn-info float-end"><i class="fab fa-twitter"></i>
            Twitter</a>
    </div>
    <div class="or-seperator"><b>ou</b></div>
    <!--Log-->
    {{form_begin}}
    {{email}}
    {{password}}
    <div class="row g-3">
        <div class="col">{{remamber_me}}</div>
        <div class="col">
            <a href="#" id="forgot-btn" class="float-end" class="close" data-bs-dismiss="modal" data-bs-toggle="modal"
                data-bs-target="#forgot-box">Mot
                de
                passe oublié</a>
        </div>
    </div>
    {{submit}}
    {{form_end}}
</div>