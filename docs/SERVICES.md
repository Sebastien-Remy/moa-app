# Services

This document describes the business services used by MOA.

A service is responsible for coordinating a business operation.

Services contain business rules and orchestrate the work performed by entities, repositories and infrastructure.

Services should remain focused on a single responsibility.

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

## UserService

**Responsibility**

Manage the owner account.

Current responsibilities:

- create the first owner account;
- recover the owner account;
- validate usernames;
- validate passwords;
- ensure username uniqueness;
- hash passwords before persistence.

`UserService` is currently the reference implementation for business services in MOA.

---

## Planned services

### DocumentImportService

Coordinates the complete document import pipeline.

Responsibilities include:

- orchestrating the import workflow;
- coordinating transactions;
- creating documents;
- creating document attachments;
- delegating file storage.

---

### StorageService

Responsible for physical file storage.

Responsibilities include:

- generating storage filenames;
- organizing the storage directory hierarchy;
- copying files;
- deleting files;
- resolving physical paths.

The storage implementation is hidden from the business model.

---

### StoredFileService

Responsible for managing physical files.

Responsibilities include:

- checksum calculation;
- duplicate detection;
- creation of `StoredFile` records;
- deletion of unused stored files.

---

### DocumentService

Responsible for document lifecycle.

Responsibilities include:

- creating documents;
- updating document metadata;
- deleting documents.

---

### DocumentFileService

Responsible for document attachments.

Responsibilities include:

- linking a `StoredFile` to a `Document`;
- removing document attachments;
- coordinating attachment lifecycle.

---

## Dependency flow

```
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

Business services may collaborate with one another when required.

However, each service remains responsible for a clearly identified business capability.