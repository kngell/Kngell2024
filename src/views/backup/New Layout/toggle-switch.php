<!-- Basic toggle (no label) -->
<div class="toggle-switch">
    <input type="checkbox" id="toggle-1">
    <label for="toggle-1" class="toggle">
        <span class="track"></span>
        <span class="knob"></span>
    </label>
</div>

<!-- Toggle with label -->
<div class="toggle-with-label">
    <input type="checkbox" id="toggle-2">
    <label for="toggle-2" class="toggle">
        <span class="track"></span>
        <span class="knob"></span>
    </label>
    <span class="label">Enable notifications</span>
</div>

<!-- Toggle with icons -->
<div class="icon-toggle">
    <input type="checkbox" id="toggle-3" checked>
    <label for="toggle-3" class="toggle">
        <span class="track"></span>
        <span class="knob"></span>
    </label>
</div>
<!-- // 1. Basic usage (matches your current implementation)
.toggle-switch {
  @include toggle-switch();
}

// 2. With custom colors
.primary-toggle {
  @include toggle-switch((
    "track-color-active": #3671d9,
    "shadow": 0px 2px 4px rgba(0,0,0,0.1)
  ));
}

// 3. With icons inside (like your Figma design with check/x)
.icon-toggle {
  @include toggle-switch((
    "with-icons": true,
    "icon-on": "✓",
    "icon-off": "✕",
    "icon-color": #ffffff
  ));
}

// 4. With label
.toggle-with-label {
  @include toggle-switch((
    "label-position": "right"
  ));
  
  .label {
    content: "Enable feature";
  }
}

// 5. Small variant
.small-toggle {
  @include toggle-switch((
    "width": 3.6rem,
    "height": 2rem,
    "knob-size": 1.6rem
  ));
} -->