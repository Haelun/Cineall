# CineAll — Unified Platform

Your four separate projects (public site, authentication, admin panel, curator panel) are now **one application running on a single database**, following the table naming from your paperwork (`movie_key`, `availability`, `home_rows`, `scheme_color_1/2`, …).

A film *aggregator*: users search films, see details and scores, and find **where to watch** (which platform, and whether it's subscription / rent / buy). Admins manage the catalogue; curators arrange the homepage; everyone shares the same data.

---

## 1. Install (XAMPP)

1. Copy this whole `cineall` folder into your XAMPP web root:
   `C:\xampp\htdocs\cineall`
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open **http://localhost/phpmyadmin** → **Import** → choose
   `cineall/database/install.sql` → **Go**.
   (This creates the `cineall` database, all tables, and sample data in one step.)
4. Visit **http://localhost/cineall/**

That's it. If your project lives at a different URL, change one line —
`APP_URL` in `config/config.php`.

> The database defaults match XAMPP exactly: host `localhost`, user `root`, empty password. If yours differ, edit the `DB_*` lines in `config/config.php`.

---

## 2. Where things live

| Area | URL |
|------|-----|
| Public site (Discover) | `http://localhost/cineall/` |
| Sign in / Sign up | `http://localhost/cineall/auth/` |
| Admin panel | `http://localhost/cineall/admin/` |
| Curator panel | `http://localhost/cineall/curator/` |

When you sign in, you're sent to the right place automatically (admin → admin panel, curator → curator panel, normal user → the site). Admin/Curator links also appear in the site header when a staff account is signed in.

## 3. Test accounts

All three use the password **`password123`**:

| Email | Role | Notes |
|-------|------|-------|
| `user@cineall.com` | User | watchlist, subscriptions, preferences |
| `admin@cineall.com` | Admin | asks for a 2-factor code on login |
| `curator@cineall.com` | Curator | asks for a 2-factor code on login |

**2-factor (dev mode):** admin/curator logins ask for a code. In development any 4+ digit number is accepted (e.g. `123456`); the "real" code is also written to PHP's error log. Turn this off by setting `APP_ENV` to `production` in `config/config.php`.

---

## 4. How it's organised

```
cineall/
├── index.php              Public site (JS single-page app)
├── config/
│   ├── config.php         ONE shared config (DB, URLs, session, constants)
│   └── database.php       ONE PDO layer (getDB() + query helpers)
├── includes/
│   ├── functions.php      Shared auth + helpers
│   └── header.php/footer.php
├── api/                   Public JSON APIs (movies, genres, platforms, user)
├── assets/css, assets/js  Public front-end + shared design tokens
├── auth/                  Login, signup, 2FA, logout
├── admin/                 Admin panel (dashboard, films, film editor, users)
├── curator/               Curator panel (dashboard, homepage composer, …)
└── database/
    ├── install.sql        ← import this (schema + sample data)
    ├── schema.sql         tables only
    └── seed.sql           sample data only
```

## 5. What was unified (summary)

- **One database** (`cineall`) instead of four. The whole platform reads/writes the same tables, so an admin's new film and a curator's homepage edit appear on the public site immediately.
- **One login.** A real session now drives everything — the public site shows the watchlist/subscriptions of whoever is actually signed in (previously it was hard-coded to user #1).
- **Paperwork schema wins.** The admin and curator panels were rewritten to the normalized tables from your report (genres/cast in their own tables, `home_rows`/`availability`, `movie_key`, `scheme_color_1/2`) instead of their old private layouts.
- **One look.** All sections share the same design tokens (fonts, palette, accent).

---

### Notes
- This is a development build (errors visible, demo 2FA). Before showing it as "production", set `APP_ENV = 'production'` in `config/config.php` and change `SECRET_KEY`.
- The "Continue with Google/Apple" buttons on the login page are placeholders (no OAuth wired) — normal email/password sign-in works fully.
