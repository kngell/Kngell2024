 <div class="form-card">
     <h2>About Section Configuration</h2>

     <form id="about-form">
         <div class="form-group">
             <label for="about-content">Content</label>
             <textarea id="about-content" name="content" rows="5"
                 class="form-control">We are dedicated to providing innovative solutions that help businesses grow and succeed in the digital age. With over 10 years of experience, our team is committed to excellence and customer satisfaction.</textarea>
         </div>

         <div class="form-row">
             <div class="form-group">
                 <label for="logo-url">Logo URL</label>
                 <input type="text" id="logo-url" name="logo_url" value="/assets/images/logo-footer.png"
                     class="form-control">
             </div>
             <div class="form-group">
                 <label for="logo-icon">Logo Icon</label>
                 <input type="text" id="logo-icon" name="logo_icon" value="icon-logo-footer" class="form-control">
             </div>
         </div>

         <div class="form-row">
             <div class="form-group">
                 <label for="logo-alt">Logo Alt Text</label>
                 <input type="text" id="logo-alt" name="logo_alt" value="Company Logo" class="form-control">
             </div>
             <div class="form-group">
                 <label for="logo-link">Logo Link</label>
                 <input type="text" id="logo-link" name="logo_link" value="/" class="form-control">
             </div>
         </div>

         <div class="form-group">
             <label class="checkbox-label">
                 <input type="checkbox" name="is_active" value="1" checked>
                 <span>Active</span>
             </label>
         </div>

         <div class="form-row">
             <div class="form-group">
                 <label for="valid-from">Valid From</label>
                 <input type="datetime-local" id="valid-from" name="valid_from" class="form-control">
             </div>
             <div class="form-group">
                 <label for="valid-to">Valid To</label>
                 <input type="datetime-local" id="valid-to" name="valid_to" class="form-control">
             </div>
         </div>

         <div class="form-actions">
             <button type="submit" class="btn btn-primary">Save About Section</button>
         </div>
     </form>
 </div>