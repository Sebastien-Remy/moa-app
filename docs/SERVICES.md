# Services

This document describes the application services used by MOA.

Application services coordinate business use cases.

They orchestrate domain entities, repositories and infrastructure services while keeping each component focused on a single responsibility.

Services are the entry points of the application layer.

---

# Design principles

Application services:

- implement one clearly identified use case;
- coordinate domain entities;
- coordinate infrastructure services;
- may execute database transactions;
- never contain presentation logic;
- never manipulate HTTP requests or EasyAdmin objects.

Business entities remain responsible for their own invariants.

Infrastructure services remain responsible for technical operations.

---

# Storage principles

Physical file storage is an infrastructure concern.

The business model never manipulates absolute filesystem paths.

Application services manipulate relative storage paths only.

`StorageService` is the only component allowed to resolve:

```text
relative path
        │
        ▼
absolute filesystem path
```

using `DOCUMENT_STORAGE_PATH`.

---

# UserService

## Responsibility

Manage owner accounts.

Current responsibilities include:

- creating the first owner;
- recovering the owner account;
- validating usernames;
- validating passwords;
- hashing passwords;
- ensuring username uniqueness.

---

# StorageService

## Responsibility

Perform physical filesystem operations.

Responsibilities include:

- resolving absolute paths;
- creating storage directories;
- storing files;
- deleting files;
- checking file existence.

`StorageService` never knows about:

- `Document`;
- `StoredFile`;
- `DocumentFile`;
- checksums;
- ULIDs.

It only manipulates filesystem paths.

It never generates storage paths.

---

# Stored file path

A stored file path is deterministic.

It is calculated from:

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

It can always be recalculated.

---

# StoredFileService

## Responsibility

Resolve a source file into a `StoredFile`.

Responsibilities include:

- validating the source file;
- calculating its SHA-256 checksum;
- detecting duplicate files;
- detecting MIME type;
- detecting extension;
- detecting file size;
- creating new `StoredFile` entities;
- generating deterministic storage paths;
- delegating physical storage to `StorageService`.

The service never coordinates the complete document import.

It returns a `StoredFileResolution`.

---

# StoredFileResolution

## Responsibility

Describe the result of resolving a source file.

It contains:

- the resolved `StoredFile`;
- whether it was newly created.

This information allows `DocumentImportService` to safely clean up newly created physical files when a transaction fails.

---

# DocumentService

## Responsibility

Create documents.

Current responsibilities include:

- assigning the document date;
- assigning the recording date;
- assigning the direction;
- persisting the document.

`DocumentService` does not flush database changes.

---

# DocumentImportService

## Responsibility

Coordinate the complete document import workflow.

Responsibilities include:

- receiving `DocumentImportData`;
- resolving the source file;
- creating the document;
- creating the attachment relationship;
- coordinating the Doctrine transaction;
- cleaning up a newly stored physical file if the transaction fails.

`DocumentImportService` is the single entry point for every document import.

No interface may bypass this service.

---

# DocumentFile

A dedicated `DocumentFileService` is currently unnecessary.

The `DocumentFile` constructor already:

- validates the original filename;
- links the `Document`;
- links the `StoredFile`;
- maintains both entity collections.

A dedicated service will only be introduced if attachment management later requires additional business rules.

---

# Dependency flow

```text
Interface
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

Every component owns one clearly identified responsibility.

New services should only be introduced when they represent a genuine business capability rather than simply wrapping existing code.