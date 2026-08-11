# Commerce Module

## Purpose

The Commerce module provides the visual language for ecommerce and retail applications.

It extends the Core module with business-specific concepts such as products, pricing, catalog management, checkout, orders, fulfillment, customers, and product identification.

Whenever possible, Commerce reuses primitives from the Core module instead of introducing duplicate concepts.

Examples:

- Search → Core
- Security → Core
- Navigation → Core
- Commerce-specific concepts → Commerce

---

# Scope

The Commerce module contains concepts related to the lifecycle of selling physical or digital products.

This includes:

- Product discovery
- Catalog organization
- Pricing
- Shopping
- Checkout
- Orders
- Fulfillment
- Customer engagement
- Product identification

General interface concepts remain part of the Core module.

---

# Module Organization

```text
Commerce/

├── Catalog
├── Products
├── Pricing
├── Checkout
├── Orders
├── Fulfillment
├── Customer
└── Identification
```

Each family represents a business domain rather than a visual style.

---

# Family Overview

## Catalog

Product discovery and merchandising.

Examples:

- Category
- Subcategory
- Collection
- Featured
- Bestseller
- New
- Product Grid
- Product List
- Compare
- Tags
- Rating
- Wishlist

---

## Products

Represents the product itself.

Examples:

- Product

This family intentionally remains small.

Additional product concepts should only be added when they represent distinct business entities.

---

## Pricing

Represents pricing information.

Examples:

- Price
- Percent
- Discount
- Sale

---

## Checkout

Represents payment and purchase completion.

Examples:

- Credit Card
- Wallet
- Coupon
- Gift Card
- Invoice
- Receipt

---

## Orders

Represents the order lifecycle.

Examples:

- Order
- Tracking
- Return
- Refund
- Exchange

---

## Fulfillment

Represents logistics and delivery.

Examples:

- Package
- Warehouse
- Shelf
- Truck
- Delivery
- Pickup
- Locker
- Shipping

---

## Customer

Represents customer services and engagement.

Examples:

- Membership
- Loyalty
- Rewards
- Recommended
- Recently Viewed
- Gift Wrap

---

## Identification

Represents product identification.

Examples:

- Barcode
- QR Code

---

# Master Primitives

The following icons are considered foundational primitives for the Commerce module.

- Product
- Package
- Price Tag
- Credit Card
- Wallet
- Truck
- Warehouse
- Store
- Barcode
- QR Code

These primitives should be reused whenever possible when creating new Commerce icons.

---

# Composition Principles

Commerce favors composition over duplication.

New icons should reuse existing primitives before introducing new geometry.

Examples:

```text
Discount

Tag
+
Percent
```

```text
Sale

Tag
+
Badge
```

```text
Shipping

Package
+
Motion Lines
```

```text
Tracking

Package
+
Location
```

```text
Gift Wrap

Package
+
Ribbon
```

The objective is to preserve a consistent visual language across the entire module.

---

# Dependency Maps

Every primitive documents the composed icons that depend on it.

Example:

```text
Package

Used By

Gift Wrap

Shipping

Tracking

Delivery

Pickup

Return

Exchange

Refund
```

Likewise, every composed icon documents the primitives from which it is built.

This creates a bidirectional dependency graph that greatly simplifies future redesigns.

---

# Core Dependencies

Commerce intentionally reuses Core concepts.

Examples include:

- Search → Product Search
- Filter → Catalog Filters
- Sort → Product Sorting
- Share → Product Sharing
- Print → Invoice Printing
- Upload → Product Import
- Download → Export Catalog
- Security → Payment Authentication
- Navigation → Store Navigation

Core remains the single source of truth for generic interface concepts.

---

# Naming Convention

Commerce icons follow the same naming rules as the Core module.

- lowercase
- kebab-case
- concept-based names

Examples:

- gift-card
- credit-card
- product-grid
- recently-viewed

Avoid implementation-specific names.

---

# Design Principles

The Commerce module follows five principles.

## Business First

Icons represent business concepts rather than interface widgets.

## Reuse Before Create

Prefer composing existing primitives instead of introducing new geometry.

## One Concept, One Icon

Each business concept should have a single canonical representation.

## Preserve Visual Consistency

Shared primitives should remain visually identical across every composition.

## Document Relationships

Every composition documents both its construction and its dependencies.

---

# Audit Status

Production Status

Nearly Complete

Architecture

Stable

Remaining production work

- discount
- sale
- shipping

These are composed icons based entirely on existing primitives and therefore do not affect the module architecture.

---

# Future Roadmap

Potential future additions include:

- Bundle
- Product Variant
- Subscription
- Tax
- Shipping Label
- Inventory Count
- Restock
- Out of Stock

New icons should be introduced only when they represent a genuinely new commerce concept that cannot be expressed through an existing primitive or composition.

---

# Long-Term Vision

The Commerce module is designed as a reusable business vocabulary for ecommerce platforms.

By documenting primitives, compositions, and dependency relationships, the module becomes more than a collection of SVG files—it becomes a maintainable visual system.

As the library grows, this documentation ensures that new icons remain consistent with existing geometry, preserve semantic clarity, and integrate naturally with the rest of the Kngell Icon Library.
