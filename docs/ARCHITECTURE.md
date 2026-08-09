# MOA Architecture

## Mission

MOA is a document-centered business management application.

The document is the central object of the application.

Everything else is built around it.

A document may generate:

- financial categories;
- payments;
- bank reconciliations;
- analytics;
- dashboards.

MOA is not accounting software.

Its goal is to help small businesses organize documents, monitor expenses and revenues, and prepare data for accountants.

---

## Architectural principles

MOA separates:

- user interfaces;
- application use cases;
- the domain model;
- infrastructure.

Each layer has a clearly defined responsibility.

Higher-level business rules must not depend on a specific user interface or infrastructure implementation.

---

## Layers

```text
Interface
    │
    ▼
Application
    │
    ▼
Domain
    │
    ▼
Infrastructure
```

### Interface layer

The interface layer receives user or external input and translates it into application data.

Current and future interfaces include:

- EasyAdmin;
- command-line commands;
- REST APIs;
- email imports;
- scanner imports;
- desktop or mobile applications.

Interfaces must not contain business rules.

They call application services and present their results.

EasyAdmin is the first interface used to exercise the document import workflow, but it is not the primary application architecture.

---

### Application layer

The application layer represents business use cases.

It coordinates domain entities, repositories and infrastructure services.

Examples include:

- `DocumentImportService`;
- `StoredFileService`;
- `DocumentService`;
- `UserService`.

Application services:

- execute a clearly identified use case;
- coordinate transactions when required;
- delegate infrastructure operations;
- return domain entities or explicit result objects;
- remain independent from EasyAdmin and other interfaces.

---

### Data transfer objects

Data transfer objects represent the input required by a use case.

They describe an intention, not a persisted entity.

For example:

```text
DocumentImportData
```

represents a request to import a document.

A DTO may be created by:

- an EasyAdmin form;
- a REST API;
- a command;
- an email importer;
- a scanner integration.

The same DTO can then be passed to the same application service.

DTOs:

- contain no persistence logic;
- contain no filesystem logic;
- remain independent from a specific interface;
- may use readonly properties when appropriate.

---

### Domain layer

The domain layer represents the state and rules of MOA.

Domain entities include:

- `Document`;
- `DocumentFile`;
- `StoredFile`;
- `Folder`;
- `Status`;
- `Tag`;
- `ThirdParty`;
- `User`.

Entities maintain their own invariants and relationships.

They must not:

- manipulate the filesystem;
- read environment variables;
- depend on EasyAdmin;
- coordinate application transactions.

Entities represent what exists in MOA.

DTOs represent what a user or external system wants MOA to do.

---

### Infrastructure layer

The infrastructure layer contains technical implementations.

Examples include:

- Doctrine;
- PostgreSQL;
- the filesystem;
- Docker;
- Nginx;
- future mail or external API adapters.

Infrastructure details must remain hidden from the domain model.

`StorageService` is currently the filesystem infrastructure service used by the document import workflow.

---

## Use-case pattern

Complex business operations use a data object and an application service.

Example:

```text
DocumentImportData
        │
        ▼
DocumentImportService
        │
        ├────────► StoredFileService
        │              └────────► StorageService
        │
        ├────────► DocumentService
        │
        └────────► DocumentFile
```

The DTO carries the input.

The application service coordinates the workflow.

The entities represent the resulting domain state.

The infrastructure services perform technical operations.

This pattern should be used when an operation:

- requires several entities or services;
- must be transactional;
- must be reused by several interfaces;
- contains business rules that do not belong in a controller.

Simple reference-data CRUD operations may continue to use direct EasyAdmin entity management when no separate business use case exists.

---

## Document import workflow

The document import workflow is a reusable application use case.

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
    │              ├────────► checksum calculation
    │              ├────────► duplicate detection
    │              └────────► relative path generation
    │
    ├────────► StorageService
    │              └────────► physical file storage
    │
    ├────────► DocumentService
    │              └────────► document creation
    │
    └────────► DocumentFile
                   └────────► attachment relationship
```

All current and future document import interfaces must use this workflow.

No interface may bypass `DocumentImportService`.

---

## Naming conventions

Entities are named after domain concepts:

```text
Document
StoredFile
DocumentFile
```

DTOs are named after the data required by a use case:

```text
DocumentImportData
```

Application services are named after the use case or business capability they execute:

```text
DocumentImportService
StoredFileService
DocumentService
```

Result objects describe explicit outcomes:

```text
StoredFileResolution
```

---

## Evolution

New abstractions must only be introduced when they solve a current and identified need.

A dedicated service should not be created merely to wrap a constructor or move a single line of code.

Simple solutions are preferred until additional business rules justify a new abstraction.