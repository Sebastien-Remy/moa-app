# MOA

**MOA (My Office Assistant)** is an open-source, document-centered business management application for small organizations.

MOA helps organize documents, financial information and analytical data while remaining independent from traditional accounting software.

The document remains the central business object of the application.

Around each document, MOA can manage:

- financial information;
- bank transactions;
- reconciliation;
- analytical categories;
- analytical dimensions;
- third parties;
- document storage and classification.

MOA is **not** accounting software.

Its purpose is to organize business information, monitor expenses and revenues, and prepare structured data for reporting, analysis and accounting workflows.

---

## Current Status

Current stable milestone:

**v0.6 — Financial Core**

This version establishes the financial and architectural foundations of MOA, including:

- bank accounts and bank transactions;
- document reconciliation;
- analytical accounting;
- hierarchical categories;
- analytical dimensions;
- service-oriented business rules;
- EasyAdmin administration;
- Doctrine ORM with PostgreSQL;
- ULID identifiers;
- PHPStan level 6 with zero errors.

Future releases will build on this foundation with bank imports, reconciliation assistance, budgeting, forecasting, reporting and accounting exports.

---

## Technology

MOA currently uses:

- Symfony 8
- PHP 8.4
- PostgreSQL
- Doctrine ORM
- EasyAdmin
- Docker Compose
- PHPStan

---

## Documentation

### Wiki

The main user, functional and contributor documentation is maintained in the GitHub Wiki.

The Wiki includes:

- Installation
- Administration
- Architecture
- Project Rules
- Development Checklist
- Understanding MOA
- Financial Model
- Analytical Accounting

### Repository Documentation

Technical and release documentation remains versioned with the source code.

- [Server Setup](SERVER-SETUP.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Services](docs/SERVICES.md)
- [Import Pipeline](docs/IMPORT_PIPELINE.md)
- [Roadmap](docs/ROADMAP.md)
- [Releases](docs/RELEASES.md)
- [Changelog](docs/CHANGELOG.md)

---

## Development

Before every release, MOA is validated with:

- PHP syntax checks;
- Symfony container validation;
- Doctrine schema validation;
- PHPStan level 6;
- functional smoke tests.

Detailed development rules and release checks are documented in the Wiki.

---

## License

MOA is licensed under the **GNU Affero General Public License v3.0 (AGPL-3.0)**.

See the [LICENSE](LICENSE) file for details.