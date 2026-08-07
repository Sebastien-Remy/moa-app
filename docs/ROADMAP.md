# Roadmap

## v0.2 - Authentication

### Status

Completed

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
- Owner account recovery command
- Prevention of multiple owners
- Prevention of public user registration

### Success criteria

- A fresh installation provides a documented command to create the first owner.
- The owner password is securely hashed.
- The command refuses to create another owner if one already exists.
- An authenticated owner can access the protected application.
- An unauthenticated visitor is redirected to the login page.
- No public registration page is available.
- A fresh installation can create exactly one owner.
- A second owner cannot be created.
- The existing owner's username can be displayed and changed from the command line.
- The existing owner's password can be reset from the command line.