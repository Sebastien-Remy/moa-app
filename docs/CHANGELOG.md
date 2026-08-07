# Changelog

## v0.2.1 - Server Setup

Released

### Added

- Complete `.env.example` configuration
- Secure generation of PostgreSQL and Symfony secrets
- `SERVER-SETUP.md`
- Production environment variables for the PHP container

### Fixed

- Symfony now receives `APP_ENV` and `APP_SECRET` from Docker Compose
- Production Composer installation no longer attempts to load development bundles

### Validated

- Fresh PostgreSQL installation
- Production Composer installation
- Database migrations
- Owner account creation
- Login and logout over HTTPS

## v0.2.0 - Authentication

Released

### Added

- User authentication
- Login and logout
- Protected application
- Owner creation command
- Owner account recovery command

## v0.1.1 - Production Deployment

Released

### Infrastructure

- Production Docker Compose override
- Reverse proxy configuration
- HTTPS deployment
- Production deployment process validated

## v0.1 - Foundation

Released

### Infrastructure

- Docker Compose environment
- Official PHP 8.4 image
- Composer
- ZIP support
- Symfony 8.1 skeleton
- PostgreSQL 18
- Nginx
- First HTTP endpoint
