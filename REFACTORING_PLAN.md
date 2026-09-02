# 🛠️ REFACTORING & ARCHITECTURE PLAN
## Project: `local-services-platform` (Dabberha / دبرها)
**Date:** 2026-08-29  
**Status:** Audit Completed — Ready for Incremental Execution

---

## 1. Executive Summary & Audit Overview

A complete codebase audit was performed across all directories and files in `local-services-platform`.
The project is a local service marketplace supporting three user roles: **Customer (`customer`)**, **Service Provider (`provider`)**, and **Administrator (`admin`)**.

### Summary of Audit Findings
1. **Frontend Inconsistency**: 90% of the project uses Tailwind CSS (via CDN) with Tajawal and Plus Jakarta Sans fonts and Material Symbols/FontAwesome icons. However, 3 files (`public/add-review.php`, `public/register-admin.php`, and `admin/reviews.php`) use Bootstrap 5, creating visual divergence.
2. **Duplicated Layout Boilerplate**: Every page duplicates `<head>`, meta tags, Google Fonts imports, Tailwind config scripts, flash alert banners, and status badge styling.
3. **Fragmented Navigation & Sidebars**:
   - `admin/` pages duplicate sidebar HTML across 6 files.
   - `provider/` uses a separate `provider/header.php` and `provider/sidebar.php`.
   - `public/` pages duplicate the top navbar across 5 files.
4. **Embedded Database Queries**: Complex SQL queries with `JOIN` operations are embedded directly into presentation templates rather than encapsulated into modular data-access functions.
5. **Translations**: `includes/translations.php` contains >500 bilingual keys. A few validation messages in `public/add-review.php` and `public/register.php` remain hardcoded in English.

---

## 2. Detailed Problems Discovered

| File(s) | Problem Description | Duplication Type | Risk | Recommended Solution |
|---|---|---|---|---|
| All 25+ `.php` templates | Inlined `<head>`, Tailwind config script, Font imports | Layout boilerplate | Broken design token updates if changed in one place | Create `includes/components/head.php` & `assets/css/theme.css` |
| `admin/dashboard.php`, `users.php`, `services.php`, `bookings.php`, `categories.php`, `reviews.php` | Inlined admin sidebars and top headers | Navigation & layout | Inconsistent navigation links & active states | Centralize in `includes/components/sidebar.php` and `header_dashboard.php` |
| `provider/dashboard.php`, `my-services.php`, `bookings.php`, `reviews.php`, `profile.php` | Redundant header and sidebar include patterns | Layout & styling | CSS divergence across dashboards | Migrate to shared `includes/components/` while keeping provider menu items |
| `index.php`, `public/browse-services.php`, `public/service-detail.php`, `public/my-bookings.php` | Duplicated public top navbar and mobile bottom nav | Navigation | Broken active link indicators, duplicate auth checks | Create `includes/components/navbar_public.php` & `footer_public.php` |
| `public/add-review.php`, `public/register-admin.php`, `admin/reviews.php` | Uses Bootstrap 5 CDN instead of Tailwind CSS | Framework conflict | Visual mismatch, heavy unused CSS load | Safely convert cards, inputs, and tables to Tailwind CSS matching the brand theme |
| `admin/*.php`, `provider/*.php`, `public/*.php` | Status badge styling (`pending`, `confirmed`, `completed`, `cancelled`, `active`, `inactive`) defined inline | UI Badges | Color inconsistencies across pages | Create `renderStatusBadge($status)` in `includes/helpers/ui_helpers.php` |
| `admin/*.php`, `provider/*.php` | Alert / Flash messages (`$message`, `$message_type`) rendered with custom HTML | Alerts | Varying padding, icon styles, and colors | Create `renderAlert($message, $type)` in `includes/helpers/ui_helpers.php` |
| `browse-services.php`, `service-detail.php`, `provider/reviews.php`, `admin/reviews.php` | Star rating rendering calculated inline with loops or strings | Rating UI | Inconsistent star sizes/colors | Create `renderStarRating($rating)` in `includes/helpers/ui_helpers.php` |
| Multiple templates across all directories | Raw SQL queries for services, bookings, categories, reviews, and users | Database queries | Inconsistent column aliasing, duplicate SQL maintenance | Encapsulate into `includes/db/*.php` helper modules using existing PDO |
| `admin/admin dashboard`, `provider/provider dashboard`, `public/main website pages`, `includes/header`, `includes/functions` | 0-byte orphan files | Dead files | Confusion for developers | Remove confirmed unreferenced 0-byte files |

---

## 3. Proposed Architecture

```text
local-services-platform/
│
├── config/
│   ├── db.php                          # Single PDO database connection
│   └── schema.sql                      # Schema definition
│
├── includes/
│   ├── auth.php                        # Session guard, requireRole(), isLoggedIn(), getUserName()
│   ├── translations.php                # Single source of truth for translations: __(), _e()
│   ├── notifications.php               # Notification dispatch & retrieval
│   │
│   ├── components/                     # Reusable UI component templates
│   │   ├── head.php                    # Unified <head> with fonts, Tailwind config, & theme.css
│   │   ├── navbar_public.php           # Public & Customer top navigation
│   │   ├── header_dashboard.php        # Dashboard header for Provider & Admin
│   │   ├── sidebar.php                 # Role-based sidebar for Provider & Admin
│   │   └── footer_public.php           # Public desktop footer & mobile bottom navigation
│   │
│   ├── helpers/                        # UI and view rendering helper functions
│   │   └── ui_helpers.php              # renderStatusBadge(), renderAlert(), renderStarRating(), etc.
│   │
│   └── db/                             # Modular Database Access Layer (PDO)
│       ├── users_db.php                # User queries (auth check, status toggle, list)
│       ├── services_db.php             # Services queries (catalog, filters, detail, provider)
│       ├── bookings_db.php             # Bookings queries (list, detail, status update, insert)
│       ├── reviews_db.php              # Reviews queries (list, rating average, create, delete)
│       └── categories_db.php           # Categories queries (list, create, delete, icon map)
│
├── assets/
│   ├── css/
│   │   └── theme.css                   # Centralized design tokens (CSS variables)
│   ├── js/
│   │   └── main.js                     # Shared client-side scripts (dropdowns, alerts, modals)
│   └── images/
│
├── public/                             # Public and Customer pages
├── provider/                           # Provider dashboard pages
├── admin/                              # Admin dashboard pages
├── uploads/                            # User uploads
├── index.php                           # Application landing page
└── REFACTORING_PLAN.md                 # This plan
```

---

## 4. Design System & CSS Token Specifications (`assets/css/theme.css`)

```css
:root {
    /* Brand Colors */
    --color-primary: #CB6D51;
    --color-primary-hover: #B55A40;
    --color-primary-light: #FFF0ED;
    --color-primary-dark: #914530;
    --color-secondary: #C18B8B;
    --color-secondary-dark: #805252;

    /* Backgrounds & Surfaces */
    --color-bg: #F9F5F1;
    --color-surface: #FFFFFF;
    --color-surface-alt: #F4EAE6;
    --color-border: #E8DFD8;
    --color-border-light: #E9D5CF;

    /* Text Colors */
    --color-text-main: #3A2F2B;
    --color-text-muted: #6B5E59;
    --color-text-light: #9C8E87;

    /* Status Colors */
    --color-status-active-bg: #DCFCE7;
    --color-status-active-text: #15803D;
    --color-status-pending-bg: #FEF3C7;
    --color-status-pending-text: #B45309;
    --color-status-cancelled-bg: #FEE2E2;
    --color-status-cancelled-text: #B91C1C;
    --color-status-confirmed-bg: #DBEAFE;
    --color-status-confirmed-text: #1D4ED8;
}
```

---

## 5. Incremental Refactoring Phases

```text
PHASE 1: Design Tokens & Base Asset Setup (assets/css/theme.css, assets/js/main.js)
PHASE 2: Core Components & UI Helpers (head.php, ui_helpers.php, navbar_public.php, header_dashboard.php, sidebar.php, footer_public.php)
PHASE 3: Database Access Layer (includes/db/ categories, users, services, bookings, reviews)
PHASE 4: Admin Section Refactoring (admin/*.php & migrate reviews.php to Tailwind)
PHASE 5: Provider Section Refactoring (provider/*.php & unify headers/sidebars)
PHASE 6: Public & Customer Section Refactoring (index.php, public/*.php & migrate add-review.php and register-admin.php to Tailwind)
PHASE 7: Full Translation Audit & Verification
PHASE 8: Cleanup Unreferenced Dead Files
PHASE 9: Complete Syntax Testing (PHP Lint) & End-to-End Verification
```
