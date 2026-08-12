# Services

This document describes the conventions used for implementing services in MOA.

Services are responsible for application logic and business rules. They coordinate repositories, entities and infrastructure services while keeping controllers thin and repositories focused on data access.

---

## Philosophy

Services are the heart of the application layer.

They implement business workflows, enforce business rules and coordinate persistence.

Typical execution flow:

```text
EasyAdmin / Controller
          │
          ▼
       Service
          │
          ▼
    Repository
          │
          ▼
   Doctrine ORM
          │
          ▼
     PostgreSQL
```

Repositories retrieve data.

Entities represent the domain.

Services make decisions.

---

## Responsibilities

A service may:

- validate business rules;
- normalize business data;
- coordinate multiple repositories;
- manage Doctrine transactions;
- orchestrate infrastructure services;
- throw business exceptions;
- persist domain entities.

A service should not:

- render HTML;
- configure EasyAdmin;
- access HTTP requests;
- contain presentation logic.

---

## Typical CRUD Services

Most business entities expose a simple service API.

```php
save(Entity $entity): void

delete(Entity $entity): void
```

The `save()` method is responsible for both creation and update.

The service determines which validations must be performed before persistence.

---

## Business Validation

Simple validation belongs to entities.

Examples include:

- required values;
- string lengths;
- positive amounts;
- date consistency.

Business validation belongs to services.

Typical examples include:

- preventing duplicate relationships;
- protecting referenced entities;
- validating hierarchy rules;
- preventing currency changes after transactions exist;
- reconciliation validation.

Whenever validation requires repository access, it belongs in a service.

---

## Transactions

A service is responsible for transactional operations involving multiple entities.

Example:

```text
Document Import
        │
        ▼
StoredFile
        │
        ▼
DocumentFile
        │
        ▼
Document
```

The complete operation should either succeed or fail as a whole.

---

## Exceptions

Business-rule violations should throw:

```text
BusinessRuleException
```

Examples include:

- deleting an entity that is still referenced;
- invalid reconciliation;
- invalid analytical assignment;
- forbidden currency change.

These exceptions are intended to be displayed to the user.

Unexpected technical failures should use standard PHP exceptions.

---

## Infrastructure Services

Some services implement technical infrastructure rather than business logic.

Examples include:

- StorageService
- MimeTypeService
- HashService

These services should not contain business rules.

Business services coordinate infrastructure services when necessary.

---

## Dependency Injection

Services should use constructor dependency injection exclusively.

Example:

```php
public function __construct(
    private readonly EntityManagerInterface $entityManager,
    private readonly DocumentRepository $documentRepository,
) {
}
```

Dependencies should always be explicit.

---

## Service Design

Business services should generally be declared:

```php
final readonly class ExampleService
```

Constructor injection should be preferred over service locators or static access.

Business logic should remain cohesive and focused on a single responsibility.

---

## Repository Collaboration

Repositories are collaborators of services.

Repositories answer questions.

Services make decisions.

Typical example:

```text
Repository

"Does a reconciliation already exist?"
```

```text
Service

"If yes, reject the operation."
```

This separation keeps business rules independent from persistence.

---

## Future Evolution

As MOA evolves, services will become the primary orchestration layer for features such as:

- bank imports;
- reconciliation assistance;
- budgeting;
- forecasting;
- accounting exports;
- reporting.

The architecture has been designed so that these features can be added without moving business logic into controllers or repositories.

---

## Design Principles

Services follow a few simple principles.

- Keep controllers thin.
- Keep repositories focused on data access.
- Keep entities independent from persistence.
- Centralize business rules.
- Prefer constructor dependency injection.
- Use `final readonly` whenever possible.
- Throw `BusinessRuleException` for expected business failures.
- Keep infrastructure services independent from business logic.
- Favor readability over unnecessary abstraction.
- 