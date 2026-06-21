# CineAll — Unified Platform

Your four separate projects (public site, authentication, admin panel, curator panel) are now **one application running on a single database**, following the table naming from your paperwork (`movie_key`, `availability`, `home_rows`, `scheme_color_1/2`, …).

A film *aggregator*: users search films, see details and scores, and find
**where to watch** (which platform, and whether it's subscription / rent / buy,
with a link straight to that title on the service). Admins manage the
catalogue; curators arrange the homepage; everyone shares the same data.

> The seeded catalogue uses **real films** (Dune: Part Two, The Batman, Glass
> Onion, Killers of the Flower Moon, …) on **real platforms** (Netflix, Disney+,
> Prime Video, HBO Max, Apple TV+). Each "Where to watch" option links out to
> that title on the platform. Streaming availability is representative and
> varies by region/time — edit it any time from the Admin or Curator panel.

---

## 1. Install (XAMPP)

1. Copy this whole `cineall` folder into your XAMPP web root:
   `C:\xampp\htdocs\cineall`
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open **http://localhost/phpmyadmin** → **Import** → choose
   `cineall/database/install.sql` → **Go**.
   (This creates the `cineall` database, all tables, and sample data in one step.)
4. Visit **http://localhost/cineall/**

That's it. The app **auto-detects its own URL**, so it works whether you put it
in `htdocs/cineall`, rename the folder, or serve it from another host — no need
to edit any path. (If you ever want to force a fixed URL, there's a one-line
note in `config/config.php`.)

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

## 3. Accounts

Only the two **staff** accounts ship with the database (password `password123`):

| Email | Role |
|-------|------|
| `admin@cineall.com` | Admin |
| `curator@cineall.com` | Curator |

**Regular users are not pre-seeded.** Per the design, the watchlist,
subscriptions and preferences are *Registered User* features — so you create a
user by clicking **Sign up** (`/auth/signup.php`), which makes a `user`-role
account and signs you straight in. Guests can browse, search and compare, but
are asked to sign in when they try to save something.

To add a user **manually** instead, insert a row with a bcrypt password hash —
there's a ready-to-use example at the bottom of `database/seed.sql` (the hash
shown is for `password123`).

Every role signs in directly with email + password (no 2-factor step) and is
sent to the right place: admin → admin panel, curator → curator panel, user →
the site.

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
- **One login.** A real session now drives everything — the public site shows the watchlist/subscriptions of whoever is actually signed in (previously it was hard-coded to user #1). Every role logs in directly (no 2FA).
- **Paperwork schema wins.** The admin and curator panels were rewritten to the normalized tables from your report (genres/cast in their own tables, `home_rows`/`availability`, `movie_key`, `scheme_color_1/2`) instead of their old private layouts.
- **One look.** All sections share the same design tokens (fonts, palette, accent).

## 6. Searching & sorting algorithms

The search area is powered by `includes/search_sort.php` — hand-written algorithms,
not just SQL:

- **Relevance search** (`searchMoviesRanked`) — a linear scan that scores every
  movie against the query (exact title = 100, title-starts-with = 60,
  title-contains = 40, director = 25, cast = 15, genre = 10, plus per-word
  bonuses) and keeps only the matches. O(n).
- **Quick sort** (`quickSort`) — divide-and-conquer ordering used for
  Relevance, Rating and Newest. Average O(n log n).
- **Merge sort** (`mergeSort`) — stable divide-and-conquer used for the A–Z
  title sort. O(n log n) guaranteed.
- **Binary search** (`binarySearchByTitle`) — O(log n) exact-title lookup on a
  title-sorted array.

The public API (`api/movies.php`) pulls a filtered candidate set from the DB,
then runs the search ranking and the chosen sort in PHP. On the site, the
search box, the **Relevance / Rating / Newest / A–Z** buttons, and the
genre/platform filters are all wired to this.

## 7. Database schema (18 tables)

Import `database/install.sql` — it creates the `cineall` database, all tables,
and the sample data in one step. Tables and their relationships:

**Content**
- `movies` — one row per film (`movie_key`, title, year, scores, colour scheme…)
- `genres`, `cast_members` — genre names; cast linked to a movie
- `movie_genres` — movies ↔ genres (many-to-many)
- `platforms` — streaming services
- `availability` — a film on a platform, with `kind` (subscription/rent/buy) + price

**Accounts & user data**
- `users` — every account (role = user/admin/curator)
- `sessions`, `password_resets` — login sessions and reset tokens
- `watchlist` — user ↔ saved movies
- `user_subscriptions` — services a user subscribes to
- `user_preferences` — per-user toggles

**Homepage curation**
- `home_rows` — the rows shown on Discover (`row_key`, title, kicker, order, active)
- `home_row_movies` — which movies sit in each row, in order
- `homepage_settings` — hero film, tagline, published flag

**Operations (admin/curator)**
- `reviews` — user reviews awaiting moderation (status + flag)
- `analytics` — one row per day (visits, searches, click-throughs, signups)
- `activity_log` — shared audit trail (who did what)

Key relationships: `movie_genres`, `availability`, `cast_members`, `watchlist`,
`home_row_movies` all reference `movies(id)`; `availability`/`user_subscriptions`
reference `platforms(id)`; `watchlist`/`user_subscriptions`/`user_preferences`/
`sessions` reference `users(id)`; `home_row_movies` references `home_rows(id)`.
All use `ON DELETE CASCADE`, so deleting a film or user cleans up its dependents.

---

### Notes
- This is a development build (errors visible). Before showing it as "production", set `APP_ENV = 'production'` in `config/config.php` and change `SECRET_KEY`.
- The "Continue with Google/Apple" buttons on the login page are placeholders (no OAuth wired) — normal email/password sign-in works fully.
