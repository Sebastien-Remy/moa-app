# Roadmap
# v0.8 — Document Analysis & Browsing

**Status:** Planning

## Goal

Improve the daily document workflow by making the document list scalable and searchable, and introduce the first analytical allocation of document amounts.

This version focuses on two complementary areas:

- navigating and analysing the document collection;
- allocating document amounts to categories and projects.

## Scope

### Document List

Improve the existing user-facing document list so that it remains practical as the number of documents grows.

Planned features:

- Paginated document list.
- Search by relevant document information.
- First document filters.
- Preserve filters and search while navigating between result pages.
- Display the number of matching documents.
- Add a summary area below the list.
- Display the cumulative amount of matching documents.
- Keep monetary aggregation consistent with the current single-currency model.

The existing `DataTable` component should be extended only when concrete reusable needs emerge.

Pagination, filters, search and statistics should remain composable concerns rather than being embedded into one oversized generic table component.

### Document Analysis

Introduce the first user-facing analytical allocation workflow.

A document amount can be distributed across one or more analysis lines.

Each allocation may reference:

- a category;
- a project;
- an amount;
- optional notes.

The sum of the allocations should be comparable to the document amount.

The interface should make it easy to understand:

- the document total;
- the amount already allocated;
- the amount remaining to allocate.

### Categories

Use the existing hierarchical `Category` model.

Categories represent the nature of income or expense.

Examples:

- Revenue
- Purchases
- Travel
- Software
- Bank fees

Category hierarchy remains available for future consolidated reporting.

### Projects

Use the existing hierarchical `Project` model as the first analytical axis.

Projects represent where income or expenses should be attributed.

Examples:

- MOA
- Television production
- Internal administration
- Client project

The model should remain compatible with future analytical dimensions without attempting to expose every possible axis in v0.8.

### Document Edit

Extend the current document edit workflow with an analysis section.

The existing document metadata and PDF preview remain unchanged.

The analysis area should allow the user to:

- view existing allocations;
- add an allocation;
- edit an allocation;
- remove an allocation;
- see allocated and remaining amounts.

Do not turn the document form into one large monolithic form if a dedicated analysis component or workflow provides a clearer interface.

## Architecture

Reuse the existing financial core introduced before v0.7.

Do not create parallel entities or services when the existing `Analysis`, `Category` and `Project` domain can be extended.

Business rules belong in services and domain validation, not in Twig.

Document list queries, filtering, pagination and aggregation should be handled by repositories or dedicated query services rather than Twig.

Monetary totals must use the existing money conventions and services.

## Completion Criteria

v0.8 can be considered complete when:

- The document list is paginated.
- Documents can be searched from the frontend.
- At least the first useful document filters are available.
- Search and filters work together with pagination.
- The list displays the number of matching documents.
- The list displays the cumulative amount of matching documents.
- A document can contain analytical allocations.
- Allocations can reference a category and a project.
- Allocations can be added, edited and removed from the frontend.
- Allocated and remaining amounts are clearly visible.
- Business rules prevent inconsistent analytical allocations.
- Existing document import, view and edit workflows remain functional.
- EasyAdmin remains compatible with the underlying domain.