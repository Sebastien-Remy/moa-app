# Technical Architecture

This document describes the internal technical architecture of MOA.

It complements the higher-level architecture documentation available in the GitHub Wiki.

The Wiki explains the architectural principles of the project.

This document focuses on how those principles are implemented in the source code.

---

## Overview

MOA follows a layered architecture.

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

In the current Symfony application, this is primarily represented by:

```text
EasyAdmin / Forms / Commands
            │
            ▼
         Services
            │
            ▼
       Repositories
            │
            ▼
         Entities
            │
            ▼
          Doctrine
            │
            ▼
        PostgreSQL
```

Each layer has a specific responsibility.

Dependencies should always point toward the application and domain layers, not toward the interface.

---

## Entities

Entities represent the persistent domain model.

They are located in:

```text
src/Entity/
```

Entities contain:

- Doctrine ORM mappings;
- relationships;
- simple invariants;
- Symfony validation constraints;
- normalized setters;
- collection management;
- display helpers such as `getDisplayName()` and `__toString()`.

Entities must not:

- access repositories;
- query the database;
- depend on EasyAdmin;
- implement complex application workflows.

---

## Repositories

Repositories are located in:

```text
src/Repository/
```

Their responsibility is limited to data access.

Typical repository methods include:

- existence checks;
- aggregate queries;
- optimized lookups;
- queries required by business services.

Business rules must not be implemented inside repositories.

For entities using ULID identifiers stored as PostgreSQL UUID values, relation queries should explicitly use Doctrine ULID typing when necessary.

Example:

```php
->andWhere(
    'IDENTITY(entity.bankAccount) = :bankAccountId'
)
->setParameter(
    'bankAccountId',
    $bankAccount->getId(),
    UlidType::NAME,
)
```

This avoids passing the Base32 ULID representation directly to PostgreSQL UUID columns.

---

## Services

Services are located in:

```text
src/Service/
```

They contain application and business logic.

Typical responsibilities include:

- validation;
- normalization;
- consistency checks;
- persistence workflows;
- deletion protection;
- orchestration between repositories and infrastructure services.

Most business services expose a simple API:

```text
save()
delete()
```

Create and update operations generally share the same `save()` method.

Business-rule violations should normally throw:

```text
BusinessRuleException
```

This allows the interface layer to display a clean message without exposing database or infrastructure errors.

---

## CRUD Controllers

EasyAdmin CRUD controllers are located in:

```text
src/Controller/Admin/
```

They are intentionally thin.

Their responsibilities include:

- configuring fields;
- configuring search;
- configuring sorting;
- configuring actions;
- delegating persistence to services.

They must not contain business logic.

Typical flow:

```text
EasyAdmin
    │
    ▼
CRUD Controller
    │
    ▼
Service
    │
    ▼
Repository / EntityManager
```

---

## BaseCrudController

Business CRUD controllers inherit from:

```text
BaseCrudController
```

It centralizes business exception handling.

Typical persistence workflow:

```text
persistEntity()
    │
    ▼
Service::save()

updateEntity()
    │
    ▼
Service::save()

deleteEntity()
    │
    ▼
Service::delete()
```

`BusinessRuleException` is converted into a user-facing EasyAdmin flash message.

Technical CRUD controllers such as stored-file inspection may remain read-only.

---

## Form Models

Form models belong to the interface layer.

They contain data in the format required by a specific interface or framework.

For example:

```text
DocumentImportFormData
```

contains the Symfony `UploadedFile` received by the EasyAdmin form.

Its responsibility is to convert interface-specific data into the application DTO:

```text
DocumentImportFormData
        │
        │ toDocumentImportData()
        ▼
DocumentImportData
```

Form models may depend on framework-specific types.

Application DTOs must remain independent from those frameworks.

The dependency always points from the interface layer toward the application layer.

---

## DTOs

Data Transfer Objects are located in:

```text
src/DTO/
```

DTOs transport data between application components without introducing persistence or framework dependencies.

They should normally be:

- immutable;
- independent from Doctrine;
- independent from EasyAdmin;
- focused on transporting data.

Example:

```text
StoredFileResolution
```

may describe the result of a storage operation without becoming part of the domain model.

---

## Document Storage

MOA separates document metadata from physical file storage.

```text
Document
    │
    ▼
DocumentFile
    │
    ▼
StoredFile
```

Responsibilities are separated between several services.

### DocumentStorageService

Coordinates the document creation workflow.

It may orchestrate:

- document persistence;
- stored-file resolution;
- document-file creation;
- database transaction handling;
- physical-file cleanup after failure.

### StoredFileService

Handles stored-file metadata.

Responsibilities include:

- checksum calculation;
- deduplication;
- MIME type detection;
- extension detection;
- deterministic storage-path generation.

### StorageService

Handles the physical filesystem only.

Responsibilities include:

- storing files;
- deleting files;
- resolving absolute paths;
- checking file existence.

`StorageService` must not contain document business logic.

---

## Storage Deduplication

Physical files are identified using a SHA-256 checksum.

If the same physical file is imported several times, MOA can reuse the existing `StoredFile` instead of storing another copy.

This keeps physical storage independent from document metadata.

---

## Financial Model

Financial values are stored as integer minor units.

Examples:

```text
€10.50  → 1050
$42.00  → 4200
```

Floating point values must not be used for persisted monetary amounts.

Currency precision is defined by the `Currency` entity.

Bank transactions inherit their currency from their bank account.

Reconciliation validates currency compatibility through the service layer.

---

## ULID Identifiers

Domain entities use Symfony ULIDs as identifiers.

Doctrine stores them as UUID-compatible database values.

Typical mapping:

```php
#[ORM\Id]
#[ORM\GeneratedValue(strategy: 'CUSTOM')]
#[ORM\CustomIdGenerator(class: UlidGenerator::class)]
#[ORM\Column(type: 'ulid', unique: true)]
private ?Ulid $id = null;
```

ULIDs provide:

- globally unique identifiers;
- sortable identifiers;
- independence from database-generated integer sequences.

---

## Doctrine Relations

Foreign-key deletion policies must always be explicit.

Typical policies include:

```text
RESTRICT
```

when deleting the referenced entity would invalidate business data.

```text
SET NULL
```

when the related metadata is optional and the primary entity may remain valid without it.

Deletion rules that represent business constraints should also be validated in the service layer so users receive a meaningful error instead of a raw database exception.

---

## Validation

Validation is split between entities and services.

### Entity Validation

Used for local invariants.

Examples:

- required values;
- string lengths;
- positive amounts;
- date consistency;
- amount/currency consistency.

### Service Validation

Used when validation depends on:

- repositories;
- existing database state;
- other entities;
- business workflows.

Examples:

- reconciliation limits;
- duplicate relationships;
- hierarchy cycles;
- protected deletion;
- currency changes after financial activity.

---

## Exceptions

MOA distinguishes between business errors and technical errors.

### BusinessRuleException

Used for expected business-rule violations.

Examples:

- deleting a referenced analytical value;
- changing a bank-account currency after transactions exist;
- over-reconciling a document.

These exceptions are intended to be presented to the user.

### Technical Exceptions

Standard exceptions such as:

```text
RuntimeException
LogicException
```

are used for infrastructure or programming errors.

Examples:

- missing physical files;
- invalid storage state;
- impossible application state.

---

## Static Analysis

MOA uses PHPStan for static analysis.

Current baseline:

```text
PHPStan level 6
0 errors
```

Configuration is stored in:

```text
phpstan.dist.neon
```

Doctrine and Symfony extensions are enabled so PHPStan understands framework-specific behavior.

---

## Technical Documentation

This file documents cross-cutting technical architecture.

More specialized topics are documented separately:

- `SERVICES.md`
- `IMPORT_PIPELINE.md`
- `ROADMAP.md`
- `RELEASES.md`
- `CHANGELOG.md`

User-facing and conceptual documentation is maintained in the GitHub Wiki.

---

## Design Principles

The technical architecture follows a few simple principles.

- Keep responsibilities explicit.
- Keep controllers thin.
- Keep repositories focused on data access.
- Keep business rules in services.
- Keep framework-specific objects outside application DTOs.
- Keep physical storage independent from domain metadata.
- Prefer explicit validation over database failures.
- Prefer simple code over unnecessary abstractions.
- Maintain a clean static-analysis baseline.