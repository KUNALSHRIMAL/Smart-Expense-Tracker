# 💰 Smart Expense Tracker

A personal monthly expense tracker built as a **Progressive Web App (PWA)** using PHP and MySQL. Track every rupee — add expenses, set a monthly budget, record credits, view history, and install it on your phone like a native app.

---

## Screenshots

> _Add screenshots to this folder and update the paths below_

| Dashboard | Add Expense | History |
|-----------|-------------|---------|
| (<img width="648" height="871" alt="image" src="https://github.com/user-attachments/assets/e9734a1e-0b04-4b6f-82de-3944ceb7ca59" />
) | (<img width="626" height="857" alt="image" src="https://github.com/user-attachments/assets/2e1f294a-2cb0-49cf-840c-4a404e7e5c53" />
) | (<img width="709" height="852" alt="image" src="https://github.com/user-attachments/assets/bb5112bc-5acd-42a1-82c1-17c3f473ba7c" />
) |

---

## Features

- **Dashboard** — Monthly budget card with progress bar, today's spend, quick shortcuts, and recent transactions
- **Add Expense** — 7 categories (Grocery, Milk, Fuel, Food, Bills, Medical, Other) with emoji icons
- **Credit tracking** — Record money given to someone; shows as **+positive** in history and does not count against your budget
- **History** — Browse by month, search, filter by category, and export to CSV
- **Quick Shortcuts** — One-tap buttons for frequent expenses (e.g. Milk ₹50, Petrol ₹200)
- **Settings** — Update monthly budget, manage shortcuts, change password
- **Multi-user** — Each user has their own data; register new accounts from the login page
- **PWA** — Installable on Android/iOS, works offline via service worker

---

## Tech Stack

| Layer    | Technology                        |
|----------|-----------------------------------|
| Backend  | PHP 8+ (no framework)             |
| Database | MySQL / MariaDB                   |
| Frontend | Tailwind CSS (CDN), Vanilla JS    |
| PWA      | Web App Manifest + Service Worker |
| Server   | Apache (XAMPP)                    |

---

## Installation

### Requirements
- [XAMPP](https://www.apachefriends.org/) (PHP 8+, MySQL, Apache)

### Steps

1. **Clone into your XAMPP web root**
   ```bash
   git clone https://github.com/your-username/expense-tracker.git C:/xampp/htdocs/money
   ```

2. **Start Apache and MySQL** in the XAMPP Control Panel

3. **Run the installer**
   Open your browser and go to:
   ```
   http://localhost/money/setup/install.php
   ```
   Click **Run Setup**. This creates the database, all tables, and a default user.

4. **Log in**
   ```
   http://localhost/money/login.php
   Username: admin
   Password: 1234
   ```

5. **Delete or restrict the installer** after setup
   ```
   C:/xampp/htdocs/money/setup/install.php
   ```

---

## Configuration

Edit `config.php` to change the database credentials or base URL:

```php
define('BASE_URL', '/money');   // Change to '' if deployed in web root
define('DB_HOST',  'localhost');
define('DB_NAME',  'expense_tracker');
define('DB_USER',  'root');
define('DB_PASS',  '');
```

---

## Project Structure

```
money/
├── config.php              # App constants, DB credentials, category list
├── dashboard.php           # Home screen
├── add-expense.php         # Add / edit expense or credit
├── history.php             # Monthly transaction history + CSV export
├── settings.php            # Budget, shortcuts, password
├── login.php               # Login
├── register.php            # New user registration
├── logout.php
├── includes/
│   ├── auth.php            # Session helpers (requireLogin, isLoggedIn)
│   ├── db.php              # PDO connection + auto-migration
│   ├── functions.php       # All shared helper functions
│   ├── head.php            # HTML <head> with Tailwind config
│   └── nav.php             # Bottom navigation bar
├── api/
│   ├── quick_add.php       # POST — add expense from shortcut
│   ├── delete_expense.php  # POST — delete expense
│   ├── update_budget.php   # POST — update monthly budget
│   ├── add_shortcut.php    # POST — create shortcut
│   ├── delete_shortcut.php # POST — remove shortcut
│   ├── change_password.php # POST — change password
│   └── export_csv.php      # GET  — download CSV for a month
├── assets/
│   ├── icon.php            # Generates PNG app icon dynamically
│   └── css/app.css
├── setup/
│   └── install.php         # One-time DB installer
├── manifest.json           # PWA manifest
└── sw.js                   # Service worker (offline cache)
```

---

## Database Schema

```
users       — id, username, password, created_at
expenses    — id, user_id, title, amount, category, expense_date, notes, is_credit, created_at
budgets     — id, user_id, amount, month, created_at
shortcuts   — id, user_id, title, amount, category, sort_order, created_at
```

The `is_credit` column is added automatically on first run if it doesn't exist — no manual migration needed.

---

## PWA — Install on Mobile

1. Open `http://your-local-ip/money/` in Chrome on Android (or Safari on iOS)
2. Tap **Add to Home Screen**
3. The app opens fullscreen with no browser UI, just like a native app

---

## Built With AI

This project was designed and built with the help of **[Claude](https://claude.ai)** by Anthropic, using **[Claude Code](https://claude.ai/code)** — an AI coding assistant that works directly inside the editor.

Claude helped with:
- Designing the full project structure and database schema
- Writing all PHP backend logic (auth, CRUD, API endpoints)
- Building the Tailwind CSS UI and responsive layouts
- Implementing the PWA manifest and service worker
- Adding features like credit tracking, quick shortcuts, and CSV export
- Debugging issues (e.g. missing `functions.php` include breaking the login form)

> _Entire app built through natural language conversation — no boilerplate generators, no copy-paste from Stack Overflow._

---

## License

MIT — free to use and modify.
