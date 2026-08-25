# v0.10 — Document Export

**Status:** Planning

## Goal

Allow users to export documents from the document list as a structured ZIP archive.

The export must respect the current document filters and organize exported files by folder with predictable and readable filenames.

---

## Scope

### Export from Document List

Add an export action to the document list.

The export uses the same filters currently applied to the document list.

All documents matching the active filters are exported, regardless of pagination.

When no filter is active, all accessible documents are exported.

---

### ZIP Archive

Generate a ZIP archive containing the exported document files.

Documents are organized into directories based on their associated `Folder`.

Example:

```text
moa-export-2026-08-25.zip
│
├── Purchases/
│   ├── 2026-08-03 - Amazon - 458796.pdf
│   ├── 2026-08-12 - Apple - 45698.pdf
│   └── 2026-08-18 - OVH - FR123456.pdf
│
├── Bank/
│   └── 2026-08-01 - BoursoBank - RELEVE-08-2026.pdf
│
└── Sales/
    └── 2026-08-15 - Client ABC - FAC-2026-014.pdf
```

Documents without a folder are exported into an `Unclassified` directory.

---

### Exported Filename Convention

Generate exported filenames using the following convention:

```text
{documentDate} - {primaryThirdParty} - {reference}.{extension}
```

Example:

```text
2026-08-25 - Apple - 45698.pdf
```

The original file extension is preserved.

Missing optional values such as the primary third party or reference are omitted cleanly from the generated filename.

File and directory names are sanitized to remain compatible with common operating systems.

---

### Duplicate Filenames

Automatically handle filename collisions within the same export directory.

When several documents generate the same filename, append a numeric index:

```text
2026-08-25 - Apple - 45698.pdf
2026-08-25 - Apple - 45698 (2).pdf
2026-08-25 - Apple - 45698 (3).pdf
```

No exported document must overwrite another document.

---

### Filter Consistency

The export must reuse the existing document filtering logic.

Filtering logic must not be duplicated specifically for exports.

The same filtered document query should be usable for:

- paginated document display;
- document export.

Pagination applies only to the document list and must not limit the ZIP export.

---

### Download

Once generated, the ZIP archive is returned as a downloadable file.

The archive filename follows a simple date-based convention:

```text
moa-export-2026-08-25.zip
```

---

## Out of Scope

The following features are not included in v0.10:

- CSV or spreadsheet exports;
- accounting reports;
- document metadata manifests;
- custom export structures;
- user-configurable filename patterns;
- asynchronous or scheduled exports.

These features may be considered in future versions.
