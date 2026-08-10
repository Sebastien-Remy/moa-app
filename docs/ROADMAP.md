# Roadmap

## v0.5 - Rich Document Import

### Status

Planned

### Objectives

Provide a complete and user-friendly document import workflow.

### Features

- Complete the EasyAdmin document import form
- Support all editable `Document` fields during import
- Improve the import page layout
- Introduce a clean Bootstrap-based interface
- Use Font Awesome icons where appropriate
- Fix the default document date behaviour
- Allow importing documents issued before the current date
- Improve the document list
- Display folder, third party, amount, tags and key metadata
- Open the attached document directly from EasyAdmin
- Improve the document detail page

### Out of scope

- Orphan file detection
- Storage consistency verification
- Maintenance commands
- File deduplication repair tools

---

# Roadmap v0.6 — Banking and Financial Analysis Foundation

## Goal

Introduce the first financial analysis layer in MOA.

This version establishes the data model required for:

- Bank accounts and bank transactions.
- Document-to-transaction reconciliation.
- Expense and income categorization.
- Analytical breakdowns.
- Multiple analytical dimensions (projects, associates, etc.).
- Future forecasting and profitability analysis.

The objective of **v0.6** is to build a robust and flexible data model without implementing advanced accounting workflows.

---

# Banking

## BankAccount

Create a `BankAccount` entity.

Fields:

- id
- name
- bankName
- iban (nullable)
- currency
- active

---

## BankTransaction

Create a `BankTransaction` entity linked to a bank account.

Fields:

- id
- bankAccount
- date
- valueDate (nullable)
- label
- amount
- reference (nullable)
- importReference (nullable)

A bank transaction represents an actual movement on a bank account.

It must be able to exist independently from any document.

---

# Document Reconciliation

## DocumentTransaction

Create a `DocumentTransaction` entity.

Fields:

- id
- document
- bankTransaction
- amount

This relationship must support:

- One document paid by multiple bank transactions.
- One bank transaction paying multiple documents.
- Partial payments.

Automatic reconciliation is **out of scope** for v0.6.

---

# Categories

## Category

Create a hierarchical `Category` entity.

Fields:

- id
- name
- parent (nullable)
- position
- active

Example:

```text
Expenses
├── Travel
│   ├── Hotel
│   └── Meals
├── Equipment
└── Software
```

Categories describe the **economic nature** of an amount.

---

# Financial Analysis

## Analysis

Create an `Analysis` entity representing a financial allocation.

Fields:

- id
- document (nullable)
- bankTransaction (nullable)
- category (nullable)
- amount
- notes (nullable)

An analysis line belongs to either:

- a document
- or a bank transaction

This allows transactions and documents to exist before they are analysed.

---

# Analytical Dimensions

## AnalysisDimension

Create an `AnalysisDimension` entity.

Fields:

- id
- name
- code (nullable)
- position
- active

Examples:

- Project
- Associate
- Department
- Client
- Activity

---

## AnalysisDimensionValue

Create a hierarchical `AnalysisDimensionValue` entity.

Fields:

- id
- analysisDimension
- name
- parent (nullable)
- position
- active

Example:

```text
Project
├── EdisonTV
│   ├── TV Show A
│   └── TV Show B
└── GorillaDev
    └── MOA
```

Another independent hierarchy:

```text
Associate
├── Associate A
├── Associate B
└── Associate C
```

---

## AnalysisDimensionAssignment

Create an `AnalysisDimensionAssignment` entity.

Fields:

- id
- analysis
- analysisDimensionValue

This allows multiple analytical axes on the same analysis line.

Example:

```text
Amount: 1200 €

Category:
Travel

Dimensions:
Project   → TV Show A
Associate → Associate A
```

---

# EasyAdmin

Create basic CRUD interfaces for:

- BankAccount
- BankTransaction
- DocumentTransaction
- Category
- Analysis
- AnalysisDimension
- AnalysisDimensionValue
- AnalysisDimensionAssignment

The purpose is to validate the data model and allow manual administration.

No dedicated financial interface is required yet.

---

# Database

Create Doctrine migrations for all entities.

Add appropriate indexes and foreign keys.

The database must guarantee the integrity of:

- bank transactions
- document reconciliation
- category hierarchy
- analytical dimension hierarchy
- analysis assignments

---

# Out of Scope

The following features are intentionally postponed:

- OFX import
- CSV import
- QIF import
- Bank statement import
- Automatic reconciliation
- Duplicate detection
- Budget management
- Forecasting
- VAT management
- Accounting entries
- Accounting exports
- Financial dashboards
- Profitability reports
- Maintenance commands

---

# Target Architecture

```text
Document
   │
   ├── DocumentTransaction ── BankTransaction ── BankAccount
   │
   └── Analysis
          │
          ├── Category
          │
          └── AnalysisDimensionAssignment
                  │
                  └── AnalysisDimensionValue
                           │
                           └── AnalysisDimension
```

At the end of **v0.6**, MOA will have a stable financial model capable of supporting future developments such as reconciliation, budgeting, forecasting, analytical reporting and accounting exports.