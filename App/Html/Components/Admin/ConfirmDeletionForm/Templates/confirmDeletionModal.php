<div class="modal-overlay confirm-deletion-modal {{visible}}" data-modal="confirm-deletion"
    data-cancel-url="{{cancel-route}}">
    <div class="modal confirm-deletion">

        <!-- Close button: link for no-JS, JS intercepts -->
        <a href="{{cancel-route}}" class="modal-close-btn" aria-label="Close modal" data-modal-close>
            {{icon-close}}
        </a>

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

        <!-- Body: standard form, works without JS -->
        {{confirmDeletionForm}}
    </div>
</div>