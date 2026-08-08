# Administration

This document describes the most common administration commands.

## Recover the owner account

If the owner username or password has been lost, run:

```bash
docker compose exec app php bin/console app:user:recover-owner
```

The command:

- displays the current owner username;
- allows the username to be kept or changed;
- asks for a new password;
- never creates a second owner.

## Run pending migrations

```bash
docker compose exec app php bin/console doctrine:migrations:migrate
```

## Check migration status

```bash
docker compose exec app php bin/console doctrine:migrations:status
```

## Initialize default reference data

Run:

```bash
docker compose exec app php bin/console app:initialize