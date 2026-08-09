# Installation

This guide explains how to install MOA from a fresh clone.

---

## Requirements

- Git
- Docker
- Docker Compose

---

## Clone the repository

```bash
git clone https://github.com/Sebastien-Remy/moa-app.git
cd moa-app
```

---

## Configure the environment

Create the local environment file.

```bash
cp .env.example .env
```

Edit the `.env` file and replace the example values with your own configuration.

### Document storage

The document storage location is configured through:

```dotenv
DOCUMENT_STORAGE_PATH=/srv/moa/storage
```

The default Docker Compose configuration mounts the local `storage/` directory into the container, allowing imported documents to persist even when containers are recreated.

---

## Start the containers

```bash
docker compose up -d --build
```

---

## Install PHP dependencies

```bash
docker compose exec app composer install
```

---

## Run database migrations

```bash
docker compose exec app php bin/console doctrine:migrations:migrate
```

---

## Create the owner account

```bash
docker compose exec app php bin/console app:user:create-owner
```

---

## Open the application

Open your browser:

```
http://localhost:8080
```

Log in using the owner account created during the previous step.