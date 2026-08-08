# Import Pipeline

This document describes the standard document import workflow used by MOA.

Every document entering the application must follow this pipeline.

The goal is to guarantee consistent file storage, duplicate detection and document creation regardless of the import source.

## Philosophy

There is only one supported way to import a document into MOA.

Whether the document comes from:

- the web interface;
- drag and drop;
- a mobile application;
- a command line tool;
- a future REST API;
- an IMAP mailbox;
- a scanner;
- another application;

the same import pipeline must always be executed.

No alternative import process should bypass these steps.

## Import workflow

```
User
 │
 ▼
Select file
 │
 ▼
Calculate SHA-256 checksum
 │
 ▼
Search for an existing StoredFile
 │
 ├─────────────── Exists
 │                    │
 │                    ▼
 │           Reuse StoredFile
 │
 └─────────────── Not found
                      │
                      ▼
             Copy file to storage
                      │
                      ▼
             Create StoredFile
                      │
                      ▼
              Create Document
                      │
                      ▼
            Create DocumentFile
                      │
                      ▼
          User completes document metadata
```

## StoredFile lookup

Before copying a file into the storage area, MOA calculates its SHA-256 checksum.

The application searches for an existing `StoredFile` with the same checksum.

If a matching file already exists:

- no physical copy is created;
- the existing `StoredFile` is reused.

Otherwise:

- the uploaded file is copied into the storage directory;
- a new `StoredFile` record is created.

## Document creation

Once the physical file has been resolved, MOA creates the corresponding `Document`.

At this stage the document may still contain only minimal metadata.

The user may complete or modify the document information afterwards.

## DocumentFile creation

A `DocumentFile` links a `Document` to a `StoredFile`.

Several documents may therefore reference the same physical file.

## Transaction

Document creation and database updates should execute inside a database transaction.

Physical file operations are coordinated by the application services.

## Storage

Files are stored outside the database.

The storage location is configured through the application environment.

The storage service is responsible for:

- generating physical filenames;
- organizing the directory hierarchy;
- resolving physical paths;
- copying files;
- deleting files.

Neither `Document` nor `DocumentFile` knows where files are physically stored.

## Future extensions

The import pipeline is intentionally independent from the import source.

Future features should simply invoke this pipeline without modifying its behavior.

Examples include:

- drag and drop;
- folder import;
- email import;
- scanner import;
- REST API;
- OCR integration;
- AI-assisted document processing.