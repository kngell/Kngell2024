# Manual SVG Sprite Creation

## Step 1: Create the sprite file structure

```xml
<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">

  <!-- Phone Icon -->
  <symbol id="phone" viewBox="0 0 24 24">
    <path d="M3 5.5C3 14.0604 9.93959 21 18.5 21C18.8862 21..." stroke="currentColor" stroke-width="2" fill="none"/>
  </symbol>

  <!-- Computer Icon -->
  <symbol id="computer" viewBox="0 0 24 24">
    <path d="M4 4h16c1.1 0 2 .9 2 2v10c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" fill="none"/>
  </symbol>

  <!-- Add more symbols here -->

</svg>
```

## Step 2: Usage in HTML

```html
<svg class="icon">
  <use href="path/to/sprite.svg#phone"></use>
</svg>
```

## Step 3: CSS (same as current)

```scss
.icon {
  filter: brightness(0) saturate(100%); // Makes icons black
}
```
