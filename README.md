# TaskFlow

A Kanban-style project management application built with **Laravel 13**, **Inertia.js v3**, and **Vue 3**. TaskFlow lets teams organize work across fully customizable boards with drag-and-drop columns and tasks.

---

## Features

- 🔐 **Authentication** — Register, login, and logout with secure session-based auth
- 📋 **Project Management** — Create and manage multiple projects per user
- 🗂️ **Kanban Boards** — Per-project boards with customizable columns (e.g. To Do, In Progress, Done)
- ✅ **Task Management** — Create, edit, delete, and move tasks between columns
- ↕️ **Drag & Drop Reordering** — Reorder both columns and tasks with real-time persistence
- 🔒 **Authorization** — Users can only access and modify their own projects and tasks

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3, Laravel 13 |
| Frontend | Vue 3, TypeScript, Inertia.js v3 |
| Styling | Tailwind CSS v4 |
| Database | SQLite (local), configurable for MySQL/PostgreSQL |
| Build Tool | Vite 8 |
| Routing (typed) | Laravel Wayfinder |
| Testing | Pest v4 |
| Code Quality | Laravel Pint, ESLint v9, Prettier v3 |

---

## Requirements

Before getting started, make sure you have the following installed:

- **PHP** `>= 8.3` with the `pdo_sqlite` extension enabled
- **Composer** `>= 2.x`
- **Node.js** `>= 20.x`
- **npm** `>= 10.x`

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/HlaingMinThan/Task-Management.git taskflow
cd taskflow
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Set up the environment file

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Set up the database

The project uses SQLite by default — no database server needed.

```bash
touch database/database.sqlite
php artisan migrate
```

> **Using MySQL or PostgreSQL instead?** Update the `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` values in your `.env` file, then run `php artisan migrate`.

### 5. Install JavaScript dependencies

```bash
npm install
```

### 6. Start the development server

```bash
composer run dev
```

This starts all required processes concurrently:

| Process | Description |
|---|---|
| `php artisan serve` | Laravel HTTP server at `http://localhost:8000` |
| `npm run dev` | Vite HMR dev server |
| `php artisan queue:listen` | Queue worker (for background jobs) |
| `php artisan pail` | Real-time log viewer |

Open **http://localhost:8000** in your browser and register a new account to get started.

---

## One-Command Setup (Alternative)

For a quick start, you can run the full setup in a single command:

```bash
composer run setup
```

This will install all dependencies, generate an app key, run migrations, and build frontend assets automatically. After this completes, start the dev server with `composer run dev`.

---

## Useful Commands

```bash
# Run the test suite
php artisan test --compact

# Fix PHP code style
vendor/bin/pint

# Fix JS/Vue code style
npm run lint
npm run format

# Check TypeScript types
npm run types:check

# Generate Wayfinder route types (run after adding/changing routes)
php artisan wayfinder:generate

# List all registered routes
php artisan route:list --except-vendor
```

---

## Project Structure

```
app/
├── Http/Controllers/
│   ├── Auth/             # Registration, login, logout
│   ├── ProjectController.php
│   ├── ColumnController.php
│   └── TaskController.php
├── Models/
│   ├── User.php
│   ├── Project.php
│   ├── Column.php
│   └── Task.php
└── Policies/             # Authorization policies

resources/js/
├── pages/
│   ├── Auth/             # Login & Register pages
│   ├── Board/            # Kanban board & components
│   └── Dashboard.vue     # Project listing dashboard
└── components/           # Shared Vue components

database/
├── migrations/           # Database schema
├── factories/            # Model factories for testing
└── seeders/

routes/
├── web.php               # Authenticated app routes
└── auth.php              # Auth routes
```

---

## Running Tests

```bash
php artisan test --compact
```

To run a specific test:

```bash
php artisan test --compact --filter=TestName
```

---

## Contributing

1. Create a feature branch from `main`: `git checkout -b feature/your-feature`
2. Make your changes, following the existing code conventions
3. Run the linter and tests before committing:
   ```bash
   vendor/bin/pint
   npm run lint
   php artisan test --compact
   ```
4. Open a pull request with a clear description of your changes
