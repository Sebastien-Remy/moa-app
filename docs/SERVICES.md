# Services

This document describes the business services used by MOA.

A service is responsible for coordinating a business operation.

Services contain business rules and orchestrate the work performed by entities, repositories and infrastructure.

Each service must have a single, clearly defined responsibility.

---

## Design principles

- One service, one responsibility.
- Entities represent the business model.
- Repositories retrieve and persist entities.
- Services orchestrate business operations.
- Infrastructure concerns (filesystem, mail, external APIs, etc.) are isolated behind dedicated services.
- Business entities must never manipulate the filesystem directly.
- Services may coordinate database transactions when required.

---

## Storage principles

Physical file storage is considered an infrastructure concern.

The business model never manipulates absolute filesystem paths.

All business services manipulate relative paths only.

Relative paths are part of the business model.

Absolute paths are an infrastructure detail.

`StorageService` is the only service allowed to resolve a relative path into an absolute filesystem path using `DOCUMENT_STORAGE_PATH`.

---

## UserService

### Responsibility

Manage the owner account.

Current responsibilities include:

- creating the first owner account;
- recovering the owner account;
- validating usernames;
- validating passwords;
- ensuring username uniqueness;
- hashing passwords before persistence.

`UserService` is currently the reference implementation for business services in MOA.

---

## Planned services

### DocumentImportService

Coordinates the complete document import pipeline.

Responsibilities include:

- orchestrating the complete import workflow;
- coordinating database transactions;
- delegating physical file management;
- creating documents;
- creating document attachments.

`DocumentImportService` is the single entry point for every document import, regardless of the import source.

---

### StorageService

Responsible for physical file storage.

Responsibilities include:

- storing files at a relative path;
- copying files;
- deleting files;
- resolving absolute paths from relative paths;
- ensuring destination directories exist.

The storage implementation is completely hidden from the business model.

`StorageService` never knows about `Document`, `StoredFile` or `DocumentFile`.

It only manipulates files and filesystem paths.

The service is the only component allowed to know the physical storage location configured through `DOCUMENT_STORAGE_PATH`.

The strategy used to generate storage paths is not the responsibility of `StorageService`.

The same relative path always resolves to the same absolute filesystem path.

For example:

```text
Relative path:
01/K2/01K2ABCDEF1234567890GHJKLM.pdf

Absolute path:
/srv/moa/storage/01/K2/01K2ABCDEF1234567890GHJKLM.pdf
```

---

### Stored file path

A `StoredFile` path is deterministic.

It is always derived from the `StoredFile` identifier and never from the original filename.

The same `StoredFile` always resolves to the same relative storage path.

The relative path is never persisted in the database.

It is calculated whenever needed by applying the project's storage naming strategy.

Example:

```text
StoredFile ID:
01K2ABCDEF1234567890GHJKLM

Relative path:
01/K2/01K2ABCDEF1234567890GHJKLM.pdf
```

---

### StoredFileService

Responsible for managing stored files.

Responsibilities include:

- calculating SHA-256 checksums;
- detecting duplicate files;
- generating storage relative paths;
- creating `StoredFile` records;
- deleting unused stored files.

`StoredFileService` applies the project's storage naming strategy.

It generates the relative path that is passed to `StorageService`, which performs the physical filesystem operations.

---

### DocumentService

Responsible for the document lifecycle.

Responsibilities include:

- creating documents;
- updating document metadata;
- deleting documents.

`DocumentService` manages business operations related to documents only.

---

### DocumentFileService

Responsible for document attachments.

Responsibilities include:

- linking a `StoredFile` to a `Document`;
- removing document attachments;
- coordinating attachment lifecycle.

---

## Dependency flow

```text
DocumentImportService
        │
        ├────────► StoredFileService
        │
        ├────────► StorageService
        │
        ├────────► DocumentService
        │
        └────────► DocumentFileService
```

Business services may collaborate when required.

However, each service remains responsible for a single, clearly identified business capability.