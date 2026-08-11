   <div id="column-modal" class="modal" style="display: none;">
       <div class="modal-content">
           <div class="modal-header">
               <h3 id="column-modal-title">Add Column</h3>
               <button class="close-btn">&times;</button>
           </div>
           <form id="column-form">
               <input type="hidden" id="column-id">
               <div class="form-group">
                   <label>Column Key *</label>
                   <input type="text" id="column-key" required class="form-control">
                   <small>Unique identifier (e.g., "company", "resources")</small>
               </div>
               <div class="form-group">
                   <label>Title *</label>
                   <input type="text" id="column-title" required class="form-control">
               </div>
               <div class="form-group">
                   <label>Sort Order</label>
                   <input type="number" id="column-sort" value="0" class="form-control">
               </div>
               <div class="form-group">
                   <label class="checkbox-label">
                       <input type="checkbox" id="column-active" checked>
                       <span>Active</span>
                   </label>
               </div>
               <div class="form-row">
                   <div class="form-group">
                       <label>Valid From</label>
                       <input type="datetime-local" id="column-valid-from" class="form-control">
                   </div>
                   <div class="form-group">
                       <label>Valid To</label>
                       <input type="datetime-local" id="column-valid-to" class="form-control">
                   </div>
               </div>
               <div class="form-actions">
                   <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                   <button type="submit" class="btn btn-primary">Save</button>
               </div>
           </form>
       </div>
   </div>