# Roadmap
# v0.7 — Front Foundation

**Status:** In Progress

## Goal

Introduce the first user-facing interface for MOA outside EasyAdmin.

This version establishes the HTML, Twig, component and CSS foundations of the application and provides the first complete user-facing document workflow.

## Completion Criteria

v0.7 can be considered complete when:

- A shared Twig layout exists.
- Header, navigation, sidebar and footer are implemented.
- The layout is responsive on desktop, tablet and mobile.
- Bootstrap is integrated through AssetMapper / ImportMap.
- Font Awesome is integrated as the standard icon library.
- Front-end architecture and conventions are documented in `FRONTEND.md`.
- Layout partials and reusable UI components follow a documented convention.
- Symfony UX Twig Components are integrated.
- A reusable `PageHeader` component exists.
- A reusable `DataTable` component exists.
- Reusable table action components exist.
- A reusable PDF preview component exists.
- Flash messages are handled consistently across the frontend.
- A user-facing home page exists.
- A user-facing document list exists.
- Document view, edit and delete workflows exist.
- PDF files can be previewed inline from the user-facing interface.
- Document pages provide consistent breadcrumb navigation.
- Monetary values are formatted outside Twig.
- Business rule exceptions are handled consistently at the interface boundary.
- Document import is available from the user-facing interface.
- Import creates a minimal document and redirects the user to the edit workflow.
- The import workflow reuses the existing document storage pipeline.
- The interface works correctly on desktop and mobile.