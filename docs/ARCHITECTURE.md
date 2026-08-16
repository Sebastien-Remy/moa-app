# Technical Architecture v3

## Purpose

This document defines the long-term technical architecture of MOA.

It is intended to be the reference document for contributors and maintainers.

It describes both the implementation architecture and the fundamental design principles that every feature must follow.

---

# 1. Architectural Vision

MOA is not a document manager with financial features.

MOA is a financial document platform built around four independent business domains:

- Document
- Analysis
- Settlement
- Bank Transaction

These domains intentionally remain decoupled.

```text
                 Document
                     │
      ┌──────────────┼──────────────┐
      │              │              │
      ▼              ▼              ▼
 Analysis     ThirdPartyEntry   DocumentTransaction
      │                             │
      └──────────────┬──────────────┘
                     ▼
             BankTransaction
```

## Core Principles

- Documents describe business events.
- Analysis explains the economic destination of money.
- Settlement represents obligations between third parties.
- Bank transactions represent actual financial movements.
- Reconciliation is independent from settlement.

---

# 2. Layered Architecture

```text
Interface
    │
Application
    │
Domain
    │
Infrastructure
```

Dependencies always point toward the Domain layer.

---

# 3. Workspace Isolation

Every business entity belongs to exactly one Workspace.

The Workspace is the highest security boundary.

No relationship may cross Workspace boundaries.

Self-hosted deployments use one default Workspace.

Future SaaS deployments create one Workspace per customer.

```text
demo.moa-app.fr
customer-a.moa-app.fr
customer-b.moa-app.fr
```

The self-hosted and SaaS editions share the same codebase.

---

# 4. Domain Model

## Document

Represents business evidence.

Examples:

- invoice
- receipt
- payroll summary
- contract
- bank statement

## Analysis

Answers:

> Where does the money belong?

Supports:

- categories
- projects
- future analytical dimensions

## Settlement

Answers:

> Who owes money to whom?

Supports:

- suppliers
- customers
- employees
- tax authorities
- social organizations

One document may create multiple settlement entries.

## Bank Transaction

Represents a movement imported or created from a real bank account.

---

# 5. Storage

Document metadata and physical storage are separated.

```text
Document
    │
DocumentFile
    │
StoredFile
```

Stored files are deduplicated using SHA-256.

---

# 6. Services

Business rules belong to Services.

Repositories provide persistence only.

Controllers orchestrate the request.

Twig renders data.

---

# 7. Money

Money is always stored as integer minor units.

Never use floating point values.

---

# 8. Validation

Entity validation:
- local invariants

Service validation:
- business workflows
- cross-entity consistency

---

# 9. Design Principles

- Workspace-first architecture.
- Explicit responsibilities.
- Thin controllers.
- Repository = data access.
- Service = business logic.
- Immutable business documents whenever possible.
- Analysis and Settlement are independent.
- Prefer explicit validation.
- Prefer simplicity over abstraction.
- Maintain a clean PHPStan baseline.

---

# 10. Future Architecture

The current architecture prepares MOA for:

- SaaS deployment
- Multi-workspace hosting
- Advanced analytical accounting
- OCR pipeline
- Electronic invoicing
- Reporting
- Dashboards
- Future accounting exports

These capabilities should reuse the existing domain model instead of introducing parallel concepts.
