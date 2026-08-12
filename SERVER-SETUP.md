# Server Setup

This document describes the standard production deployment procedure for MOA.

For local development, see the Installation guide in the GitHub Wiki.

---

## Requirements

- Ubuntu 24.04 LTS
- Git
- Docker
- Docker Compose v2
- OpenSSL

---

## Clone the Repository

```bash
cd /opt/services

git clone https://github.com/Sebastien-Remy/moa-app.git

cd moa-app
```

---

## Checkout the Release

Fetch the available tags.

```bash
git fetch --tags
```

Checkout the desired release.

```bash
git checkout <release-tag>
```

Replace `<release-tag>` with the version you want to deploy (for example `v0.6.0`).

---

## Create the Production Environment

Copy the environment template.

```bash
cp .env.example .env
```

Generate secure secrets automatically.

```bash
set -e

POSTGRES_PASSWORD=$(openssl rand -hex 32)
APP_SECRET=$(openssl rand -hex 32)

sed -i "s/^APP_ENV=.*/APP_ENV=prod/" .env
sed -i "s/^APP_SECRET=.*/APP_SECRET=$APP_SECRET/" .env
sed -i "s/^POSTGRES_PASSWORD=.*/POSTGRES_PASSWORD=$POSTGRES_PASSWORD/" .env
sed -i "s|^DATABASE_URL=.*|DATABASE_URL=postgresql://moa:${POSTGRES_PASSWORD}@database:5432/moa?serverVersion=18\&charset=utf8|" .env

chmod 600 .env

echo "Production environment created successfully."
```

The generated secrets are stored only in the local `.env` file and are never committed to Git.

---

## Start the Application

Start the Docker services.

```bash
docker compose up -d
```

---

## Run Database Migrations

Initialize the database schema.

```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

---

## Verify the Installation

Check that the containers are running.

```bash
docker compose ps
```

The MOA application should now be available and ready for the initial owner account creation.