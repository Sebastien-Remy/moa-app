# Architecture Roadmap

## Multi-Workspace Foundation

**Status:** Planned

### Goal

Prepare MOA for a future SaaS offering while keeping self-hosted installations simple.

The objective is to establish a clear security boundary between organizations from the beginning, without implementing the complete SaaS platform yet.

---

### Principles

Every business entity belongs to exactly one `Workspace`.

The Workspace is the security boundary of the application.

Business data must never exist outside a Workspace.

Self-hosted installations will contain a single default Workspace.

Future SaaS deployments will create one Workspace per customer.

Examples:

- `demo.moa-app.fr`
- `gorilladev.moa-app.fr`
- `mycompany.moa-app.fr`

---

### Initial Scope

Introduce a new root entity:

- `Workspace`

Every business entity will eventually belong to a Workspace, including:

- Users
- Documents
- Folders
- Tags
- Categories
- Projects
- Third Parties
- Bank Accounts
- Analysis
- Settlement Entries

The Workspace context should be resolved once during the request lifecycle and automatically applied by repositories and services.

Controllers and Twig templates should never be responsible for filtering data by Workspace.

---

### Non Goals

This milestone does **not** implement:

- SaaS subscriptions;
- customer billing;
- workspace creation UI;
- subdomain provisioning;
- multi-company management;
- workspace switching.

Those features belong to future releases.

---

### Completion Criteria

This architectural milestone can be considered complete when:

- A `Workspace` entity exists.
- Existing data belongs to a Workspace.
- The Workspace is resolved automatically for every request.
- Repository queries are Workspace-aware.
- A self-hosted installation continues to behave exactly as before using a single default Workspace.
- 
# Roadmap
# v0.8 — Third Party Settlement Engine

**Status:** Planning

## Goal

Introduce the settlement engine that connects documents, third parties and bank transactions.

This version establishes the financial workflow that allows MOA to answer one fundamental question:

> **Who owes money to whom?**

Documents generate receivables or payables for one or more third parties.

Bank transactions settle these receivables and payables.

This version introduces the settlement domain without implementing a full double-entry accounting system.

---

## Scope

### Third Party Settlement

Introduce a new settlement layer independent from document analysis.

Create a new entity:

- `ThirdPartyEntry`

Each entry represents a receivable or payable towards a single third party.

A settlement entry may originate from:

- a document;
- a bank transaction.

A settlement entry must never originate from both simultaneously.

---

### Multiple Third Parties per Document

A single document may generate multiple receivables or payables.

This makes it possible to model documents such as:

- payroll summaries;
- expense reports;
- tax declarations;
- grouped invoices.

Examples:

```text
Payroll Summary

→ Employee A
→ Employee B
→ URSSAF
→ Audiens
→ AFDAS
→ Tax Administration
```

```text
Expense Report

Restaurant receipt
→ Employee

Taxi receipt
→ Employee

Hotel invoice
→ Employee
```

```text
Supplier Invoice

Supplier invoice
→ Supplier
```

The document remains a single business document while settlement is performed independently for every concerned third party.

---

### Third Party Balance

The balance of every third party is calculated from settlement entries.

Examples:

```text
Supplier

Invoice      -500 €
Payment      +500 €

Balance         0 €
```

```text
Employee

Expense claim   -200 €
Reimbursement   +200 €

Balance           0 €
```

Future versions may expose these balances through dedicated dashboards and reports.

---

### Settlement from Bank Transactions

Bank transactions also create settlement entries.

This allows settlement to become independent from analytical allocation.

A payment may settle:

- one document;
- multiple documents;
- part of a document;
- multiple third parties.

The existing `DocumentTransaction` reconciliation mechanism remains available.

Settlement and document reconciliation represent two different business concepts.

---

### Relationship with Analysis

Analysis answers:

> **Where does the money belong?**

Settlement answers:

> **Who should receive or pay the money?**

These two domains remain completely independent.

Examples:

```text
Supplier Invoice

Settlement
→ Supplier

Analysis
→ Purchases
→ Project MOA
```

```text
Expense Receipt

Settlement
→ Employee

Analysis
→ Travel
→ Client Project
```

---

## Architecture

Introduce the settlement domain without transforming MOA into a complete accounting application.

Reuse the existing `ThirdParty` entity.

Avoid introducing accounting journals, debit/credit entries or general ledger accounts.

The settlement layer must remain lightweight while supporting:

- supplier invoices;
- customer invoices;
- expense reports;
- payroll summaries;
- tax and social declarations;
- partial payments;
- grouped payments.

Business rules belong in dedicated services rather than controllers or Twig templates.

---

## Completion Criteria

v0.8 can be considered complete when:

- `ThirdPartyEntry` has been implemented.
- A document can generate multiple settlement entries.
- A bank transaction can generate settlement entries.
- Third party balances can be calculated.
- Settlement and document reconciliation remain independent.
- Existing `DocumentTransaction` reconciliation continues to work.
- Analysis continues to work independently from settlement.
- Existing document workflows remain compatible.
- EasyAdmin supports the new settlement entities.

---

# v0.9 — Document Analysis & Browsing

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