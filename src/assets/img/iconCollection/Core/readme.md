# Core Module

## Purpose

The Core module provides the universal building blocks used throughout the Kngell Icon Library.

Unlike domain-specific modules (Commerce, Users, Analytics, etc.), Core contains concepts that are common to almost every application regardless of industry.

Examples include actions, navigation, files, search, security, visibility, layout, and status.

Every other module should reuse Core whenever possible before introducing new concepts.

---

# Scope

The Core module contains only generic UI concepts.

It intentionally excludes business-specific icons such as products, orders, invoices, warehouses, customers, or payments, which belong to dedicated modules.

Core is therefore the visual foundation of the entire library.

---

# Module Organization

```text
Core/

├── Actions
├── Navigation
├── Status
├── Files
├── History & Synchronization
├── Search
├── Geography
├── Selection
├── Organization
├── Layout & View
├── Time
├── Security
├── Visibility
└── Menus
```

Each family groups concepts rather than visual appearance.

---

# Family Overview

## Actions

Represents operations initiated by the user.

Examples:

- Save
- Edit
- Delete
- Copy
- Upload
- Download
- Print
- Share

---

## Navigation

Represents movement through an interface.

Examples:

- Arrows
- Chevrons
- Back
- Forward
- Home
- Expand
- Collapse
- External Link

---

## Status

Represents application state and user feedback.

Examples:

- Success
- Error
- Warning
- Information
- Notification

---

## Files

Represents generic documents and file-related objects.

Examples:

- Document
- Folder
- Clipboard
- Archive
- Report
- Attachment

---

## History & Synchronization

Represents state changes over time.

Examples:

- Undo
- Redo
- Refresh
- Reload
- Synchronization
- History

---

## Search

Represents discovery and result manipulation.

Examples:

- Search
- Filter
- Sort
- Zoom

---

## Geography

Represents physical locations.

Examples:

- Map
- Pin
- Globe
- Route
- Location

---

## Selection

Represents selectable UI controls.

Examples:

- Checkbox
- Radio Button
- Select All
- Select None

---

## Organization

Represents structural relationships.

Examples:

- Group
- Ungroup
- Link
- Unlink
- Drag

---

## Layout & View

Represents interface organization.

Examples:

- Grid
- List
- Columns
- Sidebar

---

## Time

Represents temporal concepts.

Examples:

- Calendar
- Clock
- Timer

---

## Security

Represents authentication and protection.

Examples:

- Lock
- Unlock
- Shield
- Key

---

## Visibility

Represents visibility states.

Examples:

- Eye
- Eye Off

---

## Menus

Represents access to additional commands.

Examples:

- More Horizontal
- More Vertical

---

# Primitive Icons

Primitive icons are the original visual building blocks of the module.

Examples include:

- document
- folder
- shield
- map
- calendar
- eye
- checkbox
- grid

Whenever possible, new icons should reuse these primitives instead of introducing new geometry.

---

# Composed Icons

Core intentionally contains very few composed icons.

Most compositions are implemented inside domain modules.

Examples:

- check-circle
- checkbox-checked
- checkbox-indeterminate
- radio-selected

---

# Variants

Variants preserve the geometry of a primitive while changing its state.

Examples:

- eye / eye-off
- edit / edit-off
- checkbox / checkbox-checked
- star / star-filled (Commerce)
- heart / heart-filled (Commerce)

---

# Design Principles

The Core module follows five fundamental principles.

## 1. Concept First

Icons are organized by meaning rather than appearance.

## 2. Reuse Geometry

Existing primitives should be reused whenever possible.

## 3. Build Through Composition

Prefer composing existing icons over drawing unrelated ones.

## 4. Consistent Visual Language

Stroke width, proportions, spacing, radii, and alignment remain consistent across all families.

## 5. Minimal Duplication

Each concept exists only once within the library.

---

# Naming Convention

All filenames use lowercase kebab-case.

Examples:

- external-link.svg
- search-off.svg
- check-circle.svg

Names describe concepts rather than implementation.

---

# Dependency Philosophy

Primitive icons document every composed icon that depends on them.

Likewise, composed icons document every primitive they reuse.

This bidirectional mapping makes future redesigns predictable and significantly reduces maintenance effort.

---

# Relationship with Other Modules

Core provides the shared vocabulary used by every other module.

Commerce, Users, Communication, Finance, Analytics, and future modules should reference Core primitives whenever appropriate instead of recreating equivalent concepts.

---

# Audit Status

Production Status:

✅ Frozen

Families:

14

Architecture:

Stable

Remaining work:

Documentation refinements only.

Future additions will be accepted only if they introduce a genuinely new generic UI concept without duplicating an existing icon.

---

# Future Roadmap

Potential future additions include:

- loading
- verified
- compass
- panel
- rows

These icons are intentionally deferred until a concrete use case requires them.

This conservative approach keeps the Core module focused, coherent, and easy to maintain while providing a stable foundation for every other module in the Kngell Icon Library.
