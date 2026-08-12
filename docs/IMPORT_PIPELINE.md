# Import Pipeline

This document describes the standard document import workflow implemented by MOA.

It focuses on the technical execution of a document import rather than the business concepts documented in the GitHub Wiki.

Every document entering the application must follow this pipeline.

The goal is to guarantee consistent document creation, duplicate detection and physical file storage regardless of the import source.

---

# Philosophy

MOA defines a single document import workflow.

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

This guarantees that every imported document follows the same business rules and storage process.

---

# Overall Workflow

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
        ├────────► DocumentFile
        │
        └────────► Doctrine Transaction
```

The interface collects user input.

The form model converts framework-specific objects into an application DTO.

The application service coordinates the complete workflow.

---

# Form Model

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

Application DTOs remain independent from the interface used to create them.

---

# Stored File Resolution

`DocumentImportService` delegates file resolution to `StoredFileService`.

`StoredFileService`:

- validates the source file;
- calculates the SHA-256 checksum;
- searches for an existing `StoredFile`;
- detects the MIME type;
- detects the file extension;
- detects the file size.

If a matching checksum already exists:

- the existing `StoredFile` is reused;
- no new physical file is created.

Otherwise:

- a new `StoredFile` entity is created;
- the deterministic storage path is generated;
- the physical file is stored through `StorageService`.

---

# Deterministic Storage Path

The physical filename never depends on the original filename.

It is generated from:

- the `StoredFile` ULID;
- the normalized file extension.

Example:

```text
StoredFile ID:
01K2ABCDEF1234567890GHJKLM

Relative path:
01/K2/01K2ABCDEF1234567890GHJKLM.pdf
```

The relative path is never stored in the database.

It can always be recalculated from the `StoredFile`.

This guarantees deterministic and stable storage paths.

---

# Document Creation

Once the stored file has been resolved, `DocumentService` creates the corresponding `Document`.

The service currently assigns:

- the issue date;
- the recording date;
- the document direction.

Additional metadata may be completed afterwards by the user.

---

# Document Attachment

The relationship between a `Document` and a `StoredFile` is represented by `DocumentFile`.

The constructor:

- validates the original filename;
- links both entities;
- maintains both entity collections.

Several documents may therefore reference the same physical `StoredFile`.

This enables physical file deduplication while preserving document independence.

---

# Transaction

`DocumentImportService` executes the complete workflow inside a Doctrine transaction.

A successful import creates:

- one `Document`;
- one `DocumentFile`;
- either a reused or a newly created `StoredFile`.

If the transaction fails:

- database changes are rolled back;
- a newly created physical file is deleted;
- a previously existing physical file is never removed.

This behavior is implemented using `StoredFileResolution`.

The database and filesystem therefore remain consistent at all times.

---

# Responsibilities

Each component has a single responsibility.

## DocumentImportService

Coordinates the complete business workflow.

## StoredFileService

Resolves a source file into a `StoredFile`.

## StorageService

Performs physical filesystem operations only.

## DocumentService

Creates and persists the business document.

## DocumentFile

Represents the attachment relationship between a document and a stored file.

No business rule should be duplicated between these components.

---

# EasyAdmin

EasyAdmin is currently the first interface using the standard import pipeline.

The standard **New** action is disabled for documents.

Documents are created exclusively through the dedicated **Import Document** action.

EasyAdmin:

- receives the uploaded file;
- creates `DocumentImportFormData`;
- converts it into `DocumentImportData`;
- calls `DocumentImportService`.

It contains no document import business rules.

---

# Future Import Sources

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

---

# Design Principles

The import pipeline follows a few simple principles.

- Every document follows the same import workflow.
- Interfaces remain thin.
- Application DTOs remain independent from interface frameworks.
- Business rules are centralized in services.
- Physical storage is independent from business data.
- Duplicate files are detected automatically.
- Database and filesystem consistency are guaranteed through transactions.
- New import sources should reuse the existing pipeline.