  <ul class="menu-list">
      <li class="menu-list__item">
          <a href="/admin/index" class="menu-list__item--link">
              <svg class="icon home" aria-label="Home" role="img">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-dashboard" class="image">
                  </use>
              </svg>
              <span>Dashboard</span>
          </a>
      </li>
      <li class="menu-list__item">
          <a href="/admin/contact" class="menu-list__item--link">
              <svg class="icon contact" aria-label="Contact" role="img">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-email" class="image">
                  </use>
              </svg>
              <span>Contact</span>
          </a>
      </li>
      <li class="menu-list__item">
          <button class="menu-list__item--dropdown-button">
              <svg class="icon product-icon" aria-label="Product" role="img">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-shopping-bag" class="image">
                  </use>
              </svg>
              <span>Product</span>
              <svg class="icon arrow-down rotated" aria-label="Checklist" role="img">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down" class="image">
                  </use>
              </svg>
          </button>
          <ul class="menu-list__item--dropdown-menu">
              <li role="presentation" class="wrapper">
                  <ul class="dropdown-list">
                      <li class="dropdown-list__item">
                          <a href="/admin/product-list" class="dropdown-list__item--link">
                              Product List
                          </a>
                      </li>
                      <li class="dropdown-list__item">
                          <a href="/admin/product-add" class="dropdown-list__item--link">
                              Add Product
                          </a>
                      </li>
                      <li class="dropdown-list__item">
                          <a href="/admin/product-edit" class="dropdown-list__item--link">
                              Edit Product
                          </a>
                      </li>
                      <li class="dropdown-list__item">
                          <a href="/admin/<?= $id ?? ''?>/product-show" class="dropdown-list__item--link">
                              Show Product
                          </a>
                      </li>
                  </ul>
              </li>
          </ul>
      </li>
      <li class="menu-list__item">
          <button class="menu-list__item--dropdown-button">
              <svg class="icon checklist" aria-label="Create New Folder" role="img">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-checklist" class="image">
                  </use>
              </svg>
              <span>Todo Lists</span>
              <svg class="icon arrow-down" aria-label="Arrow down" role="img">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down" class="image">
                  </use>
              </svg>
          </button>
          <ul class="menu-list__item--dropdown-menu">
              <li role="presentation" class="wrapper">
                  <ul class="dropdown-list">
                      <li class="dropdown-list__item">
                          <a href="#" class="dropdown-list__item--link">
                              Private
                          </a>
                      </li>
                      <li class="dropdown-list__item">
                          <a href="#" class="dropdown-list__item--link">
                              Work
                          </a>
                      </li>
                      <li class="dropdown-list__item">
                          <a href="#" class="dropdown-list__item--link">
                              Coding
                          </a>
                      </li>
                      <li class="dropdown-list__item">
                          <a href="#" class="dropdown-list__item--link">
                              Gardening
                          </a>
                      </li>
                      <li class="dropdown-list__item">
                          <a href="#" class="dropdown-list__item--link">
                              School
                          </a>
                      </li>
                  </ul>
              </li>
          </ul>
      </li>
      <li class="menu-list__item">
          <a href="#" class="menu-list__item--link">
              <svg class="icon calendar" aria-label="Calendar" role="img">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-calendar" class="image">
                  </use>
              </svg>
              <span>Calendar</span>
          </a>
      </li>
      <li class="menu-list__item">
          <a href="#" class="menu-list__item--link">
              <svg class="icon person" aria-label="Person" role="img">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-person" class="image">
                  </use>
              </svg>
              <span>Profile</span>
          </a>
      </li>
  </ul>