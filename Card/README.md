# AstitvaHub

**One Link. Your Complete Digital Identity.**

AstitvaHub is a PHP 8+ and MySQL SaaS platform for category-aware digital profiles at clean URLs like `https://astitvahub.com/pratik`.

## Module 1 Status

This checkpoint includes the production database schema and the deployable MVC folder structure. The next checkpoint will wire the Core Router, Config, and Auth module to this schema.

## Install Database

1. Open phpMyAdmin or the MySQL CLI.
2. Import `database.sql` from the project root.
3. The same schema is mirrored at `database/schema.sql` for convenience.

Seeded admin:

- Email: `admin@astitvahub.local`
- Password: `Admin@12345`

Change this account before launch.

## Folder Structure

```text
AstitvaHub/
├── app/
│   ├── Controllers/      # MVC controllers for landing, auth, dashboard, admin, profiles and API
│   ├── Core/             # Router, database, session, CSRF, view and base controller classes
│   ├── Helpers/          # Framework-free helper modules added as the app grows
│   ├── Middleware/       # Auth, admin, CSRF and rate-limit middleware
│   ├── Models/           # PDO-backed data access models
│   ├── Services/         # QR, VCF, analytics, mail, SEO and upload services
│   ├── Support/          # Shared functions and small utilities
│   └── Views/            # Layouts, partials, landing, auth, dashboard, admin and profile views
├── config/               # App, database, category field and security configuration
├── database/
│   ├── migrations/       # Optional incremental SQL changes for future versions
│   ├── seeds/            # Optional seed data split files for future versions
│   └── schema.sql        # Mirror of the root database.sql
├── public/
│   ├── assets/           # CSS, JS, images and vendor assets
│   ├── uploads/          # Profile photos, covers, resumes, business PDFs, gallery, products and portfolios
│   └── index.php         # Front controller for XAMPP/cPanel
├── routes/               # Web route definitions
├── storage/
│   ├── cache/            # Runtime cache
│   ├── exports/          # CSV/report exports
│   ├── logs/             # App logs
│   ├── mail/             # Local email previews/fallbacks
│   └── qrcodes/          # Generated QR image cache
├── .htaccess             # Clean URL rewrite entry
└── database.sql          # Full fresh-install schema
```

## Stack

- PHP 8+, MySQL, PDO prepared statements
- HTML5, CSS3, Bootstrap 5, vanilla JavaScript and AJAX
- Chart.js, Font Awesome, Google Fonts
- Custom lightweight MVC, no Laravel, no Composer required
- XAMPP and cPanel shared hosting compatible
