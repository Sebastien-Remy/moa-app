# Frontend

This document defines the frontend architecture, conventions and design principles used throughout MOA.

It complements the backend architecture documentation and serves as the reference for every user-facing interface.

The objective is to build a frontend that is simple, consistent, maintainable and independent from business logic.

---

# Frontend Philosophy

The frontend follows a small number of principles that should guide every implementation.

- Simplicity over visual effects.
- Consistency over creativity.
- Readability over complexity.
- Reuse before duplication.
- Accessibility by default.
- Mobile support from the beginning.
- Long-term maintainability.

The interface should feel like a professional business application rather than a marketing website.

Whenever possible:

- HTML displays data.
- Controllers orchestrate.
- Services implement business rules.
- Components encapsulate presentation.

Business logic never belongs inside Twig templates.

---

# Frontend Stack

MOA intentionally keeps its frontend lightweight.

Current stack:

- Twig
- Symfony UX Twig Components
- Bootstrap
- Font Awesome
- AssetMapper
- ImportMap
- Vanilla JavaScript (ES Modules)

Node.js is intentionally not required.

The goal is to minimize dependencies while keeping a modern frontend architecture.

---

# Asset Management

MOA uses Symfony AssetMapper.

Application assets are stored under:

```text
assets/
```

Application entry point:

```text
assets/app.js
```

Global stylesheet:

```text
assets/app.css
```

Third-party frontend libraries are managed through ImportMap.

Bootstrap source files must never be modified.

---

# Frontend Architecture

The frontend follows the same architecture philosophy as the backend.

```text
Repositories
        │
        ▼
Services
        │
        ▼
Controllers
        │
        ▼
Pages
        │
        ▼
Components
```

Responsibilities:

| Layer | Responsibility |
|--------|----------------|
| Repository | Retrieve data |
| Service | Business logic |
| Controller | Orchestrate |
| Page | Assemble components |
| Component | Encapsulate presentation |

Each layer has one responsibility.

---

# Layout

Every authenticated page inherits from:

```text
base.html.twig
```

The application layout is assembled from reusable layout partials.

```text
templates/layout/

_header.html.twig
_sidebar.html.twig
_navigation.html.twig
_footer.html.twig
```

Responsibilities:

| Component | Responsibility |
|-----------|----------------|
| Header | Top application bar |
| Sidebar | Desktop navigation container |
| Navigation | Navigation links |
| Footer | Shared footer |

The shared layout should remain as small as possible.

Whenever a section becomes reusable it should be extracted into its own partial.

---

# Navigation

MOA uses a single logical navigation.

Desktop:

- Persistent sidebar

Tablet / Mobile:

- Bootstrap Offcanvas

Navigation links are defined only once:

```text
templates/layout/_navigation.html.twig
```

Desktop sidebar and mobile navigation both reuse this partial.

The application navigation is only displayed for authenticated users.

---

# Component-Oriented Architecture

Pages assemble components.

Components encapsulate presentation.

Pages should contain as little presentation logic as possible.

Current reusable components include:

- PageHeader
- FlashMessages
- DataTable
- TableActions
- ActionButton
- ActionForm
- PdfPreview
- DocumentDropZone

A component should only be introduced after a real reuse opportunity has been observed.

Factorize certainties.

Delay abstractions.

---

# Prepared View Models

Twig templates should receive presentation-ready data.

Controllers prepare presentation data when necessary.

Typical responsibilities include:

- formatted monetary values
- localized dates
- display labels
- formatted identifiers

Twig should display values rather than transform them.

---

# Design System

The frontend should be built from reusable interface building blocks.

Current design system includes:

## Forms

Forms share a common visual language.

Reusable building blocks include:

- form-shell
- form-card
- form-actions

Avoid page-specific form CSS whenever shared components or Bootstrap utilities can express the same layout.

## Tables

Document lists use reusable DataTable components.

Tables should remain visually consistent throughout the application.

## Buttons

Bootstrap buttons are used throughout the application.

Prefer Bootstrap utilities over custom button implementations.

## Cards

Cards are used to group related content.

Cards should remain lightweight and consistent.

## Drop Zones

Document import is implemented using a reusable DocumentDropZone component.

Future upload workflows should reuse this component.

## Flash Messages

User notifications are displayed through the shared FlashMessages component.

## Icons

MOA uses Font Awesome.

Only one icon library should be used throughout the application.

Icon identifiers stored in the database are Font Awesome class names.

---

# CSS Organization

CSS is organized by reusable interface concepts rather than pages.

Current structure:

```text
app.css

Layout

Forms

Tables

Cards

Buttons

Drop Zones

Utilities
```

Avoid page-specific stylesheets such as:

```text
login.css
document.css
folder.css
```

The objective is to build reusable interface elements rather than page-specific layouts.

---

# Responsive Design

The application is desktop-first.

Supported devices:

- Desktop
- Tablet
- Mobile

Desktop uses a persistent sidebar.

Tablet and mobile use Bootstrap Offcanvas.

The same navigation structure should be reused on every device.

---

# Accessibility

Accessibility is considered part of the frontend architecture.

General rules include:

- semantic HTML
- accessible labels
- keyboard navigation
- sufficient color contrast
- Bootstrap accessibility conventions

Accessibility should never be considered an optional feature.

---

# Performance

Frontend code should remain lightweight.

General principles include:

- avoid unnecessary JavaScript
- prefer native browser features
- reuse Bootstrap utilities
- avoid duplicated HTML
- avoid unnecessary dependencies

---

# Document Import

Document import is one of the core workflows of MOA.

The workflow should be as simple as possible.

Current workflow:

```text
Documents

↓

Import

↓

Choose or Drop PDF

↓

Create Document

↓

Edit Metadata

↓

Return to Documents
```

The current implementation provides:

- dedicated import page
- drag & drop support
- classic file picker
- shared storage pipeline
- automatic document creation
- automatic document file creation
- redirection to document editing
- return to document list after saving

Future improvements should reuse the existing DocumentDropZone component.

---

# General Rules

Always remember:

- HTML is not business logic.
- Controllers orchestrate.
- Pages assemble components.
- Components encapsulate presentation.
- Factorize certainties.
- Delay abstractions.
- Prefer Bootstrap utilities before custom CSS.
- One responsibility per layer.
- Build for maintainability.