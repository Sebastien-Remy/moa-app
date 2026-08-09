# Import Pipeline

This document describes the standard document import workflow used by MOA.

Every document entering the application must follow this pipeline.

The goal is to guarantee consistent document creation, duplicate detection and physical file storage regardless of the import source.

---

# Philosophy

MOA supports exactly one document import workflow.

Whether a document comes from:

- EasyAdmin;
- drag and drop;
- a mobile application;
- a command-line tool;
- a future REST API;
- an IMAP mailbox;
- a scanner;
- another application;

the same application workflow must always be executed.

No interface may bypass `DocumentImportService`.

---

# Overall workflow

```text
Interface
        │
        ▼
DocumentImportFormData
        │
        ▼
DocumentImportData
        │
        ▼
DocumentImportService
        │
        ├────────► StoredFileService
        │              │
        │              └────────► StorageService
        │
        ├────────► DocumentService
        │
        └────────► DocumentFile
```

The interface collects user input.

The form model converts framework-specific objects into an application DTO.

The application service coordinates the complete workflow.

---

# Form model

The current EasyAdmin interface uses:

```text
DocumentImportFormData
```

It contains Symfony-specific objects such as:

- `UploadedFile`.

Its responsibility is to convert interface data into:

```text
DocumentImportData
```

The application layer never depends on Symfony form objects.

---

# Application DTO

`DocumentImportData` represents the business intention to import a document.

It currently contains:

- source file path;
- original filename;
- document date;
- document direction.

The same DTO can later be created by:

- EasyAdmin;
- REST APIs;
- command-line tools;
- email importers;
- scanner integrations.

---

# Stored file resolution

`DocumentImportService` delegates file resolution to `StoredFileService`.

`StoredFileService`:

- validates the source file;
- calculates the SHA-256 checksum;
- searches for an existing `StoredFile`;
- detects MIME type;
- detects extension;
- detects file size.

If a matching checksum already exists:

- the existing `StoredFile` is reused;
- no new physical file is created.

Otherwise:

- a new `StoredFile` entity is created;
- the deterministic relative storage path is generated;
- the physical file is stored through `StorageService`.

---

# Deterministic storage path

The physical filename never depends on the original filename.

It is generated from:

- the `StoredFile` ULID;
- the normalized extension.

Example:

```text
StoredFile ID:
01K2ABCDEF1234567890GHJKLM

Relative path:
01/K2/01K2ABCDEF1234567890GHJKLM.pdf
```

The relative path is never stored in the database.

It can always be recalculated from the `StoredFile`.

---

# Document creation

Once the stored file has been resolved, `DocumentService` creates the corresponding `Document`.

The service currently assigns:

- the document date;
- the recording date;
- the document direction.

Additional metadata may be completed afterwards.

---

# Document attachment

The relationship between a `Document` and a `StoredFile` is represented by `DocumentFile`.

The constructor:

- validates the original filename;
- links both entities;
- maintains both entity collections.

Several documents may therefore reference the same physical `StoredFile`.

---

# Transaction

`DocumentImportService` executes the import inside a Doctrine transaction.

A successful import creates:

- one `Document`;
- one `DocumentFile`;
- either a reused or a newly created `StoredFile`.

If the transaction fails:

- database changes are rolled back;
- a newly created physical file is deleted;
- a previously existing physical file is never removed.

This behavior is implemented using `StoredFileResolution`.

---

# Responsibilities

## DocumentImportService

Coordinates the complete business workflow.

## StoredFileService

Resolves a source file into a `StoredFile`.

## StorageService

Performs physical filesystem operations only.

## DocumentService

Creates the document.

## DocumentFile

Represents the attachment relationship.

---

# EasyAdmin

EasyAdmin is currently the first interface using the standard import pipeline.

The standard **New** action is disabled for documents.

Documents are created exclusively through the dedicated **Import document** action.

EasyAdmin:

- receives the uploaded file;
- creates `DocumentImportFormData`;
- converts it into `DocumentImportData`;
- calls `DocumentImportService`.

It contains no document import business rules.

---

# Future extensions

The import pipeline is intentionally independent from the import source.

Future interfaces should simply invoke the existing workflow.

Examples include:

- drag and drop;
- folder import;
- email import;
- scanner import;
- REST API;
- OCR integration;
- AI-assisted document processing.

The business workflow should remain unchanged.