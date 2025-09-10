<form class="search-box">
    <button type="submit" class="search-box__btn">
        <svg class="icon search">
            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search" alt="search" class="search">
            </use>
        </svg>
    </button>
    <input type="text" name="search" id="search-box--input-id" class="search-box__input" placeholder="Search...">
</form>