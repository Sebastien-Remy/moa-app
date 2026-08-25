# v0.9 — Assisted Document Entry

**Status:** Completed

## Goal

Improve document entry in MOA by reducing repetitive work and making recurring and batch document imports faster.

This version introduces three complementary improvements:

- configurable default document status;
- reusable document models based on existing documents;
- batch PDF import.

The objective is to accelerate document entry while keeping the workflow explicit, predictable and fully controlled by the user.

---

## Scope

### Default Document Status

Allow one `Status` to be configured as the default status for newly created documents.

The default status is managed from EasyAdmin.

When a new document is created, MOA automatically assigns the configured default status.

The user can then change the document status normally.

The system ensures that only one status can be configured as the default.

When a model is applied to a document, the current status of the target document remains unchanged.

---

### Document Models

Allow an existing document to be used as a reusable model for future document entry.

A model is always a real `Document`.

Marking a document as a model only makes it available as a reusable source.

It does not change:

- its normal visibility;
- its financial impact;
- its analytical data;
- its presence in document lists;
- its normal lifecycle.

The `Document` entity supports:

```text
isModel
modelName
```

`modelName` is nullable.

The application exposes:

```php
Document::getModelDisplayName()
```

When an explicit `modelName` exists, it is used.

Otherwise, `getModelDisplayName()` falls back to:

```php
Document::getDisplayName()
```

This keeps model creation lightweight while allowing users to provide clearer reusable names.

Examples:

```text
EDF — Electricity
Orange — Mobile subscription
Adobe — Creative Cloud
Monthly rent
```

---

### Model Management

Models remain normal documents and therefore do not require a separate CRUD or separate domain entity.

A document can be marked or unmarked as a model.

Model configuration is available through:

- EasyAdmin;
- the frontend document edit form.

The configuration allows:

- enabling or disabling model status;
- optionally defining a custom model name.

A dedicated model management interface is not required.

---

### Model Selection

Model selection is available from the frontend document edit workflow.

Only documents explicitly marked as models are displayed in the model selector.

Example:

```text
Document model

[ EDF — Electricity ▼ ] [ Apply ]
```

The model selector uses `getModelDisplayName()`.

Applying a model initializes the target document using reusable information from the source document.

The user remains free to modify every copied value afterward.

Applying a model:

- never modifies the source document;
- never changes the current status of the target document;
- never turns the target document into a model;
- never creates a persistent dependency between the target and source documents.

The model is applied through an explicit POST action protected by CSRF validation.

---

### Model Copy Rules

Applying a model copies reusable business information from the source document.

This includes, where applicable:

- folder;
- document type;
- direction;
- primary third party;
- total amount;
- currency;
- tags;
- notes;
- validity dates;
- analysis allocations;
- analysis dimension assignments;
- third party entries.

Existing reusable collections on the target document are replaced by those from the selected model where appropriate.

Related financial and analytical structures are recreated as new entities associated with the target document.

They never remain linked to the corresponding entities belonging to the source document.

The document status is explicitly excluded from model copying.

The target document keeps its current status, normally initialized from the configured default document status.

---

### Date Copy Rules

Document models provide reusable date patterns rather than blindly copying obsolete absolute dates.

When a model is applied:

```text
issuedAt = current date
```

For date fields whose relative relationship with the model's `issuedAt` can be determined, MOA preserves that relative offset.

In general:

```text
newDate = newIssuedAt + (modelDate - modelIssuedAt)
```

This principle is currently applied where relevant to:

- `validFrom`;
- `validUntil`;
- `ThirdPartyEntry::entryDate`;
- `Analysis::analysisDate`.

Example:

```text
Model issuedAt:    2026-08-01
Model validUntil:  2026-08-31
```

If the model is applied on:

```text
New issuedAt:      2026-09-05
```

the resulting date becomes:

```text
New validUntil:    2026-10-05
```

If the source model has no `issuedAt`, no relative offset can be calculated.

In this situation:

- document validity dates that cannot be calculated remain empty;
- recreated third party entries use the current date where required;
- recreated analysis entries use the current date where required.

If a source date is empty, the corresponding optional date remains empty unless the normal creation rules require a date.

All generated dates remain editable by the user.

---

### Data Not Copied

Occurrence-specific information is not copied from the source document.

This includes at least:

- document identifier;
- document reference;
- uploaded file;
- file metadata specific to the source file;
- creation metadata;
- update metadata;
- document status;
- model status;
- model name.

The document reference is deliberately excluded because it commonly represents an occurrence-specific identifier such as an invoice number.

The target document does not automatically become a model because its source document is a model.

---

### DocumentModelService

Model application logic is centralized in:

```php
DocumentModelService
```

The service is responsible for:

- copying reusable document properties;
- preserving the current document status;
- excluding occurrence-specific information;
- resetting `issuedAt` to the current date;
- calculating relative document dates;
- replacing tags;
- recreating analysis allocations;
- recreating analysis dimension assignments;
- recreating third party entries;
- handling date initialization for recreated related entities.

Controllers, forms and Twig templates do not contain the business logic used to copy model data.

The service is the central place for future model-related behavior.

Future document properties and relations should be integrated into the model workflow through this service rather than through a parallel template entity.

---

### Document Index Integration

Document models remain visible in the normal document index.

A model document is identified with a Font Awesome model icon displayed next to its reference.

Hovering over the icon exposes its model display name.

The document filters include a model filter with three states:

```text
All documents
Models only
Non-models only
```

The model filter integrates with the existing document filters, search and pagination.

Document counts and financial totals respect the active filters, including the model filter.

Empty filtered results are handled safely.

---

### Batch Document Import

The document import workflow supports selecting or dropping multiple PDF files.

Only PDF files are accepted.

Example:

```text
Select 8 PDF files

        ↓

8 independent documents

        ↓

Return to document index

        ↓

Qualification workflow
```

Each imported PDF creates its own independent `Document`.

The batch import reuses the existing document creation and storage architecture rather than introducing a separate document domain workflow.

Each newly created document:

- receives the configured default status;
- stores its own PDF file;
- receives an initial reference derived from the PDF filename;
- remains independently editable.

After the batch operation completes, the user is redirected to the document index.

A success flash reports the number of imported documents.

Example:

```text
8 documents imported.
```

The existing single-file use case remains supported naturally by selecting or dropping one PDF.

---

### Batch Import and Models

Batch import and document models remain deliberately decoupled.

The implemented workflow is:

```text
Multiple PDF files
        ↓
Create independent documents
        ↓
Return to document index
        ↓
Qualify documents individually
        ↓
Apply a model when appropriate
```

A model is not automatically applied to an entire batch.

This keeps the import workflow predictable and avoids introducing unnecessary batch configuration complexity.

Users explicitly choose whether a model should be applied while qualifying each imported document.

---

## Architecture Principles

### Models Are Documents

A document model is not a separate domain entity.

It is a normal document that has been explicitly made reusable.

This avoids maintaining a parallel representation of document metadata and relations.

---

### Explicit User Control

MOA does not automatically decide which model applies to a document.

The user explicitly selects and applies a model.

Automatic model recognition is outside the scope of v0.9.

---

### Reusable Defaults

Model values are starting values.

They remain editable.

A document to which a model has been applied becomes completely independent from the source model.

Changing the source model later never modifies previously created documents.

---

### Status Independence

Document status is not part of model data.

New documents receive their status through the normal document creation workflow, including the configured default status.

Applying a model never replaces the current document status.

---

### Relative Dates

Models provide date patterns rather than absolute dates.

`issuedAt` is initialized using the current date when a model is applied.

Other relevant dates preserve their relative offset from the model's `issuedAt` whenever that offset can be calculated.

This allows recurring documents to inherit useful date relationships without copying obsolete absolute dates.

---

### Centralized Model Logic

Model-copy behavior belongs in `DocumentModelService`.

The service remains independent from the presentation layer.

Future changes to the `Document` domain should require changes primarily in this service rather than duplicated logic across controllers, forms or templates.

---

### Preserve Existing Workflows

Existing document workflows continue to work without using models.

Models are an optional productivity feature.

Batch import complements rather than replaces single-document import.

---

## Non Goals

v0.9 deliberately excludes:

- OCR-based classification;
- automatic model recognition;
- automatic third-party recognition;
- automatic analytical classification;
- AI-assisted classification;
- email ingestion;
- watched folders;
- scheduled imports;
- background document ingestion;
- automatic document matching;
- recurring document generation;
- automatic model application during batch import.

These features may be considered in later versions.

---

## Delivered

### Phase 1 — Default Status

- Added default status support.
- Default status configurable through EasyAdmin.
- New documents automatically receive the default status.
- Only one status can be configured as default.
- Applying a model preserves the target document status.

### Phase 2 — Document Model Metadata

- Added `isModel`.
- Added nullable `modelName`.
- Added `getModelDisplayName()` with fallback to `getDisplayName()`.
- Added model configuration through EasyAdmin.
- Added model configuration to the frontend document edit form.
- Marking a document as a model does not alter its normal behavior.
- Added model identification to the document index.
- Added Models / Non-models filtering.

### Phase 3 — DocumentModelService

- Introduced `DocumentModelService`.
- Reusable document fields are copied centrally.
- Document reference is deliberately not copied.
- Current target status is preserved.
- `issuedAt` is reset to the current date.
- Relative validity dates are calculated from the source `issuedAt`.
- Tags are replaced from the model.
- Analysis allocations are recreated.
- Analysis dimension assignments are recreated.
- Third party entries are recreated.
- Related entry dates are shifted relative to the source model where possible.
- Related required dates fall back to the current date when the source model has no usable `issuedAt`.

### Phase 4 — Model Selection

- Added a dedicated model section to the frontend document edit workflow.
- Only explicitly marked models are available.
- Model names use `getModelDisplayName()`.
- Added explicit Apply action.
- Model application uses a POST route with CSRF protection.
- Model application delegates business logic to `DocumentModelService`.
- Copied values remain editable.
- Source documents remain unchanged.

### Phase 5 — Batch Import

- Added multiple PDF file selection.
- Added multiple PDF drag and drop.
- Each PDF creates an independent document.
- Existing document creation and storage architecture is reused.
- Default status is assigned to every imported document.
- Initial references are derived from filenames.
- User is returned to the document index after import.
- Import result is reported through a success flash.
- Single-PDF import remains supported.
- Frontend assets support multiple file selection and display the selected file count.

---

## Completion Criteria

v0.9 is complete because:

- [x] one status can be configured as the default;
- [x] new documents automatically receive the configured default status;
- [x] applying a model does not change the current document status;
- [x] an existing document can be marked as a model;
- [x] a model can optionally have a custom name;
- [x] `getModelDisplayName()` falls back to `getDisplayName()` when no model name is defined;
- [x] only documents marked as models appear in the model selector;
- [x] marking a document as a model does not change its normal document behavior;
- [x] models remain visible in the normal document index;
- [x] models can be identified from the document index;
- [x] documents can be filtered by model status;
- [x] a model can be explicitly applied from the document edit workflow;
- [x] reusable document information is copied to the target document;
- [x] document reference is not copied from the model;
- [x] document status is not copied from the model;
- [x] analysis allocations are recreated for the target document;
- [x] analysis dimension assignments are recreated for the target document;
- [x] third party entries are recreated for the target document;
- [x] source files and source identifiers are never copied;
- [x] `issuedAt` is initialized with the current date when a model is applied;
- [x] relevant dates preserve their relative offset from the model's `issuedAt` where possible;
- [x] missing source `issuedAt` values are handled safely;
- [x] copied and calculated values remain editable;
- [x] the target document does not automatically become a model;
- [x] the source model remains unchanged;
- [x] previously created documents remain independent from their model;
- [x] multiple PDF files can be imported in a single operation;
- [x] every imported PDF creates an independent document;
- [x] imported documents receive the configured default status;
- [x] batch import returns the user to the document index;
- [x] the number of imported documents is reported to the user;
- [x] single-PDF import continues to work;
- [x] existing document creation continues to work;
- [x] document lists, filtered counts and financial totals remain functional;
- [x] EasyAdmin remains compatible with the updated domain model.

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

- Users;
- Documents;
- Folders;
- Tags;
- Categories;
- Projects;
- Third Parties;
- Bank Accounts;
- Analysis;
- Third Party Entries.

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

- a `Workspace` entity exists;
- existing data belongs to a Workspace;
- the Workspace is resolved automatically for every request;
- repository queries are Workspace-aware;
- a self-hosted installation continues to behave exactly as before using a single default Workspace.