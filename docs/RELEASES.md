# Releases

# v0.6.0

## Overview

Version 0.6 establishes the financial foundations of MOA.

This release introduces the complete financial domain model together with a major architectural cleanup across the project. The codebase now follows a consistent Entity → Repository → Service → EasyAdmin architecture, providing a solid foundation for future accounting, reconciliation and analytical features.

---

## Added

### Financial model

- Currency management (ISO 4217)
- Bank accounts
- Bank transactions
- Document reconciliation
- Analytical categories
- Analytical dimensions
- Analytical dimension values
- Analytical assignments
- Financial analyses
- Multi-dimensional analytical model

### Business rules

- Partial reconciliation
- Split reconciliation
- Currency consistency validation
- Remaining amount validation
- Duplicate reconciliation prevention
- Default currency management
- Financial integrity validation

### Administration

- Complete EasyAdmin support for the financial model
- Dedicated CRUD controllers
- Money formatting
- Read-only technical administration for stored files
- Improved search, sorting and detail pages

---

## Changed

### Architecture

- Introduced a service-oriented business layer.
- Centralized business rules into dedicated services.
- Unified CRUD controllers through `BaseCrudController`.
- Standardized create, update and delete workflows.

### Doctrine

- Reviewed entity mappings.
- Improved indexes.
- Reviewed foreign key deletion policies.
- Harmonized ULID handling.
- Improved repository queries.

### Entities

- Consistent `getDisplayName()` implementations.
- Consistent `__toString()` implementations.
- Improved validation callbacks.
- Normalized setters.
- Improved collection handling.

### Code quality

- Repository cleanup.
- Service cleanup.
- EasyAdmin cleanup.
- Entity cleanup.
- PHPStan level 6 with zero errors.
- Symfony container validation.
- Doctrine schema validation.

---

## Internal improvements

- Consistent coding conventions.
- Improved project structure.
- Reduced duplicated business logic.
- Better separation of responsibilities.
- Improved maintainability.

---

## Ready for

The project is now ready to support future developments including:

- Bank statement imports
- Assisted reconciliation
- Budgeting
- Forecasting
- Accounting exports
- Third-party reporting
- Profitability analysis

---

## v0.5.1

### Name

Reverse Proxy Support

### Status

Released

### Highlights

- Proper reverse proxy support for Docker deployments
- Trusted proxy configuration
- Correct HTTPS URL generation behind Nginx
- Fixed mixed HTTP/HTTPS links in EasyAdmin
- Fixed secure document deletion in Safari

---

## v0.5.0

### Name

Complete Document Management

### Status

Released

### Highlights

- Complete EasyAdmin document management
- Integrated document creation
- Document detail page
- Technical CRUDs for DocumentFile and StoredFile
- Secure document preview
- Open original file action
- Document storage refactoring
- Recorded timestamps with time support
- Production-ready document management workflow

## v0.4.0

### Name

First Document Import

### Status

Released

### Highlights

- First complete document import workflow
- Dedicated EasyAdmin document import interface
- Standard document import pipeline
- Configurable persistent document storage
- Physical file storage outside the database
- `StorageService`
- `StoredFileService`
- `StoredFileResolution`
- `DocumentService`
- `DocumentImportService`
- `DocumentImportData`
- `DocumentImportFormData`
- Deterministic storage paths based on ULIDs
- SHA-256 duplicate detection
- Stored file deduplication
- Transactional document import
- Automatic cleanup after failed imports
- First production-ready document attachment workflow

## v0.3.0

### Name

Initial Document Structure

### Status

Released

### Highlights

- Initial document domain
- ULID identifiers
- EasyAdmin dashboard
- CRUD interfaces for reference entities
- Default initialization command
- Initial GitHub Wiki
- Production deployment validated

---

## v0.2.1

### Name

Server Setup

### Status

Released

### Highlights

- Complete environment template
- Secure production secret generation
- Documented server installation procedure
- Production environment variables injected into PHP
- Fresh installation validated on a production server

---

## v0.2.0

### Name

Authentication

### Status

Released

### Highlights

- User authentication
- Owner account
- Login / Logout
- Protected application
- Owner creation command
- Owner account recovery command

---

## v0.1.1

### Name

Production Deployment

### Status

Released

---

## v0.1

### Name

Foundation

### Status

Released