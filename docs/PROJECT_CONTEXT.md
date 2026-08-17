# PROJECT_CONTEXT.md

> This document contains the long-term context of the MOA project.
>
> Every AI assistant contributing to MOA should read this file before making architectural or significant code changes.
>
> The objective is to preserve architectural consistency across conversations, contributors and AI models.

---

# Project Vision

MOA (My Office Assistant) is an open-source financial document management platform.

The objective is **not** to become a complete ERP or accounting application.

MOA focuses on the workflow between documents, banking and financial analysis.

Typical users include:

- freelancers;
- small businesses;
- accountants;
- associations;
- production companies.

The application should remain simple, explicit and understandable.

Complexity must only be introduced when it clearly solves a real business problem.

---

# Long-Term Goals

MOA aims to become a platform able to manage:

- business documents;
- bank transactions;
- document reconciliation;
- analytical accounting;
- financial reporting;
- budgeting;
- cash flow analysis;
- electronic invoicing;
- document sharing.

The project intentionally stops before full double-entry accounting.

Accounting exports may be produced later.

---

# Technical Stack

Framework:

- Symfony 8

Database:

- PostgreSQL

Administration:

- EasyAdmin

Identifiers:

- Symfony ULID

Language:

- Source code in English.
- User interface initially in English.
- Documentation written in English.

Comments are written in English.

---

# Architectural Principles

## Layered Architecture

```text
Interface
    │
Application
    │
Domain
    │
Infrastructure
```

Business rules belong to the Domain.

Controllers remain thin.

Repositories only access data.

Services implement business workflows.

---

# Core Domain Model

The financial model is intentionally separated into independent domains.

## Document

Represents a business document.

Examples:

- supplier invoice;
- customer invoice;
- receipt;
- payroll summary;
- expense receipt;
- contract.

A document represents evidence.

It does not represent accounting entries.

---

## Analysis

Analysis answers:

> Where does the money belong?

Analysis allocates money to:

- categories;
- projects;
- future analytical dimensions.

Analysis is completely independent from settlement.

---

## Settlement

Settlement answers:

> Who owes money to whom?

Settlement represents receivables and payables involving third parties.

Settlement is independent from Analysis.

Settlement is intentionally simpler than accounting.

The future model introduces:

```text
ThirdPartyEntry
```

A document may create several settlement entries.

Example:

Payroll

- Employee A
- Employee B
- URSSAF
- Audiens
- AFDAS

---

## BankTransaction

Represents an actual movement on a bank account.

Bank transactions are independent from documents.

---

## Document Reconciliation

Document reconciliation links documents and bank transactions.

Settlement and reconciliation are two different concepts.

---

# Money

Money is always stored as integer minor units.

Examples:

```text
10.50 €
↓

1050
```

Floating point values must never be persisted.

---

# Storage

Business metadata and physical storage remain independent.

```text
Document
    │
DocumentFile
    │
StoredFile
```

Stored files are deduplicated using SHA-256.

---

# Workspace

MOA must support both self-hosted and future SaaS deployments.

Every business entity belongs to exactly one Workspace.

Workspace is the security boundary.

The self-hosted version contains one default Workspace.

Future SaaS deployments create one Workspace per customer.

Examples:

```
demo.moa-app.fr
mycompany.moa-app.fr
```

Controllers should never manually filter by Workspace.

Repositories and services should operate inside the current Workspace.

---

# Development Rules

Business rules belong to Services.

Repositories never implement business rules.

Controllers remain thin.

Prefer explicit code.

Avoid unnecessary abstraction.

Prefer readability over cleverness.

---

# Coding Conventions

Use English everywhere in source code.

Use Symfony best practices.

Prefer composition over inheritance.

Avoid premature abstraction.

Static analysis must remain clean.

---

# Current Roadmap

Recently completed:

- Document domain
- File storage
- Bank accounts
- Bank transactions
- Analysis
- Analytical dimensions
- Frontend foundations

Current work:

- Third-party settlement model
- Workspace architecture

Next milestones:

- Settlement engine
- Analytical allocation UI
- Search
- Pagination
- Dashboard

---

# Architecture Decisions

## Documents are immutable

Documents represent business evidence.

Business workflows should not rewrite imported documents.

---

## Analysis and Settlement are independent

Analysis answers:

Where does the money belong?

Settlement answers:

Who owes money to whom?

Never merge these concepts.

---

## Reconciliation is independent

Reconciliation links payments to documents.

Settlement manages third-party balances.

These concepts intentionally remain separate.

---

## Workspace First

Every new business entity must belong to a Workspace.

Workspace isolation is a security requirement.

Never rely on controllers to implement tenant filtering.

---

# Philosophy

MOA is designed for the next ten years.

Whenever a design decision is made, prefer the solution that:

- keeps the domain model simple;
- scales to future requirements;
- preserves architectural consistency;
- avoids coupling unrelated concepts.

The objective is not to build features quickly.

The objective is to build a coherent platform that can evolve for many years.
