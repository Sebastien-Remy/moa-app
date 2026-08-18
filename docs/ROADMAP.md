## v0.8 — Financial Analysis Workflow

**Status:** In Progress

## Goal

Make MOA genuinely usable for everyday business document management by introducing the first complete financial qualification workflow.

This version focuses on qualifying documents rather than implementing a complete accounting system.

Each document should answer four fundamental questions:

- **Who** is the primary third party associated with this document? (`ThirdParty`)
- **What** does the money correspond to? (`Category`)
- **Where** should the money be allocated? (`Analysis Dimension Values`)
- **Who** owes money to whom? (`ThirdPartyEntry`)

Financial analysis and third-party positions are intentionally modeled as two independent domains.

The objective is to provide meaningful financial reporting without introducing the complexity of double-entry accounting or bank reconciliation.

---

## Scope

### Financial Analysis

Build upon the existing `Analysis` entity.

Each analysis represents an allocation of part (or all) of a document amount.

An analysis contains:

- analysis date;
- category;
- one value for each active analysis dimension;
- signed amount;
- currency;
- optional notes.

The analysis date is independent from the document issue date.

This allows a single document to allocate amounts across multiple accounting periods.

Example:

```text
Train tickets invoice

August travel      -120 €
September travel   -180 €
```

Analysis answers:

> **What does this amount correspond to?**

and

> **Where should it be allocated?**

---

### Analysis Dimensions

Reuse the existing analytical model:

- `AnalysisDimension`
- `AnalysisDimensionValue`
- `AnalysisDimensionAssignment`

Active analysis dimensions are automatically displayed in the analysis form.

Each analysis may reference one value for each active dimension.

Example:

```text
Analysis

Category      Travel
Project       MOA
Department    Television
Amount        -350 €
```

Adding a new analysis dimension from EasyAdmin must automatically expose it in the frontend without requiring application code changes.

`AnalysisDimensionAssignment` remains an implementation detail and is never exposed directly to end users.

---

### Third Party Entries

Introduce a new entity:

- `ThirdPartyEntry`

Third-party entries are completely independent from analyses.

An analysis answers:

> **Where does the money belong?**

A third-party entry answers:

> **Who owes money to whom?**

Each entry contains:

- entry date;
- third party;
- signed amount;
- currency;
- optional notes;
- origin document or origin bank transaction.

Each entry must originate from exactly one source:

```text
Document XOR BankTransaction
```

Bank transactions are intentionally excluded from the v0.8 workflow but the data model should already support them.

---

### Multiple Third Parties per Document

The existing `Document.thirdParty` remains the primary third party associated with the document.

It represents document metadata only.

A document may generate multiple `ThirdPartyEntry` records.

Example:

```text
Payroll summary

Primary third party
Payroll provider

Third-party entries

Employee A      -2 000 €
Employee B      -2 100 €
URSSAF          -1 800 €
Audiens           -700 €
```

Another example:

```text
Expense report

Primary third party
Employee

Third-party entries

Employee          -245 €
```

This separates document metadata from financial positions.

---

### Third Party Position

The financial position of a third party is calculated exclusively from `ThirdPartyEntry`.

It is never calculated from analyses.

Sign convention:

```text
Positive amount

The third party owes money to us.

Negative amount

We owe money to the third party.
```

Example:

```text
Customer invoice

Customer        +5 000 €

Receivable      +5 000 €
```

```text
Supplier invoice

Supplier          -500 €

Payable           -500 €
```

Future bank transaction entries will allow positions to be settled:

```text
Supplier invoice      -500 €
Bank payment          +500 €
----------------------------
Current position         0 €
```

Settlement itself is outside the scope of v0.8.

---

### Document Analysis Workflow

Extend the existing document edit screen with a dedicated financial analysis section.

Users should be able to:

- view analyses;
- create analyses;
- edit analyses;
- delete analyses;
- assign categories;
- assign analysis dimensions.

The interface should later expose:

- document amount;
- allocated amount;
- remaining amount;
- allocation percentage.

These indicators are informational only.

Analysis amounts are intentionally **not** limited by the document amount.

---

### Document Third Party Workflow

Extend the document edit screen with a dedicated third-party position section.

Users should be able to:

- view third-party entries;
- create third-party entries;
- edit third-party entries;
- delete third-party entries;
- assign a third party;
- assign an entry date;
- assign a signed amount.

The document primary third party and its financial entries remain independent concepts.

---

### Document Browsing

Improve the document list for daily use.

Introduce:

- pagination;
- search;
- useful filters;
- persistent filters;
- number of matching documents;
- cumulative displayed amount.

The footer should display aggregated totals for the current result set.

---

### Category Summary

Introduce the first analytical dashboard for categories.

Display:

- allocated amount;
- number of related documents.

Example:

```text
Hosting             -520 €
Travel            -1 240 €
Payroll          -25 600 €
Revenue          +18 500 €
```

No drill-down is required.

---

### Analysis Dimension Summary

Introduce summary pages for every analysis dimension.

Display the cumulative amount allocated to every dimension value.

Example:

```text
Project

MOA              -4 200 €
Internal           -980 €
Client A        +8 150 €
```

Every future analysis dimension must automatically be supported.

---

### Third Party Summary

Introduce a summary page for third-party financial positions.

Display:

- current position;
- number of related documents.

Example:

```text
EDF             -1 250 €
Amazon            -890 €
URSSAF         -12 450 €
Client A       +8 500 €
```

The summary is calculated exclusively from `ThirdPartyEntry`.

---

## Architecture

Reuse the existing financial domain as much as possible.

Continue relying on:

- `Document`
- `Analysis`
- `Category`
- `AnalysisDimension`
- `AnalysisDimensionValue`
- `AnalysisDimensionAssignment`
- `ThirdParty`

Introduce:

- `ThirdPartyEntry`

The resulting model becomes:

```text
Document
├── ThirdParty
│   └── Primary document third party
│
├── Analysis[]
│   ├── AnalysisDate
│   ├── Category
│   ├── Amount
│   ├── Currency
│   └── AnalysisDimensionAssignment[]
│       └── AnalysisDimensionValue
│
└── ThirdPartyEntry[]
    ├── ThirdParty
    ├── EntryDate
    ├── Amount
    └── Currency
```

Future versions will extend the model with:

```text
BankTransaction
└── ThirdPartyEntry[]
```

while preserving the invariant:

```text
ThirdPartyEntry
Document XOR BankTransaction
```

Business rules belong in services.

Repositories provide aggregated data.

Twig remains responsible only for presentation.

---

## Non Goals

This version deliberately excludes:

- double-entry accounting;
- chart of accounts;
- accounting journals;
- debit / credit entries;
- general ledger;
- bank reconciliation;
- payment matching;
- payment tracking;
- settlement workflow;
- currency conversion.

These features belong to future releases.

---

## Completion Criteria

v0.8 can be considered complete when:

- analyses support their own accounting date;
- analyses support their own currency;
- a document supports multiple analyses;
- analyses support dynamic analysis dimensions;
- dynamic dimensions automatically appear in the frontend;
- analyses can be created, edited and deleted;
- a document supports multiple third-party entries;
- third-party entries support date, amount, currency and third party;
- third-party entries support the `Document XOR BankTransaction` model;
- third-party positions are calculated independently from analyses;
- document editing provides complete analysis and third-party workflows;
- the document list supports pagination, search and filtering;
- cumulative totals are displayed in the document list;
- category summaries display analytical totals;
- analysis dimension summaries display analytical totals;
- third-party summaries display financial positions;
- existing document workflows remain fully compatible;
- EasyAdmin remains compatible with the updated domain model.
---

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

---

# Roadmap
# v0.9 — Third Party Settlement Engine

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