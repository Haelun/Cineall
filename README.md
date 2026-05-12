# Cineall-Project
Web project for Web Programming 

CineAll is an online streaming platform that allows users to watch movies and TV shows easily in one place. The website is designed with a user-friendly interface, providing fast access to entertainment content across various genres. CineAll aims to deliver a convenient, accessible, and enjoyable streaming experience for users anytime and anywhere.

## Group's Member:
1. LALU AHMAD FAIZ HAQIQI (F1D02410117)     -- Content Manager and UI/UX Developer
2. HAIDAR WAHYU YASARI (F1D02410114)        -- Project Manajer and Backend Developer
3. NYOMAN ADHI DHIRA PURNOMO (F1D02410132)  -- Database Developer and Frontend Developer

## Features
- Browse and search movies across multiple streaming services
- Filter by genre, platform, year, and ratings
- View detailed movie information with critic and audience scores
- Personal watchlist management
- Platform subscription tracking
- Compare streaming service coverage
- Cinematic, cinema-inspired design

## SiteMap
```
User
HOME (Discover)
├─SEARCH/BROWSE
│ ├─ MOVIE DETAIL
│ │ ├─ Add to Watchlist
│ │ ├─ Watch on Platform (External)
│ │ └─ Related Movies → MOVIE DETAIL
│ └─ Apply Filters (Genre/Platform)
│
├─GENRES
│ └─ Select Genre → MOVIE DETAIL
│
├─COMPARE
│ └─ Sample Movie → MOVIE DETAIL
│
├─RECOMMENDATIONS (For You)
│ └─ (Future: Recommended Movies → MOVIE DETAIL)
│
├─ WATCHLIST
│ ├─ MOVIE DETAIL
│ └─ Remove from Watchlist
│
└─ ACCOUNT
└─ Toggle Platform Subscriptions
````



## Project Structure
```
cineall/
├── config/                 # Configuration files
│   ├── config.php          # Application configuration
│   └── database.php        # Database connection class
├── css/                    # Modular CSS files
│   ├── variables.css       # CSS variables and theme
│   ├── base.css            # Base styles and resets
│   ├── components.css      # Reusable UI components
│   └── pages.css           # Page-specific layouts
├── js/                     # JavaScript modules
│   ├── api.js              # API client helper
│   ├── components.js       # UI component functions
│   ├── pages.js            # Page rendering logic
│   └── app.js              # Main application logic
├── php/
│   ├── api/                # API endpoints
│   │   ├── movies.php      # Movies API
│   │   ├── platforms.php   # Platforms API
│   │   ├── genres.php      # Genres API
│   │   └── user.php        # User management API
│   └── includes/           # Reusable PHP components
│       ├── header.php      # Page header
│       └── footer.php      # Page footer
├── database/               # Database files
│   ├── schema.sql          # Database schema
│   └── seed_data.sql       # Initial data
├── assets/                 # Static assets
│   └── images/             # Images
├── index.php               # Main application entry
└── README.md               # This file
```

## TechStack
- **Frontend**: HTML5, CSS3 (CSS Variables), Vanilla JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Architecture**: MVC-inspired, RESTful API
