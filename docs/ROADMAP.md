# Roadmap

## v0.3 - Initial Document Structure

### Status

Planning

### Goal

Create the first usable document management structure for a fresh installation, provide useful initial reference data, and expose a basic owner-only administration interface.

### Domain model

Create the base `Document` entity and the first related entities:

- `Folder`
- `DocumentType`
- `Status`
- `Tag`
- `ThirdParty`
- `DocumentFile`
- `StoredFile`

`DocumentType` replaces the previously planned `Category` entity. It describes the nature of a document, for example:

- Invoice
- Letter
- Contract
- Other

The reference entities intentionally remain independent even though they initially share a similar structure. They must not inherit from a common entity or be prematurely generalized because their responsibilities may diverge in future versions.

### Identifier strategy

The new document domain uses Symfony-generated ULIDs.

The following entities use ULIDs:

- `Document`
- `Folder`
- `DocumentType`
- `Status`
- `Tag`
- `ThirdParty`
- `DocumentFile`
- `StoredFile`

The existing `User` entity keeps its current identifier strategy.

### Reference entity structure

The initial structure shared by `Folder`, `DocumentType`, `Status`, `Tag`, and `ThirdParty` is:

- `id`
- `name`
- `color`
- `faIcon`
- `notes`

Rules:

- `name` is required.
- `name` must be unique without regard to letter case.
- Duplicate values such as `Banque`, `BANQUE`, and `banque` must be rejected.
- Name validation and case-insensitive uniqueness rules are implemented independently for each entity.
- Similar logic may initially be duplicated to keep entity rules and repositories independent.
- No shared abstraction is introduced until a stable common requirement has been identified.
- `color` stores an optional hexadecimal color value including the `#` prefix.
- `faIcon` stores an optional Font Awesome icon name without style classes.
- When `faIcon` is null, the application returns an entity-specific default icon defined in code.
- `notes` is a nullable free-text field suitable for longer content.
- No slug, `createdAt`, or `updatedAt` field is added in this version.

### Document structure

The initial `Document` entity contains:

- `id`
- `issuedAt`
- `recordedAt`
- `validFrom`
- `validUntil`
- `reference`
- `direction`
- `totalAmount`
- `notes`
- `folder`
- `documentType`
- `status`
- `thirdParty`
- `tags`
- `documentFiles`

Rules:

- `issuedAt` is required and stores the date shown on the document.
- `recordedAt` is required and stores the date on which the document was recorded in MOA.
- `validFrom` and `validUntil` are optional and define the document validity period.
- An open-ended validity period is represented by a null `validUntil`.
- When both validity dates are defined, `validUntil` must not be earlier than `validFrom`.
- `reference` is optional and may contain a title, subject, invoice number, contract number, or another identifying reference.
- `reference` is not unique.
- `direction` is required and supports:
  - `Incoming`
  - `Outgoing`
  - `Internal`
- `direction` defaults to `Incoming`.
- `totalAmount` is optional and is stored as an integer number of cents.
- Taxes and multiple currencies are out of scope for this version.
- All monetary amounts are assumed to use the same application currency.
- `notes` is a nullable free-text field suitable for longer content.

### Relationships

Define the following relationships:

- Many documents belong to one `Folder`.
- Many documents belong to one `DocumentType`.
- Many documents belong to one `Status`.
- Many documents belong to one `ThirdParty`.
- Many documents may have many `Tag` records.
- One `Document` may have many `DocumentFile` records.
- Each `DocumentFile` belongs to exactly one `Document`.
- Each `DocumentFile` references exactly one `StoredFile`.
- One `StoredFile` may be referenced by many `DocumentFile` records.

Relationship rules:

- The `Folder`, `DocumentType`, `Status`, and `ThirdParty` relationships are nullable.
- Deleting a related reference record must not delete a document.
- When a reference entity is deleted, related documents become unassigned.
- The join-table rows linking documents and tags are deleted automatically when either the document or the tag is deleted.
- Deleting a tag must not delete a document.
- Deleting a document must not delete a tag.
- Deleting a document deletes its related `DocumentFile` records.
- Deleting a `DocumentFile` never deletes its parent `Document`.
- Deleting a `DocumentFile` must not delete a `StoredFile` that remains referenced elsewhere.
- A `StoredFile` and its physical file may be deleted only when no `DocumentFile` references it anymore.

### Initialization

Create an idempotent initialization command that:

- populates useful default folders;
- populates useful default document types;
- populates useful default statuses;
- may populate useful default tags and third parties when justified;
- prevents duplicate seeded values;
- compares names without regard to letter case;
- preserves existing values;
- never deletes or overwrites user data;
- can safely be executed several times;
- leaves all initialized values editable by the owner.

### Administration

- Install and configure EasyAdmin.
- Create an administration dashboard.
- Restrict the administration interface to `ROLE_OWNER`.
- Provide CRUD access to:
  - `Document`
  - `Folder`
  - `DocumentType`
  - `Status`
  - `Tag`
  - `ThirdParty`
  - `DocumentFile`
- Provide safe inspection access to `StoredFile`.
- Keep EasyAdmin as an advanced maintenance and inspection tool rather than the primary MOA interface.
- Do not expose unrestricted physical-file deletion through EasyAdmin.

### Documentation

- Document the initialization command in `docs/`.
- Update the administration documentation when new commands are added.
- Maintain `docs/IMPORT_PIPELINE.md` as the reference document for document imports.
- Maintain `docs/SERVICES.md` as the reference document for service responsibilities.
- Create the first user-facing Wiki pages:
  - Understanding Folders
  - Understanding Document Types
  - Understanding Tags
  - Understanding Third Parties
  - Understanding Statuses
  - Understanding Documents
- Keep technical implementation details in the repository documentation and user-facing concepts in the GitHub Wiki.

### Doctrine and database

- Create the corresponding Doctrine entities and migrations.
- Add database-level constraints required to preserve data integrity.
- Enforce case-insensitive uniqueness for reference entity names.
- Create the document-tag join table with cascade deletion limited to its association rows.
- Create the relationships between `Document`, `DocumentFile`, and `StoredFile`.
- Ensure that a `DocumentFile` cannot exist without both a `Document` and a `StoredFile`.
- Add the database constraints required by the stored-file duplicate-detection policy.
- Validate migrations on both an existing development database and a fresh database.

### Deployment

- Validate the complete v0.3 workflow locally.
- Update the changelog and release documentation.
- Tag the v0.3 release.
- Deploy the tagged release to the DigitalOcean production server.
- Run the documented migrations and initialization command in production.
- Confirm owner access to the administration interface after deployment.

### Success criteria

- The ULID identifier strategy is documented and consistently applied to the new document-domain entities.
- A fresh installation can create all required tables through Doctrine migrations.
- The base `Document` entity can be persisted.
- A `Document` may exist without an attached file.
- Documents can be associated with a folder, document type, status, third party, and multiple tags.
- Documents can reference several stored files through `DocumentFile`.
- One stored file can be attached to several documents without creating another physical copy.
- Identical imported files reuse the existing `StoredFile`.
- All single-reference relationships remain optional.
- Removing a reference entity never removes its related documents.
- Removing a document or tag cleans the corresponding document-tag association rows.
- Deleting one attachment never removes a physical file still used by another document.
- Reference entity names cannot be duplicated by changing letter case.
- Each reference entity enforces its own name validation and case-insensitive uniqueness rules.
- Entity-specific default Font Awesome icons are returned when no icon is stored.
- Physical file locations can be resolved from the `StoredFile` identifier and storage rules.
- Files are distributed through the two-level storage hierarchy.
- A documented command populates the default data.
- The initialization command can be executed several times without creating duplicates.
- Existing values are never deleted or overwritten.
- Default values remain editable by the owner.
- The owner can inspect and manage the initial database structure through EasyAdmin.
- An unauthenticated user cannot access the administration interface.
- A user without `ROLE_OWNER` cannot access the administration interface.
- The first user-facing Wiki pages define the purpose and usage of each core entity.
- The import pipeline provides a single documented entry point for future import sources.
- The complete release can be installed and validated from a clean tagged version.
- The v0.3 tagged release is successfully deployed to the production server.

### Decisions

#### Identifiers

- `Document`, `Folder`, `DocumentType`, `Status`, `Tag`, `ThirdParty`, `DocumentFile`, and `StoredFile` use Symfony-generated ULIDs.
- `User` keeps its existing identifier strategy.

#### Entity mutability

- Business entities are mutable throughout their lifecycle.
- `StoredFile` is immutable once created.
- Creating a different physical file always creates a new `StoredFile`.
- Application services are responsible for enforcing this rule.

#### Folder

- `Folder` uses a Symfony-generated ULID.
- `name` is required, trimmed and unique without regard to letter case.
- `color` stores an optional hexadecimal value including the `#` prefix.
- `faIcon` stores an optional Font Awesome icon name without style classes.
- `faIcon` is nullable. When no icon is stored, the application returns the default `Folder` icon defined in code.
- `notes` is optional free text.
- Deleting a folder never deletes its documents.
- Documents linked to a deleted folder become unassigned.

#### Reference entities

- `Folder`, `DocumentType`, `Status`, and `ThirdParty` are linked to `Document` through nullable many-to-one relationships.
- Deleting a reference entity never deletes a document.
- When a reference entity is deleted, the related documents become unassigned.
- `Tag` uses a many-to-many relationship.
- Deleting a tag removes only its associations with documents.

#### Documents and files

- `Document` represents the business document and its classification metadata.
- File storage information is not stored directly in `Document`.
- A document may exist without an attached file.
- Attached files are represented by `DocumentFile` records.
- One `Document` may have many `DocumentFile` records.
- Deleting a `Document` deletes its related `DocumentFile` records.
- Deleting a `DocumentFile` never deletes its parent `Document`.

#### Document files

- `DocumentFile` represents the attachment of a `StoredFile` to a `Document`.
- A `DocumentFile` uses a Symfony-generated ULID.
- A `DocumentFile` belongs to exactly one `Document`.
- A `DocumentFile` references exactly one `StoredFile`.
- `DocumentFile` stores the original filename associated with the attachment.
- One `Document` may have many `DocumentFile` records.
- One `StoredFile` may be referenced by many `DocumentFile` records.
- Deleting a `Document` deletes its related `DocumentFile` records.
- Deleting a `DocumentFile` never deletes its parent `Document`.
- Deleting a `DocumentFile` does not delete a `StoredFile` that remains referenced elsewhere.
- `DocumentFile` may be created and deleted during the document lifecycle.
- `StoredFile` remains immutable once created.
- 
#### Stored files and duplicate detection

- `StoredFile` represents a unique physical file managed by MOA.
- `StoredFile` uses a Symfony-generated ULID.
- `StoredFile` represents an immutable physical file managed by MOA.
- A `StoredFile` is fully initialized when it is created.
- After creation, its MIME type, extension, size, checksum, and import date never change.
- `StoredFile` stores the MIME type, normalized extension, size, SHA-256 checksum, and import date.- `DocumentFile` stores the original filename used for each attachment.
- When an identical file already exists, MOA reuses the existing `StoredFile` instead of creating another physical copy.
- An identical file may still be attached to another document.
- Duplicate detection does not block legitimate document attachments.
- Deleting a `DocumentFile` removes only the attachment.
- A `StoredFile` and its physical file may be deleted only when no `DocumentFile` references it anymore.
- Physical deletion is coordinated by an application service and is never performed directly by a Doctrine entity.

#### File storage

- Files are stored outside the database.
- File metadata is stored in PostgreSQL.
- The storage directory is configured through the application environment.
- The application never stores an absolute file path in the database.
- Physical filenames are generated by the application.
- The storage service organizes files automatically using a two-level directory hierarchy derived from the `StoredFile` ULID.
- Only the storage service is responsible for resolving physical file locations.

#### Stored file naming

- The physical filename is generated from the `StoredFile` ULID.
- The original uploaded filename is stored separately in `DocumentFile.originalName`.
- The physical file extension is preserved when it can be determined safely.
- The relative storage path is derived from the `StoredFile` ULID using the two-level directory hierarchy.
- No absolute or relative storage path is persisted in `StoredFile`.
- The storage service is the only component responsible for building the physical path.

#### Stored file extension

- `StoredFile` stores a nullable normalized file extension without the leading dot.
- Examples: `pdf`, `jpg`, `xml`.
- The extension is determined by the server during import.
- User-provided extensions are never trusted without verification.
- When no safe extension can be determined, `extension` remains null.
- The physical filename is the `StoredFile` ULID followed by the extension when one is available.

#### File maintenance

- File maintenance is handled by dedicated application services and console commands.
- A future administrator command may detect:
  - unreferenced `StoredFile` records;
  - missing physical files;
  - orphaned physical files;
  - checksum inconsistencies.
- Destructive cleanup operations must never run automatically without an explicit administrator action.
- Maintenance commands are intended to be executed directly on the server, similarly to the owner management commands.

#### Document fields

- `Document` uses a Symfony-generated ULID.
- `issuedAt` is required and stores the date shown on the document.
- `recordedAt` is required and stores the date on which the document was recorded in MOA.
- `validFrom` and `validUntil` are optional and define the document validity period.
- `reference` is optional and may contain a title, subject, invoice number, contract number, or another identifying reference.
- `reference` is not unique.
- `direction` is required and uses the values `Incoming`, `Outgoing`, and `Internal`.
- `direction` defaults to `Incoming`.
- `totalAmount` is optional and is stored as an integer number of cents.
- The application uses one global currency in v0.3.
- `notes` is optional free text.

#### Document lifecycle

- Documents may be updated after import.
- All document metadata remains editable.
- Documents may be deleted by the user.
- No recycle bin is provided in v0.3.
- Document archiving is handled through configurable document statuses.
- A document may replace one attached file with another.
- File replacement creates a new attachment rather than modifying an existing stored file.
- The initial v0.3 user interface does not expose file replacement.

#### Import transactions

- Every import must use the shared import pipeline.
- Database operations are coordinated inside a Doctrine transaction.
- If a database operation fails, all database changes are rolled back.
- If a new physical file was copied before a later failure, the application service attempts to remove it.
- Physical file operations are coordinated by application services because the filesystem cannot participate in the database transaction.
- The import pipeline is responsible for creating `StoredFile` instances.
- Application code must never modify an existing `StoredFile`.

#### Default data localization

- Default reference data is initially stored in English.
- Initialization definitions use stable internal keys such as `purchases`, `sales`, and `draft`.
- User-created and user-renamed values are never translated automatically.
- A dedicated persistent code or translation model may be introduced before v1 multilingual support.
- The v0.3 data model remains unchanged.