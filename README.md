# Secure PHP Authentication Starter

A lightweight, security-focused PHP 8 and MySQL authentication starter created by [Goitsemang Majaga](https://github.com/goitsemangmajaga).

## Security features

- Password hashing with PHP's current `PASSWORD_DEFAULT` algorithm
- Automatic password rehashing when the default algorithm changes
- PDO prepared statements with emulated prepares disabled
- CSRF tokens on login and logout requests
- Session ID regeneration after authentication
- Secure, HTTP-only and SameSite session cookies
- Thirty-minute inactivity timeout
- Generic login errors to reduce account enumeration
- Login throttling by email and IP within the active session
- Role-based authorization support
- Output escaping helper
- Content Security Policy and defensive HTTP headers
- Environment-based configuration with secrets excluded from Git

## Requirements

- PHP 8.1 or newer
- MySQL 8 or MariaDB 10.5+
- PDO MySQL extension

## Installation

1. Clone the repository.

   ```bash
   git clone https://github.com/goitsemangmajaga/secure-php-auth-starter.git
   cd secure-php-auth-starter
   ```

2. Create the environment file.

   ```bash
   cp .env.example .env
   ```

3. Update `.env` with your database details.

4. Import the database schema.

   ```bash
   mysql -u root -p < database/schema.sql
   ```

5. Create the first administrator.

   ```bash
   php scripts/create_admin.php
   ```

6. Start the development server with `public` as the document root.

   ```bash
   php -S localhost:8000 -t public
   ```

7. Open `http://localhost:8000`.

## Project structure

```text
secure-php-auth-starter/
├── config/             Application and database configuration
├── database/           MySQL schema
├── public/             Public web root
├── scripts/            CLI administration utilities
├── src/                Authentication and security classes
├── .env.example        Safe configuration template
└── README.md            Project documentation
```

## Role protection

Protect a page for any authenticated user:

```php
$auth->requireLogin();
```

Restrict a page to specific roles:

```php
$auth->requireRole('admin', 'manager');
```

## Production guidance

- Serve only the `public/` directory through the web server.
- Set `APP_ENV=production` and use HTTPS.
- Store `.env` outside version control and rotate exposed credentials immediately.
- Use database-backed or distributed throttling for multi-server deployments.
- Add email verification and MFA where the risk profile requires them.
- Run dependency and static-analysis checks in CI before deployment.

## Licence

Released under the [MIT License](LICENSE).

