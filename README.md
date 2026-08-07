# Kangguircm — Multi-Module Platform

A modular PHP application featuring an integrated **CMS**, **Email Marketing**, and **HRM** system with unified authentication, RBAC, and enterprise-grade security.

## Architecture

```
┌─────────────────────────────────────────────────┐
│                  public/index.php               │  ← Front Controller
├─────────────────────────────────────────────────┤
│              routes/web.php                     │  ← Central Router
├──────────────┬──────────────┬───────────────────┤
│  CMS Module  │ Email Module │    HRM Module     │
│              │              │                   │
│  • Posts     │ • Subscribers│  • Employees      │
│  • Pages     │ • Lists      │  • Attendance     │
│  • Media     │ • Campaigns  │  • Leaves         │
│  • Categories│ • Templates  │  • Payroll        │
├──────────────┴──────────────┴───────────────────┤
│           Auth + RBAC + CSRF Middleware          │
├─────────────────────────────────────────────────┤
│              PDO Database Layer                  │
│        (Prepared Statements / Transactions)       │
└─────────────────────────────────────────────────┘
```

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.1+ (strict typing) |
| Database | MySQL 8+ (InnoDB, FK constraints) |
| Frontend | Tailwind CSS + Vanilla JS (Fetch API) |
| Mail | PHPMailer (SMTP) |
| Logging | Monolog |
| Packages | phpdotenv, PHPMailer, Monolog |

## Quick Start

### 1. Install Dependencies

```bash
composer install
```

### 2. Configure Environment

```bash
cp .env.example .env
# Edit .env with your database credentials and mail settings
```

### 3. Run Migrations

```bash
php database/migrate.php
```

### 4. Serve Application

```bash
# Development
php -S localhost:8000 -t public

# Production: Point Apache/Nginx document root to /public
```

### 5. Setup Cron Jobs

```bash
# Email campaigns (runs every minute)
* * * * * php /path/to/cron/send-emails.php

# Database backup (daily at 2 AM)
0 2 * * * php /path/to/cron/backup.php
```

## Project Structure

```
├── app/
│   ├── Controllers/     # Request handlers
│   ├── Core/            # Database, Router, View, Session, CSRF
│   ├── Helpers/         # Validation, Security utilities
│   ├── Middleware/       # Auth, Role, CSRF middleware
│   └── Models/          # Data models (User, Role)
├── config/              # Configuration files
├── cron/                # Background job scripts
├── database/
│   ├── migrate.php      # Migration runner
│   └── migrations/      # Schema definitions
├── public/              # Web root
│   ├── index.php        # Front controller
│   ├── .htaccess        # Apache URL rewriting
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript
│   └── uploads/         # User uploads
├── resources/views/     # PHP templates
├── routes/web.php       # Route definitions
├── scripts/             # Setup utilities
├── storage/             # Logs, cache, backups
└── tests/               # Test files
```

## Security Features

- ✅ **SQL Injection Prevention** — All queries use PDO prepared statements
- ✅ **CSRF Protection** — Token-based form validation on all state-changing requests
- ✅ **XSS Prevention** — `htmlspecialchars()` on all output via View::escape()
- ✅ **Password Security** — Argon2id hashing (PHP's strongest algorithm)
- ✅ **Content Security Policy** — Strict CSP headers on every response
- ✅ **Role-Based Access Control** — Granular permission system
- ✅ **Secure File Upload** — MIME type validation, size limits, unique filenames
- ✅ **Session Security** — HTTPOnly, SameSite=Lax, secure cookie flags
- ✅ **Financial Validation** — Range-checked salary/monetary fields
- ✅ **Hidden Error Output** — Production mode suppresses error details

## Modules

### CMS (Content Management System)
- CRUD operations for posts with rich content
- Category management
- Secure media uploader with MIME validation
- SEO-friendly slugs (auto-generated, unique)
- Draft/Published/Archived status workflow

### Email Marketing
- Subscriber management with confirmation tokens
- Segmented mailing lists
- Campaign creation with HTML templates
- Async sending via cron job
- SMTP integration via PHPMailer
- Template personalization ({{name}}, {{email}})

### HRM (Human Resource Management)
- Employee profiles linked to user accounts
- Clock-in/Clock-out attendance tracking
- Leave request workflow (request → approve/reject)
- Multiple leave types (sick, casual, earned, maternity, paternity)
- Salary management with financial validation

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_ENV` | Environment (development/production) | development |
| `APP_DEBUG` | Debug mode (true/false) | true |
| `DB_HOST` | Database host | 127.0.0.1 |
| `DB_PORT` | Database port | 3306 |
| `DB_DATABASE` | Database name | kangguircm |
| `DB_USERNAME` | Database user | root |
| `DB_PASSWORD` | Database password | (empty) |
| `MAIL_HOST` | SMTP server | smtp.mailtrap.io |
| `MAIL_PORT` | SMTP port | 2525 |
| `MAX_UPLOAD_SIZE` | Max upload (bytes) | 10485760 |

## Default Roles

| Role | Description |
|------|-------------|
| `admin` | Full system access (all permissions) |
| `user` | Standard user (read, write own) |
| `editor` | Content management (read, write, publish, delete own) |
| `hr_manager` | HR module access (read, manage HR, approve leaves) |

## License

MIT
