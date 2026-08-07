# Roadmap

## v0.2 - Authentication

### Status

Planning

### Goal

Provide secure authentication for the application and ensure that every installation has exactly one owner account.

### Scope

- User entity
- Username authentication
- Password hashing
- Login
- Logout
- Protected application
- `ROLE_OWNER`
- First owner creation command
- Owner password reset command
- Prevention of multiple owners
- Prevention of public user registration

### Success criteria

- A fresh installation provides a documented command to create its first owner.
- The owner username is unique.
- The owner password must contain at least 8 characters.
- The owner password is securely hashed and never stored in plain text.
- The owner creation command assigns `ROLE_OWNER` explicitly.
- The owner creation command refuses to create another owner if one already exists.
- The existing owner's password can be reset from the command line.
- An authenticated owner can access the protected application.
- An unauthenticated visitor is redirected to the login page.
- No public registration page is available.
