# MOA - Project Rules

## Philosophy

The project is built one step at a time.

Each new feature follows the same workflow:

1. Define the business need.
2. Design the domain model.
3. Validate the architecture.
4. Implement.
5. Test.
6. Validate.

No implementation starts before the architecture has been agreed.

During development, the application source code is mounted into the container.

The Docker image does not contain the project source code.

## Simplicity

When several solutions exist, the simplest maintainable solution is preferred.

## Documentation

README.md is the project entry point.

All internal documentation is stored in the `docs/` directory.

## Technical stack

- Symfony 8
- PHP 8.4
- PostgreSQL
- Nginx
- Docker Compose

## Coding conventions

- All code is written in English.
- All comments are written in English.
- Explanations and discussions are in French.

## Docker

Docker is the reference development environment.
Containers are disposable.
Docker images should be based on official images whenever possible.
Community images are adopted only after a conscious architectural decision.
Business data must never be stored inside containers.

## Docker Compose

compose.yaml is the development configuration.

compose.prod.yaml only contains production overrides.

## Containers

Each container has a single responsibility.

## Architecture

Infrastructure and business decisions are discussed separately.
Infrastructure decisions must not influence the business model.

## Layers

The project is divided into independent layers.

Infrastructure
↓
Framework
↓
Application

Each layer must ignore the implementation details of the upper layers.

## Project management

Roadmap describes the future.
Releases describe the current milestone.
Changelog records only completed work.

## Validation

Each implementation step must define:

- Goal
- Success criteria
- Out of scope

No step is considered complete without validation.
