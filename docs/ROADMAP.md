# Roadmap

# v0.6 — Financial Core

**Status: Completed**

## Goal

Introduce the first financial core in MOA.

This version establishes the data model required for:

- Currency management.
- Bank accounts and bank transactions.
- Manual document-to-transaction reconciliation.
- Partial payments and split payments.
- Expense and income categorization.
- Financial allocations.
- Financial analysis by third party.
- Multiple analytical dimensions.
- Future forecasting and profitability analysis.

The objective of **v0.6** is to build a robust and flexible financial data model without implementing advanced accounting workflows.

The implementation remains primarily accessible through **EasyAdmin** in this version.

No dedicated end-user financial interface is required yet.

---

# Design Decisions

## Money is stored in minor units

MOA already stores `Document.totalAmount` as an integer.

v0.6 keeps this approach and standardizes it for all financial amounts.

All monetary values are stored as integer minor units using `BIGINT`.

Examples:

```text
125000 EUR with decimalPlaces = 2 → 1,250.00 EUR
1999 USD with decimalPlaces = 2   → 19.99 USD
500 JPY with decimalPlaces = 0    → 500 JPY
```

This avoids floating-point rounding issues and provides a consistent representation for all supported currencies.

The same storage strategy must be used for:

- `Document.totalAmount`
- `BankTransaction.amount`
- `DocumentTransaction.amount`
- `Analysis.amount`

Currency formatting and conversion between stored minor units and displayed values must be centralized and must use the associated `Currency.decimalPlaces`.

---

## Currency belongs to the financial source

Currency must not be duplicated unnecessarily.

A document owns its currency:

```text
Document → Currency
```

A bank account owns its currency:

```text
BankAccount → Currency
```

A bank transaction inherits its currency from its bank account:

```text
BankTransaction → BankAccount → Currency
```

An analysis inherits its currency from its source:

```text
Analysis → Document → Currency
```

or:

```text
Analysis → BankTransaction → BankAccount → Currency
```

`BankTransaction` and `Analysis` therefore do not contain their own currency field.

This avoids contradictory data and makes the financial source authoritative.

---

## Documents remain usable for non-financial content

MOA is a document management application, not every document necessarily represents money.

For this reason:

- `Document.totalAmount` remains nullable.
- `Document.currency` is nullable.

The following combinations are valid:

```text
totalAmount = null
currency = null
```

```text
totalAmount = 125000
currency = EUR
```

The following combinations are invalid:

```text
totalAmount = 125000
currency = null
```

```text
totalAmount = null
currency = EUR
```

When a financial amount is entered, the document must have a currency.

EasyAdmin should automatically preselect the default currency when a user enters financial information.

---

## Original bank data must be preserved

A bank transaction has two different textual descriptions.

`bankLabel` contains the original information received from the bank.

Example:

```text
VIR F656Z
```

`notes` contains an optional description entered by the user.

Example:

```text
Payment of invoice F656Z
```

The bank label must remain faithful to the imported banking data.

User annotations must never overwrite it.

This separation will later be important for:

- Bank imports.
- Duplicate detection.
- Reconciliation suggestions.
- Search.
- Auditability.

---

## Reconciliation is an allocation, not a direct relation

A direct many-to-many relationship between documents and transactions would not be sufficient because MOA must store the amount reconciled between them.

The `DocumentTransaction` entity therefore represents an allocation between a document and a bank transaction.

This supports:

- One document paid by several transactions.
- One transaction paying several documents.
- Partial payments.
- Split payments.
- Partial reconciliation.

---

## Reconciliation status is derived

Do not persist a reconciliation status such as:

- unreconciled
- partially reconciled
- reconciled

These states can be calculated from reconciliation amounts.

For a document:

```text
reconciledAmount = SUM(DocumentTransaction.amount)
remainingAmount = Document.totalAmount - reconciledAmount
```

For a bank transaction:

```text
reconciledAmount = SUM(DocumentTransaction.amount)
remainingAmount = ABS(BankTransaction.amount) - reconciledAmount
```

This avoids storing a status that could become inconsistent with the actual reconciliation lines.

---

## ThirdParty remains a business entity

`ThirdParty` already exists in MOA and is already linked to `Document`.

It represents a real business counterpart and must not be duplicated as an analytical dimension.

Typical examples include:

- Client.
- Supplier.
- Service provider.
- Partner.
- Public organization.

Analytical dimensions are reserved for additional independent axes such as:

- Project.
- Associate.
- Department.
- Activity.
- Business unit.

---

## Bank transactions may have a ThirdParty

A bank transaction may exist before any document is attached to it.

Financial analysis by third party must therefore also be possible for standalone bank transactions.

Add an optional `thirdParty` relationship to `BankTransaction`.

```text
BankTransaction
    └── ThirdParty (nullable)
```

This relationship represents the identified counterpart of the banking movement.

It remains nullable because:

- The counterpart may initially be unknown.
- Some banking movements have no meaningful third party.
- A transaction may later be reconciled with one or several documents.
- Imported data may not be sufficient to identify the counterpart.

No automatic third-party detection is implemented in v0.6.

When an analysis originates from a document, the third party comes from:

```text
Analysis → Document → ThirdParty
```

When an analysis originates directly from a bank transaction, the third party comes from:

```text
Analysis → BankTransaction → ThirdParty
```

Do not duplicate `thirdParty` directly on `Analysis`.

---

# Currency

## Currency Entity

Create a `Currency` entity representing a supported monetary currency.

### Fields

- id
- code
- name
- symbol (nullable)
- decimalPlaces
- active
- isDefault

### `code`

Use the ISO 4217 alphabetic currency code.

Examples:

```text
EUR
USD
GBP
CHF
JPY
```

Rules:

- Required.
- Exactly 3 letters.
- Stored in uppercase.
- Unique.

Example:

```text
EUR
```

---

### `name`

Human-readable currency name.

Examples:

```text
Euro
US Dollar
Pound Sterling
Swiss Franc
Japanese Yen
```

Rules:

- Required.
- Maximum reasonable string length.
- Not used as a technical identifier.

---

### `symbol`

Optional display symbol.

Examples:

```text
€
$
£
CHF
¥
```

The symbol is for display only.

Business logic must always rely on the ISO code and not on the symbol because several currencies may share the same symbol.

---

### `decimalPlaces`

Number of digits used by the currency minor unit.

Examples:

```text
EUR → 2
USD → 2
JPY → 0
```

Rules:

- Required.
- Integer.
- Must be within a reasonable supported range.
- Recommended validation range for v0.6: `0..4`.

This value is used to convert stored minor units into display amounts.

---

### `active`

Inactive currencies remain available for historical data but should no longer be proposed by default when creating new financial records.

Historical records must never lose their currency because a currency is deactivated.

---

### `isDefault`

One currency may be configured as the default MOA currency.

The default currency is used to preselect currency fields when creating financial data.

Rules:

- At most one currency may be the default.
- The default currency must be active.
- Deactivating the default currency must be prevented until another default has been selected.
- Initial installation should provide one default currency.

For the initial seed:

```text
EUR
```

is the default currency.

The application architecture must not otherwise assume EUR.

---

## Initial Currency Dataset

Seed at least:

```text
EUR | Euro           | €   | 2 | active | default
USD | US Dollar      | $   | 2 | active
GBP | Pound Sterling | £   | 2 | active
CHF | Swiss Franc    | CHF | 2 | active
```

The seed mechanism should remain easy to extend later.

---

# Document

Extend the existing `Document` entity with currency support.

Current financial field:

- `totalAmount`

Add:

- `currency` (nullable)

Relationship:

```text
Document
    └── Currency
```

`totalAmount` remains stored as `BIGINT` minor units.

Validation:

```text
totalAmount == null ⇔ currency == null
```

If `totalAmount` is defined:

- Currency is required.
- Amount must remain positive or zero according to the existing document model.

If `totalAmount` is null:

- Currency must also be null.

Update the Document EasyAdmin CRUD to support the currency.

The form should use the configured default currency when financial information is entered.

The existing `ThirdParty` relationship remains unchanged.

---

# Banking

## BankAccount

Create a `BankAccount` entity.

### Fields

- id
- name
- bankName
- iban (nullable)
- currency
- active

### Currency

Each bank account has exactly one currency.

Relationship:

```text
BankAccount
    └── Currency
```

Currency is required.

The bank account currency cannot be inferred from transactions and must remain stable for the lifetime of the account.

Changing the currency of an account containing transactions must not be allowed.

A new account may use the default currency as its initial EasyAdmin selection.

---

### IBAN

`iban` is nullable because not all financial accounts have an IBAN.

Examples include:

- Payment accounts.
- Online wallets.
- Foreign accounts.
- Internal financial accounts.

No advanced IBAN validation is required for v0.6 beyond basic normalization and reasonable length validation.

---

## BankTransaction

Create a `BankTransaction` entity.

### Fields

- id
- bankAccount
- date
- valueDate (nullable)
- bankLabel
- notes (nullable)
- amount
- thirdParty (nullable)
- reference (nullable)
- importReference (nullable)

### `bankAccount`

Required.

The transaction currency is inherited from this account.

---

### `date`

Required.

Represents the transaction date provided by the bank.

---

### `valueDate`

Optional.

Represents the banking value date when available.

---

### `bankLabel`

Required.

Contains the original transaction description from the bank.

Example:

```text
VIR F656Z
```

This field must remain separate from user annotations.

---

### `notes`

Optional free text entered by the user.

Example:

```text
Payment of invoice F656Z
```

Follow the same general notes convention used elsewhere in MOA.

---

### `amount`

Required.

Stored as signed `BIGINT` minor units.

Convention:

```text
positive amount → money entering the account
negative amount → money leaving the account
```

Examples:

```text
125000  EUR → +1,250.00 EUR incoming
-8450   EUR → -84.50 EUR outgoing
```

Zero-value bank transactions should not normally be allowed.

---

### `thirdParty`

Optional relationship to the existing `ThirdParty` entity.

This allows a transaction to be classified by counterpart even when there is no document.

The field may remain null.

No automatic synchronization with reconciled documents is required in v0.6.

---

### `reference`

Optional banking or business reference.

This is distinct from the bank label.

---

### `importReference`

Optional technical identifier reserved for future bank imports.

It should be capable of storing an identifier supplied or generated during an import process.

No import process is implemented in v0.6.

---

# Document Reconciliation

## DocumentTransaction

Create a `DocumentTransaction` entity representing a reconciliation allocation.

### Fields

- id
- document
- bankTransaction
- amount

`amount` is stored as a positive `BIGINT` in minor units.

It represents the absolute amount allocated between the document and the transaction.

Example:

```text
Document totalAmount:
100000 EUR

BankTransaction amount:
-60000 EUR

DocumentTransaction amount:
60000 EUR
```

---

## Supported Reconciliation Cases

### One document paid by one transaction

```text
Document 1,000 EUR
        │
        └── 1,000 EUR ── Transaction -1,000 EUR
```

---

### One document paid by multiple transactions

```text
Document 1,000 EUR
        │
        ├── 600 EUR ── Transaction A -600 EUR
        └── 400 EUR ── Transaction B -400 EUR
```

---

### One transaction paying multiple documents

```text
Document A 600 EUR ── 600 EUR ┐
                              ├── Transaction -1,000 EUR
Document B 400 EUR ── 400 EUR ┘
```

---

### Partial reconciliation

```text
Document 1,000 EUR
        │
        └── 600 EUR ── Transaction -600 EUR

Remaining document amount:
400 EUR
```

---

## Manual Reconciliation Workflow

v0.6 uses a manual EasyAdmin workflow.

### Step 1 — Create or identify the document

The document contains:

- `totalAmount`
- `currency`
- optional `thirdParty`

---

### Step 2 — Create or identify the bank transaction

The bank transaction contains:

- account
- bank date
- original bank label
- optional notes
- amount
- optional third party

---

### Step 3 — Create a DocumentTransaction

The administrator selects:

- Document.
- Bank transaction.
- Reconciled amount.

---

### Step 4 — Validate compatibility

Before saving, MOA must verify:

- Both objects exist.
- Document has a financial amount.
- Document has a currency.
- Bank transaction has a bank account.
- Document currency equals bank account currency.
- Reconciliation amount is strictly positive.
- The allocation does not exceed the remaining amount of the document.
- The allocation does not exceed the remaining absolute amount of the transaction.

---

### Step 5 — Derive reconciliation state

After saving:

```text
Document reconciled amount
= SUM(all DocumentTransaction amounts for the document)
```

```text
Transaction reconciled amount
= SUM(all DocumentTransaction amounts for the transaction)
```

No persistent reconciliation status is required.

---

## Reconciliation Integrity Rules

### Currency equality

A reconciliation is only valid if:

```text
Document.currency == BankTransaction.bankAccount.currency
```

Cross-currency reconciliation is out of scope for v0.6.

---

### Positive allocation

`DocumentTransaction.amount` must be greater than zero.

The sign of the bank transaction is not copied into the reconciliation line.

---

### Maximum document allocation

The sum of all allocations for a document must not exceed:

```text
Document.totalAmount
```

---

### Maximum transaction allocation

The sum of all allocations for a transaction must not exceed:

```text
ABS(BankTransaction.amount)
```

---

### Unique document/transaction pair

A document and a bank transaction should have at most one `DocumentTransaction` relationship.

Add a unique database constraint on:

```text
(document_id, bank_transaction_id)
```

If the allocation changes, update the existing relationship rather than creating another one.

---

### Deletion rules

Deleting a reconciliation line must not delete:

- The document.
- The bank transaction.

Deleting a document or bank transaction that has reconciliation lines should be handled explicitly and must not silently leave inconsistent allocations.

Prefer restrictive foreign-key behaviour for core financial data.

---

# Categories

## Category

Create a hierarchical `Category` entity.

### Fields

- id
- name
- parent (nullable)
- position
- active

Categories describe the **economic nature** of an amount.

Example:

```text
Expenses
├── Travel
│   ├── Hotel
│   └── Meals
├── Equipment
└── Software
```

Another possible hierarchy:

```text
Income
├── Services
├── Sales
└── Other Income
```

Categories are independent from analytical dimensions.

---

## Category Hierarchy Integrity

A category:

- May have no parent.
- May have one parent.
- Must not be its own parent.
- Must not create a circular hierarchy.

Inactive categories remain available for historical data but should not be proposed by default for new analyses.

---

# Financial Analysis

## Analysis

Create an `Analysis` entity representing a financial allocation.

### Fields

- id
- document (nullable)
- bankTransaction (nullable)
- category (nullable)
- amount
- notes (nullable)

An analysis line represents how part or all of a financial source is categorized and analysed.

---

## Analysis Source

An analysis belongs to exactly one source:

```text
Document
```

or:

```text
BankTransaction
```

Never both.

Required invariant:

```text
document XOR bankTransaction
```

This must be enforced by Symfony validation and, where practical, by database constraints.

---

## Analysis Currency

Currency is inherited.

For document analysis:

```text
Analysis
    └── Document
            └── Currency
```

For transaction analysis:

```text
Analysis
    └── BankTransaction
            └── BankAccount
                    └── Currency
```

No currency field is stored on `Analysis`.

---

## Analysis Amount

`amount` is stored as signed `BIGINT` minor units.

This allows financial reports to aggregate positive and negative values consistently.

For a transaction-based analysis, the sum of analysis lines should eventually be capable of matching the signed transaction amount.

For a document-based analysis, the financial sign may depend on the business meaning of the document and its direction.

Strict automatic balancing of all analysis lines is **not required** for v0.6.

v0.6 must nevertheless prevent nonsensical values and provide a model compatible with future balancing validation.

---

## Analysis Category

`category` is nullable.

This allows financial data to exist before categorization.

A transaction or document can therefore be:

- Not analysed.
- Partially analysed.
- Fully analysed.

No persistent analysis status is required.

---

## Analysis Notes

Optional user notes specific to the allocation.

These notes are independent from:

- `Document.notes`
- `BankTransaction.notes`

---

# Third-Party Financial Analysis

MOA must support financial reporting by `ThirdParty`.

The existing relationship is:

```text
Document
    └── ThirdParty
```

v0.6 adds:

```text
BankTransaction
    └── ThirdParty
```

The third party of an analysis is therefore derived from the source.

For a document analysis:

```text
Analysis
    └── Document
            └── ThirdParty
```

For a bank transaction analysis:

```text
Analysis
    └── BankTransaction
            └── ThirdParty
```

Do not add `thirdParty` to `Analysis`.

This prevents duplicated and potentially contradictory information.

---

## ThirdParty and Reconciliation

A bank transaction may be reconciled with a document that has a third party.

v0.6 does not automatically copy the third party between the document and the transaction.

Reasons:

- The transaction may have been classified before reconciliation.
- One transaction may reconcile multiple documents.
- Multiple documents could theoretically involve different third parties.
- Automatically changing historical financial metadata can create unexpected side effects.

EasyAdmin may show both values to help administrators identify inconsistencies.

Automatic third-party suggestions can be introduced in a later version.

---

# Analytical Dimensions

## AnalysisDimension

Create an `AnalysisDimension` entity.

### Fields

- id
- name
- code (nullable)
- position
- active

Examples:

- Project
- Associate
- Department
- Activity
- Business Unit

An analytical dimension defines an independent reporting axis.

`ThirdParty` is deliberately excluded because it already exists as a core business entity.

---

## AnalysisDimensionValue

Create a hierarchical `AnalysisDimensionValue` entity.

### Fields

- id
- analysisDimension
- name
- parent (nullable)
- position
- active

Example:

```text
Project
├── Project Group A
│   ├── Project Alpha
│   └── Project Beta
└── Project Group B
    └── Project Gamma
```

Another independent hierarchy:

```text
Associate
├── Associate A
├── Associate B
└── Associate C
```

---

## AnalysisDimensionValue Integrity

A value:

- Belongs to exactly one `AnalysisDimension`.
- May have one parent.
- May not be its own parent.
- May not create a circular hierarchy.
- Must have a parent belonging to the same dimension.

Inactive values remain usable for historical data but should not be proposed by default for new assignments.

---

## AnalysisDimensionAssignment

Create an `AnalysisDimensionAssignment` entity.

### Fields

- id
- analysis
- analysisDimensionValue

This allows multiple independent analytical axes on one analysis line.

Example:

```text
Analysis
Amount: -120000

Category:
Travel

Dimensions:
Project   → Project Alpha
Associate → Associate A
```

---

## Assignment Integrity

An analysis may have at most one value for each analytical dimension.

Valid:

```text
Project   → Project Alpha
Associate → Associate A
```

Invalid:

```text
Project → Project Alpha
Project → Project Beta
```

The current model stores the dimension indirectly through `analysisDimensionValue`.

This rule should therefore be enforced at application level unless a clean database-level solution is introduced.

Duplicate assignment of the exact same dimension value to the same analysis must also be prevented.

---

# EasyAdmin

Create basic CRUD interfaces for:

- Currency.
- BankAccount.
- BankTransaction.
- DocumentTransaction.
- Category.
- Analysis.
- AnalysisDimension.
- AnalysisDimensionValue.
- AnalysisDimensionAssignment.

Update the existing Document CRUD.

The purpose of EasyAdmin in v0.6 is to:

- Validate the financial model.
- Create realistic test data.
- Inspect relationships.
- Manually reconcile documents and transactions.
- Manually create financial allocations.
- Verify validation rules.
- Identify missing requirements before building a dedicated financial interface.

---

## Currency EasyAdmin

Support:

- Code.
- Name.
- Symbol.
- Decimal places.
- Active state.
- Default currency.

Clearly identify the default currency in list views.

Prevent invalid default-currency states.

---

## Document EasyAdmin

Add:

- Currency next to `totalAmount`.

Display financial amounts using the associated currency.

Use the default currency as a form convenience, without forcing a currency onto documents that have no financial amount.

---

## BankAccount EasyAdmin

Support:

- Name.
- Bank name.
- IBAN.
- Currency.
- Active state.

Prevent changing account currency once transactions exist.

---

## BankTransaction EasyAdmin

Clearly separate:

### Bank data

- Bank account.
- Date.
- Value date.
- Bank label.
- Amount.
- Reference.
- Import reference.

### User data

- Third party.
- Notes.

The original bank label must be visually identifiable as bank-provided information.

---

## DocumentTransaction EasyAdmin

Provide a simple manual reconciliation form containing:

- Document.
- Bank transaction.
- Amount.

Display enough information in selectors to distinguish records easily, ideally including:

For documents:

- Date.
- Reference.
- Third party.
- Amount.
- Currency.

For transactions:

- Date.
- Bank label.
- Notes when present.
- Amount.
- Currency.

Validation errors must clearly explain:

- Currency mismatch.
- Amount exceeding remaining document value.
- Amount exceeding remaining transaction value.
- Duplicate relationship.

---

## Analysis EasyAdmin

Support:

- Source document or bank transaction.
- Category.
- Amount.
- Notes.

The form must enforce exactly one source.

Associated analytical dimensions may initially be managed through their own CRUD if embedding them in the Analysis form would add unnecessary complexity.

---

# Database and Integrity

Create Doctrine migrations for all new entities and relationships.

Use the same ULID identifier strategy as the existing MOA domain entities.

Add appropriate:

- Foreign keys.
- Indexes.
- Unique constraints.
- Nullability rules.

---

## Recommended Database Constraints

### Currency

Unique:

```text
code
```

Indexes:

```text
active
is_default
```

---

### BankAccount

Indexes:

```text
currency_id
active
```

---

### BankTransaction

Indexes:

```text
bank_account_id
date
value_date
third_party_id
reference
import_reference
```

`importReference` does not need to be globally unique in v0.6 unless the future import design proves that it can safely be guaranteed.

---

### DocumentTransaction

Unique:

```text
(document_id, bank_transaction_id)
```

Indexes:

```text
document_id
bank_transaction_id
```

Check/application validation:

```text
amount > 0
```

---

### Category

Indexes:

```text
parent_id
position
active
```

---

### Analysis

Indexes:

```text
document_id
bank_transaction_id
category_id
```

Required logical constraint:

```text
document XOR bank_transaction
```

---

### AnalysisDimension

Consider unique `code` when non-null.

Indexes:

```text
position
active
```

---

### AnalysisDimensionValue

Indexes:

```text
analysis_dimension_id
parent_id
position
active
```

---

### AnalysisDimensionAssignment

Unique:

```text
(analysis_id, analysis_dimension_value_id)
```

Additional one-value-per-dimension integrity remains application-level unless the schema is later adjusted to persist `analysisDimension` directly on the assignment.

---

# Application-Level Integrity Rules

Some rules depend on aggregate data or relationships and cannot be safely guaranteed by simple foreign keys.

These must be enforced in domain/service validation.

---

## Currency Rules

- Code is required.
- Code is normalized to uppercase.
- Code contains exactly three letters.
- Code is unique.
- Decimal places are within the supported range.
- Only one default currency may exist.
- Default currency must be active.
- Default currency cannot be deactivated without selecting another default.

---

## Document Financial Rules

- `totalAmount` and `currency` must either both be null or both be defined.
- `totalAmount` remains non-negative.
- Currency changes must not invalidate existing reconciliations.

A document that already has reconciliation or analysis data should not allow unsafe financial changes without validation.

---

## BankAccount Rules

- Name is required.
- Currency is required.
- Currency cannot be changed when transactions already exist.

---

## BankTransaction Rules

- Bank account is required.
- Date is required.
- Bank label is required.
- Amount is required.
- Amount must not be zero.
- Third party is optional.
- Notes are optional.

Changing the transaction amount or account must not invalidate existing reconciliation allocations.

---

## DocumentTransaction Rules

- Document is required.
- Bank transaction is required.
- Amount is strictly positive.
- Currencies must match.
- Document must have a financial amount.
- Document must have a currency.
- Sum of document allocations must not exceed the document total.
- Sum of transaction allocations must not exceed the absolute transaction amount.
- A document/transaction pair may only exist once.

---

## Category Rules

- Category cannot be its own parent.
- Category hierarchy cannot contain cycles.

---

## Analysis Rules

Exactly one source must be defined:

```text
document XOR bankTransaction
```

The selected source must provide a valid currency path.

Analysis amount must be a valid integer minor-unit value.

Strict source balancing is postponed, but the architecture must remain compatible with it.

---

## AnalysisDimensionValue Rules

- Dimension is required.
- Parent cannot be self.
- Parent must belong to the same dimension.
- Hierarchy cannot contain cycles.

---

## AnalysisDimensionAssignment Rules

- Analysis is required.
- Dimension value is required.
- Exact duplicate assignments are forbidden.
- Only one value from a given dimension may be assigned to an analysis.

---

# Financial Workflow Validation Dataset

Before considering v0.6 complete, validate the model entirely through EasyAdmin using realistic but generic data.

---

## Currency Dataset

Create:

```text
EUR
USD
GBP
CHF
```

Verify:

- EUR is default.
- Only one currency can be default.
- Default currency cannot be inactive.
- Amount formatting respects decimal places.

---

## Bank Account Dataset

Create:

```text
Main Account
Currency: EUR
```

Verify:

- Currency is required.
- Currency cannot be changed after transactions exist.

---

## Third Party Dataset

Create:

```text
Example Supplier
Example Client
```

---

## Document Dataset

Create:

```text
Supplier invoice
ThirdParty: Example Supplier
Amount: 1,200.00 EUR
```

Verify:

- Currency is required when amount exists.
- Default currency is conveniently proposed.
- Third party remains linked through the existing document relationship.

---

## Bank Transaction Dataset

Create:

```text
bankLabel:
VIR F656Z

notes:
Payment of supplier invoice

amount:
-1,200.00 EUR

thirdParty:
Example Supplier
```

Verify:

- Original bank label and notes remain separate.
- Third party can be assigned without a document.
- Currency is inherited from the bank account.

---

## Full Reconciliation

Create:

```text
Document:
1,200.00 EUR

Transaction:
-1,200.00 EUR

DocumentTransaction:
1,200.00 EUR
```

Verify:

```text
Document remaining amount = 0
Transaction remaining amount = 0
```

---

## Partial Reconciliation

Create:

```text
Document:
1,200.00 EUR

Transaction:
-500.00 EUR

DocumentTransaction:
500.00 EUR
```

Verify:

```text
Document remaining amount = 700.00 EUR
Transaction remaining amount = 0
```

---

## Split Transaction

Create two documents:

```text
Document A:
600.00 EUR

Document B:
400.00 EUR
```

Create one transaction:

```text
-1,000.00 EUR
```

Create:

```text
DocumentTransaction A:
600.00 EUR

DocumentTransaction B:
400.00 EUR
```

Verify that the transaction is fully reconciled.

---

## Invalid Reconciliation

Verify that MOA rejects:

- Currency mismatch.
- Allocation larger than document remaining amount.
- Allocation larger than transaction remaining amount.
- Zero allocation.
- Negative allocation.
- Duplicate document/transaction pair.

---

## Financial Analysis Dataset

Create an analysis linked to a document.

Example:

```text
Amount:
-1,200.00 EUR

Category:
Expenses / Services

Dimensions:
Project   → Project Alpha
Associate → Associate A
```

Verify that the associated third party can be obtained through:

```text
Analysis → Document → ThirdParty
```

Create another analysis directly on an unreconciled bank transaction and verify:

```text
Analysis → BankTransaction → ThirdParty
```

---

# Out of Scope

The following features are intentionally postponed:

- OFX import.
- CSV import.
- QIF import.
- Bank statement import.
- Automatic reconciliation.
- Reconciliation suggestions.
- Duplicate transaction detection.
- Automatic third-party detection.
- Automatic third-party synchronization between documents and transactions.
- Exchange-rate storage.
- Currency conversion.
- Cross-currency reconciliation.
- Foreign exchange gains or losses.
- Budget management.
- Forecasting.
- VAT management.
- Accounting entries.
- Double-entry bookkeeping.
- Accounting exports.
- Financial dashboards.
- Profitability reports.
- Analysis balancing enforcement.
- Dedicated financial user interface.
- Maintenance commands.

---

# Target Architecture

```text
Currency
   │
   ├── Document
   │      │
   │      ├── ThirdParty
   │      │
   │      ├── DocumentTransaction
   │      │          │
   │      │          └── BankTransaction
   │      │                    │
   │      │                    ├── ThirdParty
   │      │                    └── BankAccount
   │      │                           │
   │      │                           └── Currency
   │      │
   │      └── Analysis
   │
   └── BankAccount


Analysis
   │
   ├── Document XOR BankTransaction
   │
   ├── Category
   │
   └── AnalysisDimensionAssignment
              │
              └── AnalysisDimensionValue
                         │
                         ├── parent
                         │
                         └── AnalysisDimension
```

---

# Suggested Implementation Order

## Phase 1 — Currency Foundation

- Create `Currency`.
- Add initial currency dataset.
- Implement default currency rules.
- Add `currency` to `Document`.
- Update Document EasyAdmin.
- Centralize amount formatting.

This phase establishes the monetary convention used by every later entity.

---

## Phase 2 — Banking Foundation

- Create `BankAccount`.
- Create `BankTransaction`.
- Add optional `ThirdParty` to bank transactions.
- Implement `bankLabel` / `notes` separation.
- Create EasyAdmin CRUDs.
- Validate signed minor-unit amounts.

---

## Phase 3 — Manual Reconciliation

- Create `DocumentTransaction`.
- Add currency compatibility validation.
- Add remaining-amount validation.
- Add unique document/transaction constraint.
- Create manual EasyAdmin reconciliation CRUD.
- Validate full, partial and split reconciliation scenarios.

---

## Phase 4 — Categories

- Create hierarchical `Category`.
- Add hierarchy validation.
- Create EasyAdmin CRUD.

---

## Phase 5 — Financial Analysis

- Create `Analysis`.
- Enforce document XOR bank transaction.
- Implement source-derived currency.
- Confirm source-derived third party.
- Create EasyAdmin CRUD.

---

## Phase 6 — Analytical Dimensions

- Create `AnalysisDimension`.
- Create hierarchical `AnalysisDimensionValue`.
- Create `AnalysisDimensionAssignment`.
- Enforce one value per dimension and analysis.
- Create EasyAdmin CRUDs.

---

## Phase 7 — Validation and Cleanup

- Execute the complete validation dataset.
- Review indexes and foreign keys.
- Review EasyAdmin usability.
- Verify migration from the existing Document model.
- Update technical documentation.
- Update changelog and release documentation.
- Prepare v0.6 release.

---

# v0.6 Completion Criteria

v0.6 is complete with the following capabilities:

- Currency reference data exists.
- ISO 4217 currency codes are supported.
- A single active default currency is enforced.
- Currency decimal precision is respected.
- All financial values use integer minor units.
- Documents support optional financial currency consistently with `totalAmount`.
- Bank accounts have a fixed currency.
- Bank transactions can be created manually.
- Bank transactions preserve the original bank label.
- Bank transactions support independent user notes.
- Bank transactions may be linked to a third party.
- Documents and bank transactions can be reconciled manually.
- Partial reconciliation works.
- Split reconciliation works.
- Over-reconciliation is rejected.
- Cross-currency reconciliation is rejected.
- Reconciliation state can be derived without a persisted status.
- Categories support hierarchy.
- Analyses can target either documents or bank transactions.
- Third-party analysis works for both source types.
- Multiple analytical dimensions can be assigned to an analysis.
- Analytical values support hierarchy.
- Core integrity rules are validated.
- Doctrine migrations are clean.
- The full financial model can be exercised through EasyAdmin.

The v0.6 cleanup and validation pass also established:

- A consistent Entity → Repository → Service → EasyAdmin architecture.
- Centralized business-rule handling through services and `BusinessRuleException`.
- Consistent create, update and delete workflows in EasyAdmin.
- Reviewed Doctrine indexes, relations and deletion policies.
- Consistent ULID handling across the financial model.
- Clean entity mappings, validation rules and display names.
- PHPStan level 6 with zero errors.

At the end of **v0.6**, MOA has a stable financial core capable of supporting future developments such as bank imports, reconciliation assistance, budgeting, forecasting, analytical reporting, third-party reporting, profitability analysis and accounting exports.

