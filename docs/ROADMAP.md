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

`DocumentType` replaces the previously planned `Category` entity. It describes the nature of a document, for example:

- Invoice
- Letter
- Contract
- Other

The reference entities intentionally remain independent even though they initially share a similar structure. They must not inherit from a common entity or be prematurely generalized because their responsibilities may diverge in future versions.

### Identifier strategy

Before generating the entities, validate and document the identifier strategy used by the new domain model:

- auto-increment integer identifiers; or
- ULID identifiers.

The selected strategy must be applied consistently to `Document` and its related entities.

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
- Name validation and case-insensitive uniqueness rules are implemented independently for each entity. Similar logic may initially be duplicated to keep entity rules and repositories independent. No shared abstraction is introduced until a stable common requirement has been identified.
- `color` stores a hexadecimal color value.
- `faIcon` stores a Font Awesome icon name and may be nullable.
- When `faIcon` is null, the application returns an entity-specific default icon defined in code.
- `notes` is a nullable free-text field suitable for longer content.
- No slug, `createdAt`, or `updatedAt` field is added in this version.

### Document structure

The initial `Document` entity contains:

- `id`
- `issuedAt`
- `reference`
- `direction`
- `totalAmount`
- `notes`
- `folder`
- `documentType`
- `status`
- `thirdParty`
- `tags`

Rules:

- `issuedAt` stores the document issue date.
- `reference` stores a title, subject, invoice number, contract number, or another identifying reference.
- `direction` supports the following values:
  - `Incoming`
  - `Outgoing`
  - `Internal`
- `totalAmount` stores the total document amount.
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

Relationship rules:

- The `Folder`, `DocumentType`, `Status`, and `ThirdParty` relationships are nullable.
- Deleting a related reference record must not delete a document.
- The join-table rows linking documents and tags are deleted automatically when either the document or the tag is deleted.
- Deleting a tag must not delete a document.
- Deleting a document must not delete a tag.

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
- Keep EasyAdmin as an advanced maintenance and inspection tool rather than the primary MOA interface.

### Documentation

- Document the initialization command in `docs/`.
- Update the administration documentation when new commands are added.
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
- Validate migrations on both an existing development database and a fresh database.

### Deployment

- Validate the complete v0.3 workflow locally.
- Update the changelog and release documentation.
- Tag the v0.3 release.
- Deploy the tagged release to the DigitalOcean production server.
- Run the documented migrations and initialization command in production.
- Confirm owner access to the administration interface after deployment.

### Success criteria

- The identifier strategy has been explicitly selected and documented.
- A fresh installation can create all required tables through Doctrine migrations.
- The base `Document` entity can be persisted.
- Documents can be associated with a folder, document type, status, third party, and multiple tags.
- All single-reference relationships remain optional.
- Removing a reference entity never removes its related documents.
- Removing a document or tag cleans the corresponding document-tag association rows.
- Reference entity names cannot be duplicated by changing letter case.
- Each reference entity enforces its own name validation and case-insensitive uniqueness rules.
- Entity-specific default Font Awesome icons are returned when no icon is stored.
- A documented command populates the default data.
- The initialization command can be executed several times without creating duplicates.
- Existing values are never deleted or overwritten.
- Default values remain editable by the owner.
- The owner can inspect and manage the initial database structure through EasyAdmin.
- An unauthenticated user cannot access the administration interface.
- A user without `ROLE_OWNER` cannot access the administration interface.
- The first user-facing Wiki pages define the purpose and usage of each core entity.
- The complete release can be installed and validated from a clean tagged version.
- The v0.3 tagged release is successfully deployed to the production server.

### Decisions

#### Identifiers

- `Document`, `Folder`, `DocumentType`, `Status`, `Tag` and `ThirdParty` use Symfony-generated ULIDs.
- `User` keeps its existing identifier strategy.
- 
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

- `Folder`, `DocumentType`, `Status` and `ThirdParty` are linked to `Document` through nullable many-to-one relationships.
- Deleting a reference entity never deletes a document.
- When a reference entity is deleted, the related documents become unassigned.
- `Tag` uses a many-to-many relationship.
- Deleting a tag removes only its associations with documents.

#### Documents and files

- `Document` represents the business document and its classification metadata.
- File storage information is not stored directly in `Document`.
- A document may exist without an attached file.
- Attached files are represented by a separate `DocumentFile` entity.
- One `Document` may have many `DocumentFile` records.
- Deleting a `Document` deletes its related `DocumentFile` records.
- Deleting a `DocumentFile` never deletes its parent `Document`.

#### Document files

- `DocumentFile` represents a physical file attached to a `Document`.
- One `Document` may have many `DocumentFile` records.
- A `DocumentFile` uses a Symfony-generated ULID.
- A `DocumentFile` stores its original name, storage name, MIME type, size and SHA-256 checksum.
- File metadata is stored in PostgreSQL.
- File contents are stored outside the database in a persistent storage directory.
- The storage directory is configured through the application environment.
- The absolute storage path is never persisted for each file.
- The physical storage name is generated by the application and does not reuse the uploaded filename.
- MIME types are detected by the server from the file contents.
- The checksum is used to detect identical files but is not unique by default.
- A `DocumentFile` cannot exist without a parent `Document`.
- Deleting a `Document` deletes its related database records and physical files.
- Deleting a `DocumentFile` never deletes its parent `Document`.

#### File storage

- Files are stored outside the database.
- The storage directory is configured through the application environment.
- The application never stores an absolute file path in the database.
- Physical filenames are generated by the application.
- The storage service organizes files automatically using a two-level directory hierarchy derived from the file ULID.

#### Document file storage naming

- The physical filename is generated from the `DocumentFile` ULID.
- The original uploaded filename is stored separately in `originalName`.
- The physical file extension is preserved when it can be determined safely.
- The relative storage path is derived from the ULID using the two-level directory hierarchy.
- No absolute or relative storage path is persisted in `DocumentFile`.
- Only the storage service is responsible for resolving the physical file location.

#### Stored files and duplicate detection

- `StoredFile` represents a unique physical file managed by MOA.
- `DocumentFile` represents the attachment of a stored file to a document.
- One `StoredFile` may be linked to several documents through separate `DocumentFile` records.
- `StoredFile` stores the generated storage name, MIME type, size, SHA-256 checksum and import date.
- `DocumentFile` stores the original filename used for that attachment.
- When an identical file already exists, MOA reuses the existing `StoredFile` instead of creating another physical copy.
- An identical file may still be attached to another document.
- Duplicate detection does not block legitimate document attachments.
- Deleting a `DocumentFile` removes only the attachment.
- A physical file may be deleted only when its `StoredFile` is no longer referenced by any `DocumentFile`.

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
- `receivedAt` is required and stores the date on which the document was received or recorded in MOA.
- `validFrom` and `validUntil` are optional and define the document validity period.
- An open-ended validity period is represented by a null `validUntil`.
- When both dates are defined, `validUntil` must not be earlier than `validFrom`.
- `reference` is optional and may contain a title, subject, invoice number, contract number, or another identifying reference.
- `reference` is not unique.
- `direction` is required and uses the values `Incoming`, `Outgoing`, and `Internal`.
- `direction` defaults to `Incoming`.
- `totalAmount` is optional and is stored as an integer number of cents.
- The application uses one global currency in v0.3.
- `notes` is optional free text.