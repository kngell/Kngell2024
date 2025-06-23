# SVG Sprite Management Guide

## Method 1: Automatic Rebuild (Recommended)

### Adding New Icons

```bash
# 1. Add new SVG file(s) to the icons directory
cp new-icon.svg src/assets/img/icons/

# 2. Rebuild the sprite
node build-sprite.js

# Result: New sprite with all icons (old + new)
```

### Removing Icons

```bash
# 1. Remove SVG file(s) from the icons directory
rm src/assets/img/icons/example-icon.svg

# 2. Rebuild the sprite
node build-sprite.js

# Result: New sprite without the removed icons
```

### Updating Existing Icons

```bash
# 1. Replace the SVG file with updated version
cp updated-phone.svg src/assets/img/icons/phone.svg

# 2. Rebuild the sprite
node build-sprite.js

# Result: Sprite with updated icon
```

## Method 2: Manual Editing (Quick Changes)

### Adding a Single Icon Manually

1. Open `src/assets/img/icons-sprite.svg`
2. Add new symbol before closing `</svg>`:

```xml
<!-- new-icon -->
<symbol id="icon-new-icon" viewBox="0 0 24 24">
  <path d="..." stroke="currentColor" fill="none"/>
</symbol>
```

### Removing a Single Icon Manually

1. Open `src/assets/img/icons-sprite.svg`
2. Find and delete the symbol:

```xml
<!-- Remove this entire block -->
<symbol id="icon-example-icon" viewBox="0 0 24 24">
  <!-- content -->
</symbol>
```

### Updating Icon Content

1. Open `src/assets/img/icons-sprite.svg`
2. Find the symbol and update its content:

```xml
<symbol id="icon-phone" viewBox="0 0 24 24">
  <!-- Replace old path with new one -->
  <path d="NEW_PATH_DATA" stroke="currentColor"/>
</symbol>
```

## Method 3: Watch Mode (Development)

For active development, you can set up automatic rebuilding:

### Option A: Using chokidar (if installed)

```bash
npm install --save-dev chokidar-cli
npx chokidar "src/assets/img/icons/*.svg" -c "node build-sprite.js"
```

### Option B: Simple bash watch

```bash
# Create a watch script
echo '#!/bin/bash
while inotifywait -e modify,create,delete src/assets/img/icons/; do
  node build-sprite.js
done' > watch-sprite.sh

chmod +x watch-sprite.sh
./watch-sprite.sh
```

## Performance Comparison

| Method       | Speed   | Safety | Automation |
| ------------ | ------- | ------ | ---------- |
| Auto Rebuild | Medium  | High   | Full       |
| Manual Edit  | Fast    | Medium | None       |
| Watch Mode   | Instant | High   | Full       |

## Best Practices

### For Development

- Use **watch mode** for active development
- Use **auto rebuild** for occasional changes

### For Production

- Always use **auto rebuild** before deployment
- Verify sprite integrity after manual edits
- Keep original SVG files as source of truth

### File Organization

```
src/assets/img/
├── icons/              # Source SVG files
│   ├── phone.svg
│   ├── computer.svg
│   └── ...
├── icons-sprite.svg    # Generated sprite
└── icons-backup/       # Optional: backup of individual files
```
