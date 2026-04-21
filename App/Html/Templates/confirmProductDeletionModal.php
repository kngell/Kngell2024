<div class="modal-overlay confirm-deletion-modal">
    <div class="modal confirm-deletion">
        <button type="button" class="modal-close-btn" aria-label="Close modal" data-modal-close>
            {{icon-close}}
        </button>
        <!-- Header -->
        <div class="modal-header confirm-deletion__header">
            <h4 class="title">Delete Confirmation</h4>
            <span class="content">
                <div class="content__icon-container">
                    {{icon-warning}}
                </div>
                {{deletion_subtitle}}
            </span>
        </div>
        <!-- Body -->
        {{confirmDeletionForm}}
    </div>
</div>