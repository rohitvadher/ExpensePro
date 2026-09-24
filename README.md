# ExpensePro — Income & Expense Manager

[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![PWA](https://img.shields.io/badge/PWA-Ready-5A0FC8)](./manifest.json)
[![License](https://img.shields.io/badge/License-Educational-lightgrey)](#project-information)

“ExpensePro is a PHP and MySQL based personal finance management Progressive Web App designed to manage income, expenses, categories, budgets, analytics, reports, notifications and financial data import/export through a modular web architecture.”

## Project Overview

ExpensePro tracks daily income and expenses for individual users. Each user gets private categories, transactions, budgets, CSV import history, smart notifications and quick-entry templates. A dashboard summarizes the current month, analytics visualizes twelve-month trends, and reports produce CSV downloads and printable/PDF output. Guests see a public landing page; signing in routes to the dashboard. The app installs as a PWA and keeps working offline for cached pages.

## Purpose

Give individuals a clear picture of where their money goes: record income and spending in seconds, stay within monthly budgets, review trends, and export clean records for accounting or tax filing.

## Core Features

- Landing page, registration, login, logout, remember-me, secure sessions
- Dashboard with income, expense, balance, counts, recent activity and budget status
- Transactions with search, type/category/date filters, pagination, create, edit and delete
- Income and expense categories with colors, icons and safe delete (reassign or block when in use)
- Weekly, monthly and yearly budgets per category or overall, with progress and over-budget state
- Analytics charts (net-balance trend, income vs expense, category breakdown)
- Reports with date ranges, quick ranges, charts, CSV export and PDF export
- CSV import with header detection, row validation, duplicate detection, preview and history
- Smart notifications (over-budget alerts, login notices) with read/unread state
- Quick templates for one-tap frequent entries
- Profile management and password change with current-password verification
- Command palette (`Ctrl+K`), mobile bottom navigation, help guide, offline page

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8+ |
| Database | MySQL |
| Database Engine | InnoDB |
| Database Access | PDO |
| Frontend | Vanilla JavaScript |
| CSS | Tailwind CSS |
| Charts | ApexCharts 3.44.0 (lazy-loaded) |
| Icons | Lucide 1.47.0 where used, local SVG set |
| Date Tools | Flatpickr 4.6.13 for pickers, Day.js 1.11.23 for date math (both lazy-loaded) |
| PDF | html2pdf.js 0.10.2 client engine plus server printable report (both lazy/on-demand) |
| PWA | Service Worker + Web App Manifest |
| Server | Apache/XAMPP |

Only the libraries above are used. There is no jQuery, no frontend framework, no Node runtime and no build step.

## Architecture

```text
ExpensePro
    |
    +-- Frontend                  Backend
    |      |                         |
    |   Pages / UI               API / Services
    |      |                         |
    |  JS Modules                Validation
    |      |                         |
    |  API Client                Security
    |      |                         |
    +------+------------+------------+
                         |
                     Repository
                         |
                        PDO
                         |
                       MySQL
```

### Folder Structure

```text
ExpensePro/
├── api/                       # 14 JSON endpoints (login, register, logout, version,
│                              # dashboard, transactions, categories, budgets, import,
│                              # reports, export, profile, quick-templates, notifications)
├── assets/
│   ├── css/                   # Modular styles: core, layout, components, features, pages
│   ├── js/                    # core (config, api-client, errors, validation, finance,
│   │                          # form-state), services, components, features, pages, vendor
│   └── icons/                 # PWA icons + SVG set
├── backend/                   # Canonical server modules: core, validation, http,
│                              # security, domain, support, calculations, database, services
├── database/
│   ├── schema.sql             # Full MySQL schema + safe rerunnable upgrades
│   └── migrations/            # 001_performance_indexes.sql (idempotent)
├── docs/                      # Final documentation pack + screenshots
├── includes/                  # config, database, functions loader, finance, auth, api guards
├── layouts/                   # guest/app headers, sidebar, mobile nav, footer, scripts
├── offline/                   # Self-contained offline page
├── pages/                     # 14 server-rendered pages
├── .htaccess                  # Clean URLs + security headers + asset caching
├── .gitignore
├── index.php                  # Front controller (?page= routing)
├── manifest.json              # PWA manifest
├── README.md
└── sw.js                      # Service worker
```

### Frontend Architecture

Pages are server-rendered shells; behavior lives in page controllers under `assets/js/pages/` that share one core: a single API client (one JSON parser, CSRF header, `AbortController` support, network-vs-server error model), a central error handler (401 sessions, 403 tokens, 404/409/422/429/500 mapping, field-error application), one validation layer mirroring the server, one finance module for previews, and one button/form state machine (disabled while invalid or submitting, always restored). One modal controller, one toast system and one vendor loader (pinned SRI, lazy, timeouts) serve every page. Heavy libraries load only when used. Inline event handlers are not used; a delegated dispatcher routes `data-ep-action` clicks.

### Backend Architecture

`index.php` routes `?page=` through a fixed whitelist to `pages/`, wrapped by guest or application layouts. `api/*.php` controllers handle HTTP method, authentication, CSRF, validation and responses only; business workflows live in `backend/services/` and data access in `backend/database/repositories/` (PDO prepared statements, every query scoped to the authenticated user). Shared rules live in focused modules: validation, money math, CSRF, rate limiting, responses, notifications and budget periods. `includes/functions.php` loads them for backward compatibility.

### API Architecture

Every endpoint returns one envelope:

```json
{ "success": true, "message": "Transactions retrieved successfully.", "data": {}, "errors": null }
```

Validation failures return HTTP 422 with per-field messages; duplicates 409; missing login 401; bad tokens 403; missing rows 404; rate limits 429; server failures a safe generic 500. Responses never expose SQL, paths or stack traces. Downloads (CSV, printable report) use correct content types and filenames.

### Database Architecture

MySQL with InnoDB and `utf8mb4`. Nine tables: `users`, `categories`, `transactions`, `budgets`, `quick_templates`, `csv_imports`, `notifications`, `login_attempts`, `rate_limits`. Money uses exact `DECIMAL(12,2)`. All user data cascades from `users`; transaction categories are delete-restricted (reassign or block); budget and template categories null out safely. Ownership indexes plus dedicated performance indexes cover auth, filtering, reporting and cleanup queries. `schema.sql` creates everything and safely upgrades older installs; `migrations/001_performance_indexes.sql` is idempotent.

## Authentication

Registration validates name, email and password strength, rejects duplicate emails (409) and seeds default categories inside a database transaction. Login rate-limits failures (5 per 15 minutes per email and IP, dummy-hash timing on unknown users), rehashes outdated bcrypt hashes, clears failure counters and optionally sets a 30-day remember-me token (SHA-256 stored, HttpOnly, `SameSite=Lax`, rotated on each use, revoked on logout and password change). Sessions regenerate IDs on login, expire after 24 hours of inactivity and refresh CSRF tokens every 30 minutes. Password change revokes remembered devices.

## Validation

Frontend validation gives instant feedback; the backend re-validates everything authoritatively. Rules: email format, password of at least 8 characters with a letter and a number, amounts finite, positive, capped and limited to two decimals, dates in `YYYY-MM-DD`, category ownership plus income/expense type match, names 2–100 characters, hex colors and icon allowlists. Duplicates are detected for transactions, categories, budgets and emails. CSV rows are validated individually with per-row error reports.

## Financial Calculations

One canonical model (`moneyRound`, `calcTotals`, `calcPercentage`, `calcSavingsRate`, `calcAvgDaily`, `calcBudgetStatus`, Indian `formatINR` grouping) is used by dashboard, analytics, reports, budgets, exports and profile alike, so every screen agrees:

- Balance = Total Income − Total Expense
- Savings Rate = ((Income − Expense) / Income) × 100
- Budget Remaining = max(0, Limit − Spent); over-budget when Spent > Limit
- Daily average spreads expense across the selected date range

## Security

Bcrypt hashing with rehash, regenerated sessions, `HttpOnly` + `SameSite=Lax` cookies (`Secure` on real HTTPS), CSRF on every state-changing API, per-user ownership checks on every query, prepared statements throughout, input validation plus output escaping, allowlisted notification links and icons, login/register/import rate limiting with self-cleaning buckets, generic error responses, and security headers (CSP scoped to the pinned CDN, `nosniff`, `DENY` framing, same-origin referrer). Direct access to `database/`, `includes/`, `backend/` and logs is denied.

## Import/Export

CSV import accepts `.csv` under 5 MB, sniffs content beyond the extension, detects headers, parses `YYYY-MM-DD` and `DD/MM/YYYY` dates, strips currency symbols, fuzzy-matches income/expense types, maps existing categories, rejects duplicates, records per-row errors and stores a history entry (`completed`, `partial` or `failed`). CSV export and the printable/PDF report reuse the page filters and server-authoritative data with correct filenames.

## Analytics

Twelve-month net-balance and income-vs-expense charts, summary bars, category breakdown, one-click refresh with stale-response protection, graceful empty states and offline degradation to native controls when the chart CDN is unreachable.

## Budget System

Overall or per-category budgets over weekly, monthly or yearly periods with automatic period ranges, live spent totals, remaining amounts, percentages, over-budget flags and duplicate protection per category and period.

## Notification System

Budget alerts are regenerated at most every 30 minutes with same-day deduplication; login notices are deduplicated within 15 minutes. Users can mark one or all as read. Only the newest 30 notifications are retained per user.

## PWA

Installable manifest (standalone display, icons, portrait), service worker with versioned caches: static assets served stale-while-revalidate, API calls network-only with an offline JSON fallback, navigation network-first with a self-contained offline page, old-cache cleanup, update banner on new builds, deferred install prompt and online/offline toasts.

## Performance Strategy

Page-specific JS and CSS, lazy optional libraries, shared API client, cancellable requests with stale-response guards, event delegation, indexed and paginated queries, immutable caching of versioned assets, gzip output and a service worker cache — no build step, no framework overhead.

## CDN/Lazy Loading

One registry (`assets/js/vendor.js`) pins Lucide, Flatpickr (+CSS), Day.js, ApexCharts and html2pdf.js on jsDelivr with integrity hashes, loads each once on first use with timeouts and failed-load cleanup, and degrades gracefully (native date inputs, local date math, friendly chart/PDF errors).

## Installation

1. Install XAMPP (PHP 8+, MySQL, Apache) and start Apache + MySQL.
2. Copy this folder to `C:\xampp\htdocs\ExpensePro`.
3. Create the database (next section).
4. Open `http://localhost/ExpensePro/` and register an account.

## XAMPP Configuration

Apache needs `mod_rewrite` (clean `?page=` URLs) and `mod_headers` (security headers). PHP needs `pdo_mysql`, `mbstring` and `fileinfo`. No Composer packages and no npm steps are required.

## MySQL Database Setup

```sql
CREATE DATABASE IF NOT EXISTS expensepro
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then import `database/schema.sql` (phpMyAdmin → Import, or `mysql -u root expensepro < database/schema.sql`). On an existing install, also apply `database/migrations/001_performance_indexes.sql` (safe to rerun). Default credentials are user `root` with an empty password and database `expensepro`; override with `EXPENSEPRO_DB_HOST`, `EXPENSEPRO_DB_NAME`, `EXPENSEPRO_DB_USER`, `EXPENSEPRO_DB_PASS` environment values.

## Configuration

`includes/config.php` centralizes the base URL (auto-detected, overridable with `EXPENSEPRO_BASE_URL`), app name and version, `Asia/Kolkata` timezone, 24-hour sessions, 30-minute CSRF rotation and production-safe error handling (nothing displayed, nothing logged to files). Session cookies are `HttpOnly`, `SameSite=Lax`, scoped to the app path.

## Application Workflow

Guest lands on the public page, registers or logs in, and reaches the dashboard. From there every feature is one navigation away; the command palette (`Ctrl+K`) jumps anywhere. All data operations flow: UI → validation → API → service → repository → MySQL → envelope → state → UI.

## User Workflow

Register → verify dashboard → add categories → record income and expenses (or import CSV) → set budgets → review analytics and reports → export records → manage profile and notifications. Logout clears the session and remembered devices.

## API Request Flow

Browser → frontend API client → HTTP request → endpoint → authentication → CSRF → validation → service → repository → MySQL → repository result → service → API response → frontend state → UI update, with field errors mapped back onto the form that sent them.

## Security Workflow

Every protected request proves identity (session or rotated remember-me), proves intent (CSRF token), proves ownership (user-scoped queries), uses parameterized SQL, validates input twice (client for speed, server for authority), escapes output, and answers failures with safe generic messages and correct HTTP codes.

## Validation Workflow

User input → frontend validation → field error or API request → backend validation → structured error or business rules → database constraints → MySQL. Invalid data is explained at the exact field; valid data proceeds.

## Folder Responsibility

- `api/` — HTTP boundary only (14 endpoints).
- `pages/` + `layouts/` — server-rendered shells and navigation.
- `assets/js/` — frontend core, services, components, features, page controllers.
- `assets/css/` — tokens, layout, components, features, pages.
- `backend/` — validation, calculations, security, domain, services, repositories.
- `includes/` — bootstrap (config, PDO, auth, API guards).
- `database/` — schema plus idempotent migrations.
- `docs/` — final documentation, screenshots and diagrams.
- `offline/` — offline page. `sw.js`, `manifest.json` — PWA shell.

## Deployment

Copy the folder to any Apache + PHP 8 + MySQL host, point the document path at it (or keep the `/ExpensePro/` prefix and matching `RewriteBase`), import the schema, set credentials via environment, and open the app. Versioned asset URLs (`?v=`) make deploys cache-safe; the update banner notifies running clients.

## Browser Compatibility

Chrome and Edge (full PWA install), Firefox (no install prompt), Safari iOS (Add to Home Screen). Verified desktop viewports 1440×900, 1366×768, 1920×1080 and mobile 390×844, 412×915 with touch. `AbortController`, `fetch`, dynamic viewport units and backdrop filters degrade gracefully.

## Project Information

- Project: ExpensePro — Income & Expense Manager
- Student: Rohit Vadher
- Course: BCA Sem-5
- Version: 1.0
- Stack: PHP 8+, PDO, MySQL (InnoDB, utf8mb4), vanilla JavaScript, Tailwind CSS, Apache/XAMPP
- Full reference: `docs/ExpensePro_Project_Documentation.pdf` (and `.docx`), screenshots under `docs/screenshots/`
