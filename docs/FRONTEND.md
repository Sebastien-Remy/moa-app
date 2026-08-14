# Frontend

This document describes the frontend architecture and development conventions used throughout MOA.

It complements the backend architecture documentation.

The goal is to keep the user interface simple, consistent, maintainable and independent from business logic.

---

## Philosophy

The frontend of MOA follows a few simple principles.

- Simplicity over visual effects.
- Consistency over creativity.
- Readability over complexity.
- Reuse before duplication.
- Mobile support from the beginning.
- Accessibility by default.
- Long-term maintainability.

The frontend should feel like a professional business application rather than a marketing website.

---

## Frontend Stack

MOA uses a lightweight frontend stack.

- Twig
- AssetMapper
- ImportMap
- Bootstrap
- Vanilla JavaScript (ES Modules)

Node.js is intentionally not required.

The objective is to minimize dependencies while keeping a modern frontend architecture.

---

## Asset Management

MOA uses Symfony AssetMapper as the standard asset pipeline.

Third-party frontend libraries are managed through ImportMap.

Assets are stored under:

```text
assets/
```

Application entry point:

```text
assets/app.js
```

Application styles:

```text
assets/styles/
```

---

## Bootstrap

Bootstrap provides:

- layout
- grid
- responsive utilities
- standard components

Bootstrap is not responsible for the visual identity of MOA.

The application visual identity is implemented through MOA CSS.

Bootstrap source files must never be modified.

---

## Separation of Responsibilities

One fundamental rule applies throughout the frontend:

> **HTML is not business logic.**

Responsibilities are clearly separated.

Controllers prepare data.

Services implement business rules.

Twig templates display data.

Reusable presentation belongs in Twig components.

Business decisions must never be implemented inside Twig templates.

---

## Layout

Every user-facing page inherits from:

```text
base.html.twig
```

The shared layout provides:

- header
- navigation
- content area
- footer

The layout should remain as small as possible.

Reusable sections belong in dedicated Twig partials.

---

## Responsive Design

MOA is designed for:

- desktop
- tablet
- mobile

The primary interface is desktop-oriented.

Navigation is implemented as:

- persistent sidebar on desktop
- offcanvas navigation on tablet and mobile

The same navigation structure should be reused on every device.

---

## Components

This section will describe reusable frontend components.

(To be completed during v0.7.)

---

## CSS Organization

This section will describe the CSS architecture.

(To be completed during v0.7.)

---

## Icons

This section will describe icon conventions.

(To be completed during v0.7.)

---

## Accessibility

Accessibility is considered part of the frontend architecture.

This section will define the project accessibility rules.

(To be completed during v0.7.)

---

## Performance

Frontend code should remain lightweight.

General principles include:

- avoid unnecessary JavaScript
- avoid duplicated markup
- prefer native browser features
- reuse Bootstrap utilities before writing custom CSS

---

## Design Principles

The frontend follows these design principles.

- Keep templates small.
- Prefer reusable components.
- Keep HTML semantic.
- Keep business logic outside Twig.
- Prefer Bootstrap utilities before custom CSS.
- Favor readability over cleverness.
- Build for maintainability.

## Template Organization

The frontend layout is composed of small reusable Twig partials.

The shared layout is assembled by:

```text
base.html.twig
```

Layout components are stored under:

```text
templates/layout/
```

Typical examples include:

```text
_header.html.twig
_footer.html.twig
```

The shared layout should remain small.

Whenever a section becomes reusable or grows significantly, it should be extracted into its own Twig partial.

The goal is to keep `base.html.twig` focused on assembling the application layout rather than containing large amounts of HTML.

## Navigation

MOA uses a responsive navigation system.

The navigation has a single logical structure and two visual presentations depending on the available screen size.

Desktop:

- persistent sidebar

Tablet and mobile:

- Bootstrap Offcanvas

Navigation content must be defined only once and reused across both presentations.

Navigation links are implemented in a dedicated Twig partial.

```text
templates/layout/
    _navigation.html.twig
```

The sidebar and the mobile offcanvas include this partial instead of duplicating the navigation markup.

This guarantees that both desktop and mobile navigation always remain consistent.

---

## Layout Components

The application layout is built from small reusable Twig partials.

```text
templates/layout/

_header.html.twig

_navigation.html.twig

_sidebar.html.twig

_footer.html.twig
```

Each component has a single responsibility.

| Component | Responsibility |
|-----------|----------------|
| `_header.html.twig` | Top application bar and mobile navigation trigger |
| `_navigation.html.twig` | Navigation links |
| `_sidebar.html.twig` | Desktop navigation container |
| `_footer.html.twig` | Shared application footer |

The shared layout assembles these components.

Business pages should never duplicate layout elements.

Whenever possible, reusable interface elements should become dedicated Twig partials.

## Component-Oriented Architecture

MOA frontend is built from reusable interface components rather than individual pages.

Pages assemble components.

Components encapsulate presentation.

A page should contain as little presentation logic as possible.

Typical responsibilities include:

- assembling components;
- passing data to components;
- defining page-specific content.

Reusable interface elements should become dedicated Twig components.

Examples include:

```text
_page_header.html.twig

_page_toolbar.html.twig

_data_table.html.twig

_search_bar.html.twig

_filters.html.twig

_statistics.html.twig

_empty_state.html.twig
```

Whenever a component becomes useful on more than one page, it should be extracted from the page into the shared component library.

The objective is to build a consistent interface where every page is assembled from the same set of reusable building blocks.

## Frontend Philosophy

The frontend architecture follows the same philosophy as the backend.

Repositories retrieve data.

Services implement business rules.

Controllers orchestrate.

Pages assemble components.

Components encapsulate presentation.

Each layer has a single responsibility.

## Prepared View Models

Twig templates should receive data that is already prepared for presentation.

Controllers are responsible for transforming domain objects into presentation data when necessary.

Formatting responsibilities such as:

- monetary values
- localized dates
- display labels
- formatted identifiers

should be prepared before reaching Twig whenever they involve application logic.

Twig should focus on displaying values rather than transforming them.


Prefer small composable list components over a single generic list component.

Start with anonymous components. Promote a component to a PHP component only when it requires presentation logic or state preparation.


## Component Evolution

Components are introduced when a concrete reuse opportunity has been observed.

Do not create generic components based on assumptions.

Factorize certainties.

Delay abstractions until they become obvious.

A duplicated implementation is often preferable to an abstraction introduced too early.

Components should emerge naturally from repeated usage.

Architecture
------------

Pages
Components
Layouts
Twig Components


Design System
-------------

Buttons

Tables

Forms

Spacing

Colors

Icons

Typography


Rules
------

HTML is not business logic.

Pages assemble components.

Factorize certainties.

Delay abstractions.

One responsibility per layer.


## Icons

MOA uses Font Awesome as its icon library.

The icon identifier stored in the database is the Font Awesome class.

Frontend components must render these identifiers consistently.

Do not mix multiple icon libraries.

## Document import

Document import is one of the core workflows of MOA.

The application should make importing a PDF as quick and accessible as possible.

Current implementation:
- "Import document" button.

Target implementation:
- Drag & Drop area.
- Available from the Documents page.
- Reusable component for future dashboard and global import.
- After a successful import, the user is redirected to the document edit page.

