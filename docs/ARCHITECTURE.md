### Form models

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