# Roadmap

## v0.4 — First Document Import

### Objective

Allow the owner to create the first complete MOA document from EasyAdmin by uploading a file.

This release introduces the first operational implementation of the standard document import pipeline.

EasyAdmin is only the first import interface. The import logic must remain independent from EasyAdmin so that future interfaces can reuse the same workflow.

---

## Business need

The document structure introduced in v0.3 can currently be managed through EasyAdmin, but no physical file can yet be imported through the complete MOA workflow.

The owner must be able to:

1. select a file from the document creation form;
2. create a document with basic metadata;
3. store the physical file outside the database;
4. create or reuse the corresponding `StoredFile`;
5. link the stored file to the document through `DocumentFile`.

---

## Architecture

Every file imported from EasyAdmin must follow the standard import pipeline:

```text
EasyAdmin
    │
    ▼
DocumentImportService
    │
    ├────────► StoredFileService
    │              │
    │              ├────────► checksum calculation
    │              └────────► duplicate detection
    │
    ├────────► StorageService
    │              └────────► physical file storage
    │
    ├────────► DocumentService
    │              └────────► document creation
    │
    └────────► DocumentFileService
                   └────────► attachment creation
```

EasyAdmin must not:

- calculate checksums;
- generate storage filenames;
- copy files directly into permanent storage;
- create `StoredFile` records directly;
- create `DocumentFile` records directly;
- contain document import business rules.

The entities must not manipulate the filesystem.

---

## Milestone 1 — Storage configuration

### Goal

Define and configure the permanent document storage location.

### Planned work

- add an environment variable for the document storage directory;
- expose the configured directory through Symfony dependency injection;
- ensure the storage directory is outside the public web directory;
- ensure business data is not stored inside a container;
- document the development and production storage configuration.

### Success criteria

- the storage path can be configured without modifying the source code;
- the application can resolve the configured storage directory;
- imported files persist when containers are recreated;
- files cannot be accessed directly through a public URL.

### Out of scope

- cloud storage;
- S3-compatible storage;
- multiple storage backends;
- storage quotas;
- file encryption.

---

## Milestone 2 — StorageService

### Goal

Create the infrastructure service responsible for physical file storage.

### Planned work

- generate an internal physical filename;
- organize the storage directory hierarchy;
- copy an uploaded file into permanent storage;
- resolve the absolute path of a stored file;
- delete a physical file when explicitly requested by the application;
- report storage failures without modifying business entities.

### Success criteria

- an imported file can be copied into permanent storage;
- the original client filename is not used as the physical filename;
- the service returns the information required to create a `StoredFile`;
- no entity contains filesystem logic.

### Out of scope

- automatic deletion of unused files;
- file previews;
- thumbnails;
- antivirus scanning;
- OCR.

---

## Milestone 3 — StoredFileService

### Goal

Resolve the physical file associated with an import.

### Planned work

- calculate the SHA-256 checksum of the uploaded file;
- search for an existing `StoredFile` using the checksum;
- reuse the existing `StoredFile` when a duplicate is found;
- otherwise delegate physical storage to `StorageService`;
- create the new `StoredFile` record.

### Success criteria

- importing the same file twice does not create a second physical copy;
- duplicate detection is based on the SHA-256 checksum;
- a new `StoredFile` is created only when no matching checksum exists;
- the original filename and relevant file metadata are preserved in the database.

### Out of scope

- perceptual duplicate detection;
- duplicate detection based only on filenames;
- comparison of document contents;
- user-facing duplicate warnings.

---

## Milestone 4 — Document creation services

### Goal

Create documents and attachments through dedicated business services.

### Planned work

- implement the minimal document creation operation in `DocumentService`;
- implement attachment creation in `DocumentFileService`;
- define the minimal metadata required to create a document;
- keep entity persistence rules outside EasyAdmin controllers.

### Success criteria

- `DocumentService` can create a valid document from explicit input;
- `DocumentFileService` can link a `Document` to a `StoredFile`;
- the attachment relationship is represented by `DocumentFile`;
- neither service performs direct filesystem operations.

### Out of scope

- complete document lifecycle management;
- attachment removal;
- document deletion;
- attachment ordering;
- multiple uploaded files in a single import.

---

## Milestone 5 — DocumentImportService

### Goal

Implement the single entry point for document imports.

### Planned work

- receive the uploaded file and basic document metadata;
- coordinate checksum calculation and duplicate detection;
- coordinate permanent file storage;
- create the document;
- create the document attachment;
- execute database changes inside a transaction;
- coordinate cleanup when an import fails.

### Success criteria

A successful import creates or reuses:

- one `StoredFile`;
- one `Document`;
- one `DocumentFile`;
- one physical file only when the checksum is new.

A failed import must not leave:

- a partially created document;
- an orphaned `DocumentFile`;
- an inconsistent `StoredFile`;
- an unmanaged physical file created by the failed operation.

### Out of scope

- asynchronous imports;
- queues;
- background workers;
- bulk imports;
- external import sources.

---

## Milestone 6 — EasyAdmin integration

### Goal

Use EasyAdmin as the first interface for the standard import pipeline.

### Planned work

- add a file upload field to the document creation workflow;
- collect the basic document metadata already supported by the domain;
- pass the uploaded file and metadata to `DocumentImportService`;
- prevent the default EasyAdmin persistence workflow from bypassing the import service;
- display a clear success or failure message;
- keep the existing document administration interfaces basic.

### Success criteria

From EasyAdmin, the owner can:

1. open the document creation form;
2. select one local file;
3. complete the required basic metadata;
4. submit the form;
5. find the new document in the document list;
6. find its associated `DocumentFile`;
7. verify that its `StoredFile` exists;
8. verify that the physical file exists in permanent storage.

### Out of scope

- a dedicated document import page;
- drag and drop;
- upload progress;
- multiple attachments;
- file replacement;
- inline document preview;
- public document download;
- user-facing document management screens.

---

## Milestone 7 — Validation

### Goal

Validate the complete first document import workflow.

### Required scenarios

#### New file

- import a file that does not yet exist;
- verify that one physical file is created;
- verify that one `StoredFile` is created;
- verify that one `Document` is created;
- verify that one `DocumentFile` is created.

#### Duplicate file

- import the same physical file a second time;
- verify that no second physical copy is created;
- verify that the existing `StoredFile` is reused;
- verify that a new `Document` and `DocumentFile` can reference it.

#### Failed import

- simulate or trigger a storage or persistence failure;
- verify that the database remains consistent;
- verify that no unmanaged physical file remains.

#### Production persistence

- deploy the tagged release on the production server;
- import a test document;
- recreate or restart the containers;
- verify that the imported file remains available.

### Success criteria

The release is complete when:

- all required scenarios have been validated;
- the documentation has been updated;
- the installation and deployment instructions have been followed successfully;
- the production server runs the tagged v0.4 release.

---

## Documentation updates

The implementation of v0.4 must update:

- `ROADMAP.md`;
- `SERVICES.md`;
- `IMPORT_PIPELINE.md` if implementation details clarify the existing workflow;
- `INSTALLATION.md` for storage configuration;
- `ADMINISTRATION.md` for storage-related operational checks;
- `.env.example`;
- `RELEASES.md`;
- `CHANGELOG.md` after the work is completed.

---

## Release boundaries

v0.4 delivers the smallest complete and reusable document import workflow.

The following features are intentionally deferred:

- multiple files per document;
- document preview;
- document download;
- document deletion and file cleanup;
- attachment management;
- drag and drop;
- folder imports;
- email imports;
- scanner imports;
- REST API imports;
- OCR;
- AI-assisted processing;
- dedicated end-user interface.