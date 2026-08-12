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

Replace `<release-tag>` with the version you want to deploy.

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

## Production Docker Compose

Production must always use both Docker Compose files:

```text
compose.yaml
compose.prod.yaml
```

The production override contains settings that differ from local development, including the published Nginx port used by the host reverse proxy.

Do **not** start the production application with:

```bash
docker compose up -d
```

This would use only the base Compose configuration and may expose the application on the development port.

Always use:

```bash
docker compose -f compose.yaml -f compose.prod.yaml up -d --build
```

---

## Start the Application

Build and start the production Docker services.

```bash
docker compose -f compose.yaml -f compose.prod.yaml up -d --build
```

---

## Verify the Containers

Check that the production stack is running.

```bash
docker compose -f compose.yaml -f compose.prod.yaml ps
```

The Nginx container should expose the production port expected by the host reverse proxy.

For the standard MOA deployment:

```text
127.0.0.1:8100->80/tcp
```

Verify the internal HTTP endpoint:

```bash
curl -I http://127.0.0.1:8100
```

A redirect to `/login` is expected.

---

## Run Database Migrations

Before upgrading an existing installation, create a database backup.

Then run the migrations:

```bash
docker compose -f compose.yaml -f compose.prod.yaml exec app \
    php bin/console doctrine:migrations:migrate --no-interaction
```

---

## Clear the Production Cache

After deploying new code or Doctrine mappings, clear and warm the production cache.

```bash
docker compose -f compose.yaml -f compose.prod.yaml exec app \
    php bin/console cache:clear --env=prod

docker compose -f compose.yaml -f compose.prod.yaml exec app \
    php bin/console cache:warmup --env=prod
```

---

## Verify Doctrine

Validate the Doctrine mappings and database schema.

```bash
docker compose -f compose.yaml -f compose.prod.yaml exec app \
    php bin/console doctrine:schema:validate
```

Expected result:

```text
Mapping
[OK]

Database
[OK]
```

Verify that no migration remains pending:

```bash
docker compose -f compose.yaml -f compose.prod.yaml exec app \
    php bin/console doctrine:migrations:status
```

---

## Verify Symfony

Check the runtime environment.

```bash
docker compose -f compose.yaml -f compose.prod.yaml exec app \
    php bin/console about
```

Production must report:

```text
Environment  prod
Debug        false
```

---

## Host Reverse Proxy

The host Nginx reverse proxy must point to the production Docker port.

Standard configuration:

```nginx
proxy_pass http://127.0.0.1:8100;
```

If MOA returns a `502 Bad Gateway`, first verify that the Docker production stack is actually exposing port `8100`:

```bash
docker compose -f compose.yaml -f compose.prod.yaml ps
```

Do not change the host Nginx port simply to match an accidentally started development stack.

---

## Final Verification

Verify the public endpoint:

```bash
curl -I https://<your-moa-domain>/
```

A successful installation should return either:

```text
HTTP 302 → /login
```

or an authenticated application response.

MOA is then ready for use.