# v0.9 — Assisted Document Entry

**Status:** Planning

## Goal

Improve document entry in MOA by reducing repetitive work and making recurring and batch document imports faster.

This version introduces three complementary improvements:

- configurable default document status;
- reusable document models based on existing documents;
- batch file import.

The objective is to accelerate document entry while keeping the workflow explicit, predictable and fully controlled by the user.

---

## Scope

### Default Document Status

Allow one `Status` to be configured as the default status for newly created documents.

The default status is managed only from EasyAdmin.

When a new document is created, MOA automatically assigns the configured default status.

The user can then change the document status normally.

The system must ensure that only one status can be configured as the default.

When a model is applied to a new document, the current status of the new document remains unchanged.

---

### Document Models

Allow an existing document to be used as a reusable model for future document creation.

A model is always a real `Document`.

Marking a document as a model only makes it available as a reusable source during document creation.

It does not change:

- its normal visibility;
- its financial impact;
- its analytical data;
- its presence in document lists;
- its normal lifecycle.

The `Document` entity should support:

```text
isModel
modelName
```

`modelName` is nullable.

When no explicit model name is provided, the application should use the document display name returned by:

```php
Document::getDisplayName()
```

This keeps model creation lightweight while allowing users to provide clearer names when needed.

Examples:

```text
EDF — Electricity
Orange — Mobile subscription
Adobe — Creative Cloud
Monthly rent
```

---

### Model Selection

Add an optional model selector to the document creation workflow.

Only documents explicitly marked as models are displayed.

Example:

```text
Model
[ EDF — Electricity ▼ ]
```

Selecting a model initializes the new document using the reusable information of the source document.

The user remains free to modify every copied value before saving.

Selecting a model must never modify the source document.

The status already assigned to the new document must remain unchanged when a model is applied.

---

### Model Copy Rules

Creating a new document from a model must copy reusable business information from the source document.

This includes, where applicable:

- folder;
- document type;
- direction;
- primary third party;
- currency;
- tags;
- other reusable document metadata;
- analysis allocations;
- analysis dimension assignments;
- third party entries.

The document status is explicitly excluded from model copying.

The new document keeps its current status, normally initialized from the configured default document status.

Related financial and analytical structures should be recreated as new entities associated with the new document.

They must never remain linked to the source document.

---

### Date Copy Rules

Document dates require specific handling when a model is applied.

`receivedAt` and `issuedAt` are reset to the current date.

Other dates should preserve their relative offset from `issuedAt` in the source model.

For example, if the model contains:

```text
issuedAt:  2026-08-01
dueDate:   2026-08-31
```

and the new document is created on:

```text
issuedAt:  2026-09-05
```

the resulting date should initially be:

```text
dueDate:   2026-10-05
```

The same principle should apply to other relevant dates linked to the document lifecycle.

In general:

```text
newDate = newIssuedAt + (modelDate - modelIssuedAt)
```

This provides useful initial values while keeping them predictable.

All generated dates remain editable by the user before saving.

If a source date is empty, the corresponding date should remain empty unless the normal document creation rules specify otherwise.

---

### Data Not Copied

Occurrence-specific information must not be copied from the source document.

This includes at least:

- document identifier;
- uploaded file;
- file metadata specific to the source file;
- creation metadata;
- update metadata;
- model status;
- model name.

The newly created document must not automatically become a model because its source document is a model.

---

### DocumentModelService

Introduce a dedicated application service:

```php
DocumentModelService
```

The service is responsible for applying a document model to a new document.

Its responsibilities should include:

- copying reusable document properties;
- preserving the current document status;
- recreating related analysis data;
- recreating analysis dimension assignments;
- recreating third party entries;
- resetting occurrence-specific information;
- initializing `receivedAt` and `issuedAt`;
- calculating relative dates from the source model;
- keeping model-copy logic outside controllers and forms.

Controllers, forms and Twig templates must not contain the business logic used to apply model data.

The service should become the central place for future model-related behavior.

This is especially important because the `Document` domain is still evolving.

Future document properties and relations should be integrated into the model workflow through this service rather than through a parallel template entity.

---

### Model Management

Models remain normal documents and therefore do not require a separate CRUD.

A document can be marked or unmarked as a model.

The model configuration should allow:

- enabling or disabling model status;
- optionally defining a custom model name.

Initial model management may remain available through EasyAdmin if this keeps the first implementation simple.

A dedicated frontend model management interface is not required for v0.9.

---

### Batch Document Import

Allow users to select or drop multiple files during document import.

Each file creates its own independent `Document`.

Example:

```text
Select 8 files

        ↓

8 document entries

        ↓

Qualification workflow
```

The existing single-document import must remain available.

Batch import must reuse the existing document creation architecture as much as possible rather than introducing a separate document domain workflow.

---

### Batch Import and Models

Document models should integrate naturally with batch import.

The workflow should allow repetitive metadata entry to be reduced when several similar documents are imported together.

Potential interactions include:

```text
Multiple files
      ↓
Select model
      ↓
Apply reusable information
      ↓
Review each document
```

or:

```text
Multiple files
      ↓
Create documents
      ↓
Qualify documents individually
      ↓
Select a model when appropriate
```

The exact user interface should be decided during implementation.

The priority is to keep batch import understandable and avoid adding unnecessary complexity.

---

## Architecture Principles

### Models Are Documents

A document model is not a separate domain entity.

It is a normal document that has been explicitly made reusable.

This avoids maintaining a parallel representation of document metadata and relations.

---

### Explicit User Control

MOA must not automatically decide which model applies to a document.

The user explicitly selects a model.

Automatic model recognition is outside the scope of v0.9.

---

### Reusable Defaults

Model values are starting values.

They must remain editable.

A document created from a model becomes completely independent from the source model.

Changing the source model later must never modify previously created documents.

---

### Status Independence

Document status is not part of the model data.

New documents receive their status through the normal document creation workflow, including the configured default status.

Applying a model must never replace the current document status.

---

### Relative Dates

Models provide date patterns rather than absolute dates.

`receivedAt` and `issuedAt` are initialized using the current date.

Other relevant dates preserve their relative offset from the model's `issuedAt`.

This allows recurring documents to inherit useful date relationships without copying obsolete absolute dates.

---

### Centralized Model Logic

Model-copy behavior belongs in `DocumentModelService`.

The service must remain independent from the presentation layer.

Future changes to the `Document` domain should require changes primarily in this service rather than duplicated logic across controllers, forms or templates.

---

### Preserve Existing Workflows

Existing document creation and import workflows must continue to work without using models.

Models are an optional productivity feature.

Batch import must complement rather than replace single-document import.

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
- recurring document generation.

These features may be considered in later versions.

---

## Suggested Implementation Order

### Phase 1 — Default Status

- Add default status support.
- Configure it through EasyAdmin.
- Apply it during normal document creation.
- Ensure only one status can be the default.

### Phase 2 — Document Model Metadata

- Add `isModel`.
- Add nullable `modelName`.
- Use `getDisplayName()` when `modelName` is empty.
- Allow model configuration through EasyAdmin.
- Ensure marking a document as a model does not affect its normal behavior.

### Phase 3 — DocumentModelService

- Introduce `DocumentModelService`.
- Copy reusable document fields.
- Preserve the current document status.
- Reset occurrence-specific fields.
- Reset `receivedAt` and `issuedAt` to the current date.
- Calculate other dates using their relative offset from the model's `issuedAt`.
- Recreate analysis allocations.
- Recreate analysis dimension assignments.
- Recreate third party entries.

### Phase 4 — Model Selection

- Add model selection to document creation.
- Display only documents explicitly marked as models.
- Use `modelName` or fall back to `getDisplayName()`.
- Apply the selected model through `DocumentModelService`.
- Keep all copied values editable.
- Validate the workflow with recurring real-world documents.

### Phase 5 — Batch Import

- Allow multiple files to be selected or dropped.
- Create independent documents for every file.
- Reuse the existing document creation workflow.
- Integrate document models where useful.
- Preserve single-document import behavior.

---

## Completion Criteria

v0.9 can be considered complete when:

- one status can be configured as the default;
- new documents automatically receive the configured default status;
- applying a model does not change the current document status;
- an existing document can be marked as a model;
- a model can optionally have a custom name;
- `getDisplayName()` is used when no model name is defined;
- only documents marked as models appear in the model selector;
- marking a document as a model does not change its normal document behavior;
- a model can be selected during document creation;
- reusable document information is copied to the new document;
- analysis allocations are recreated for the new document;
- analysis dimension assignments are recreated for the new document;
- third party entries are recreated for the new document;
- source files and source identifiers are never copied;
- `receivedAt` and `issuedAt` are initialized with the current date;
- other relevant dates preserve their relative offset from the model's `issuedAt`;
- copied and calculated values remain editable;
- the newly created document does not automatically become a model;
- the source model remains unchanged;
- previously created documents remain independent from their model;
- multiple files can be imported in a single operation;
- every imported file creates an independent document;
- existing single-document creation continues to work;
- existing document lists, totals and analyses remain unchanged;
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