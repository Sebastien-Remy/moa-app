# Server Setup

This document describes the standard production installation procedure for MOA.

## Requirements

- Ubuntu 24.04 LTS
- Git
- Docker
- Docker Compose v2
- OpenSSL

## Clone the repository

```bash
cd /opt/services

git clone https://github.com/Sebastien-Remy/moa-app.git

cd moa-app
```

## Checkout the release

```bash
git fetch --tags
git checkout v0.2.1
```

## Create the production environment

Copy the environment template:

```bash
cp .env.example .env
```

Generate secure secrets automatically:

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