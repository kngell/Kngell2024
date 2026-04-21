<div class="title">
    <div class="title-left">
        <h4 class="title-left__text">Hero Section Manager</h4>
        <nav class="title-left__breadcrumbs">
            <ul class="breadcrumbs-list">
                <li class="breadcrumbs-list__item">
                    <a href="#" class="breadcrumbs-list__item--link">Dashboard</a>
                </li>
                <li class="breadcrumbs-list__item">
                    <a href="#" class="breadcrumbs-list__item--link active">Pages</a>
                </li>
                <li class="breadcrumbs-list__item">
                    <a href="#" class="breadcrumbs-list__item--link active">Hero Section</a>
                </li>
            </ul>
        </nav>
    </div>
    <div class="title-right">
        <button class="btn btn--outlined btn--md-compact btn--icon-left">
            <span class="btn__icon">
                <svg class="icon cancel" aria-label="Cancel" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-close"></use>
                </svg>
            </span>
            <span class="btn__label">Cancel</span>
        </button>
        <button class="btn btn--primary btn--md-compact btn--icon-left">
            <span class="btn__icon">
                <svg class="icon plus" aria-label="Plus" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-save"></use>
                </svg>
            </span>
            <span class="btn__label">Save Hero</span>
        </button>
    </div>
</div>