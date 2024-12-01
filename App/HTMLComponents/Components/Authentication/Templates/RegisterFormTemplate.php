<div class="form-wrapper rounded bg-light" id="">
   <div class="upload-profile-image d-flex justify-content-center pb-1">
      <div class="text-center">
         <div class="d-flex justify-content-center"> <img class="camera-icon" src="{{camera}}" alt="camera" />
         </div>
         <img src="{{avatar}}" class="img rounded-circle" alt="profile" />
         <small class="form-text">Profile</small>
         <input type="file" form="register-frm" class="form-control upload-profile" name="profileUpload"
            id="upload-profile">
      </div>
   </div>
   <hr class="mb-3">
   {{form_begin}}
   {{username}}
   {{email}}
   {{password}}
   {{cpassword}}
   {{terms}}
   {{submit}}
   {{form_end}}
</div>