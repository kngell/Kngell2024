# SVG Sprite Implementation Guide

## ✅ What We've Accomplished

### 1. Created SVG Sprite

- **File**: `src/assets/img/icons-sprite.svg`
- **Contains**: 16 icons with `icon-` prefix
- **Size**: Single file instead of 16 separate requests

### 2. Updated HTML Files

- ✅ `src/views/Frontend/ecommerce/index.php` - Category section
- ✅ `src/views/Layout/inc/ecommerce/header.php` - Header navigation

### 3. Updated CSS Files

- ✅ `src/assets/css/frontend/ecommerce/components/_categories.scss` - Category icons (black)
- ✅ `src/assets/css/frontend/ecommerce/layout/_header.scss` - Header icons (white)

## 🎯 Available Icons

All icons are prefixed with `icon-`:

| Icon Name          | Sprite ID             | Usage                |
| ------------------ | --------------------- | -------------------- |
| arrow-left.svg     | `icon-arrow-left`     | Navigation arrows    |
| arrow-right.svg    | `icon-arrow-right`    | Navigation arrows    |
| cameras.svg        | `icon-cameras`        | Category icon        |
| cart.svg           | `icon-cart`           | Shopping cart        |
| computers.svg      | `icon-computers`      | Category icon        |
| gaming.svg         | `icon-gaming`         | Category icon        |
| hamburger-menu.svg | `icon-hamburger-menu` | Mobile menu          |
| headphones.svg     | `icon-headphones`     | Category icon        |
| home.svg           | `icon-home`           | Home navigation      |
| logo.svg           | `icon-logo`           | Brand logo           |
| phone.svg          | `icon-phone`          | Category icon        |
| search.svg         | `icon-search`         | Search functionality |
| smart-watches.svg  | `icon-smart-watches`  | Category icon        |
| user.svg           | `icon-user`           | User account         |
| wishlist.svg       | `icon-wishlist`       | Wishlist feature     |

## 📝 How to Use

### Basic Usage

```html
<svg class="icon">
  <use href="<?= $this->asset('img/icons-sprite.svg#icon-phone') ?>"></use>
</svg>
```

### With CSS Classes

```html
<svg class="category-nav__link-icon">
  <use href="<?= $this->asset('img/icons-sprite.svg#icon-computers') ?>"></use>
</svg>
```

## 🎨 CSS Styling

### Category Icons (Black)

```scss
.icon {
  filter: brightness(0) saturate(100%); // Makes icons black
}
```

### Header Icons (White)

```scss
.category-nav__link-icon {
  filter: brightness(0) saturate(100%) invert(1); // Makes icons white
}
```

## 🔄 Adding New Icons

### Method 1: Automatic (Recommended)

1. Add new SVG files to `src/assets/img/icons/`
2. Run: `node build-sprite.js`
3. New icons will be available as `icon-filename`

### Method 2: Manual

1. Open `src/assets/img/icons-sprite.svg`
2. Add new symbol:

```xml
<symbol id="icon-new-icon" viewBox="0 0 24 24">
  <!-- SVG content here -->
</symbol>
```

## 🚀 Performance Benefits

### Before (Individual Files)

- 16 HTTP requests
- ~12KB total (estimated)
- Slower loading
- No caching benefits

### After (Sprite)

- 1 HTTP request
- ~8KB total
- Faster loading
- Better caching
- Easier maintenance

## 🔧 Build Script

The `build-sprite.js` script automatically:

- Scans `src/assets/img/icons/` for SVG files
- Extracts viewBox and content
- Creates symbols with `icon-` prefix
- Outputs to `src/assets/img/icons-sprite.svg`

### Run the script:

```bash
node build-sprite.js
```

## 📋 Next Steps

1. **Test the implementation** - Check that all icons display correctly
2. **Update other files** - Replace any remaining `<img>` tags with sprite usage
3. **Set up automation** - Add the build script to your build process
4. **Monitor performance** - Verify the performance improvements

## 🐛 Troubleshooting

### Icons not showing?

- Check the sprite file path in `href` attribute
- Verify the icon ID exists in the sprite
- Ensure CSS filters are applied correctly

### Wrong colors?

- Category icons: Use `filter: brightness(0) saturate(100%)`
- Header icons: Use `filter: brightness(0) saturate(100%) invert(1)`

### Need different colors?

- Use `filter: hue-rotate(XXdeg)` for color variations
- Or switch to inline SVG for full control
