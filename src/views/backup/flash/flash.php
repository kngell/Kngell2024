<div class="flash-container" aria-live="polite" aria-atomic="true">
    <div class="flash flash--danger flash-message-js" role="alert" data-flash-duration="5000">
        <svg class="flash__icon" aria-hidden="true" role="img">
            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-error"></use>
        </svg>

        <div class="flash__body">
            <span class="flash__text flash-message-js__text">
                This category can't be deleted because it's still in use by other records.
            </span>
        </div>

        <button type="button" class="flash__close" aria-label="Close notification" data-flash-dismiss="true">
            <svg class="flash__close-icon" aria-hidden="true" role="img">
                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-close"></use>
            </svg>
        </button>

        <!-- Optional: auto-dismiss progress bar -->
        <span class="flash__progress" aria-hidden="true"></span>
    </div>
</div>
<!-- Static error in a table: -->
<div class="flash-container">
    <div class="flash flash--danger" role="alert"> ... </div>
</div>
<!-- Auto-dismissing success toast: -->
<div class="flash-container flash-container--toast">
    <div class="flash flash--success" role="status" data-flash-duration="4000">
        ...
        <span class="flash__progress" aria-hidden="true"></span>
    </div>
</div>
<!-- Persistent warning (no progress bar, no auto-dismiss): -->
<div class="flash flash--warning" role="alert">
    <!-- no data-flash-duration, no .flash__progress -->
</div>
<div class="flash-container flash-container--toast" aria-live="polite" aria-atomic="true">
    <!-- flash messages here -->
</div>