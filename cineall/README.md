# CineAll - Film Aggregator Platform

A modern, modular web application for aggregating and discovering films across multiple streaming platforms.

## Features

- 🎬 Browse and search movies across multiple streaming services
- 🎭 Filter by genre, platform, year, and ratings
- ⭐ View detailed movie information with critic and audience scores
- 📝 Personal watchlist management
- 🔗 Platform subscription tracking
- 📊 Compare streaming service coverage
- 🎨 Beautiful, cinema-inspired design

## Technology Stack

- **Frontend**: HTML5, CSS3 (CSS Variables), Vanilla JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Architecture**: MVC-inspired, RESTful API

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

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx web server
- Composer (optional, for future dependencies)

### Step 1: Database Setup

1. Create a new MySQL database:
```sql
CREATE DATABASE cineall CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Import the database schema:
```bash
mysql -u your_username -p cineall < database/schema.sql
```

3. Import the seed data:
```bash
mysql -u your_username -p cineall < database/seed_data.sql
```

### Step 2: Configuration

1. Open `config/config.php`

2. Update the database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cineall');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

3. Update the `APP_URL` if needed:
```php
define('APP_URL', 'http://localhost/cineall');
```

### Step 3: Web Server Setup

#### Apache

1. Ensure mod_rewrite is enabled
2. Copy project to your web root (e.g., `htdocs/cineall/`)
3. Access via `http://localhost/cineall/`

#### Nginx

Add to your nginx config:
```nginx
location /cineall {
    try_files $uri $uri/ /cineall/index.php?$query_string;
}
```

### Step 4: File Permissions

Ensure proper permissions:
```bash
chmod 755 cineall/
chmod 644 cineall/config/*.php
```

## Usage

### Demo Account

Default demo account credentials:
- **Username**: demo
- **Email**: demo@cineall.com
- **Password**: demo123

### API Endpoints

All API endpoints are located in `php/api/` and return JSON responses.

#### Movies API (`movies.php`)

- **List Movies**: `?action=list&genres=Drama,Thriller&year_min=2023`
- **Movie Detail**: `?action=detail&id=1`
- **Search**: `?action=search&q=vessel`
- **By Genre**: `?action=by_genre&genre=Sci-Fi`
- **Home Rows**: `?action=home_rows`

#### Platforms API (`platforms.php`)

- **List Platforms**: `?action=list`
- **Platform Stats**: `?action=stats`

#### Genres API (`genres.php`)

- **List Genres**: `?action=list`

#### User API (`user.php`)

- **Get Watchlist**: `?action=get_watchlist`
- **Add to Watchlist**: POST `action=add_to_watchlist&movie_id=1`
- **Remove from Watchlist**: POST `action=remove_from_watchlist&movie_id=1`
- **Get Subscriptions**: `?action=get_subscriptions`
- **Toggle Subscription**: POST `action=toggle_subscription&platform_id=1`

## Customization

### Adding New Movies

Add movies directly via database or create an admin interface:

```sql
INSERT INTO movies (movie_key, title, year, runtime, rating, director, critic_score, audience_score, synopsis, tagline, scheme_color_1, scheme_color_2, accent_color)
VALUES ('m13', 'Your Movie', 2025, 120, 'PG-13', 'Director Name', 85, 88, 'Synopsis...', 'Tagline', 'oklch(0.30 0.06 180)', 'oklch(0.16 0.04 200)', 'oklch(0.80 0.12 190)');
```

### Adding New Platforms

```sql
INSERT INTO platforms (platform_key, name, hue, abbr)
VALUES ('newplatform', 'New Platform', 180, 'NP');
```

### Customizing Colors

Edit `css/variables.css` to change the color scheme:

```css
:root {
    --bg: #0A0908;           /* Background color */
    --fg: #F4EFE6;           /* Foreground/text color */
    --accent: oklch(0.78 0.14 70); /* Accent color */
}
```

### Modifying Components

All UI components are in `js/components.js`. Edit functions to customize rendering:

```javascript
Components.poster(movie, options) {
    // Customize poster rendering
}
```

### Creating New Pages

1. Add route handler in `js/app.js`:
```javascript
case 'newpage':
    await Pages.newpage(contentEl);
    break;
```

2. Create page function in `js/pages.js`:
```javascript
async newpage(container) {
    // Render your page
}
```

3. Add navigation link in `header.php`

## Troubleshooting

### Database Connection Error

- Verify database credentials in `config/config.php`
- Ensure MySQL service is running
- Check if database exists: `SHOW DATABASES;`

### API Not Working

- Check file permissions on `php/api/` folder
- Verify mod_rewrite is enabled (Apache)
- Check browser console for JavaScript errors

### Movies Not Displaying

- Verify data was imported: `SELECT COUNT(*) FROM movies;`
- Check browser console for API errors
- Verify API URL in `js/api.js`

### CSS Not Loading

- Clear browser cache
- Check file paths in `header.php`
- Verify CSS files exist in `css/` folder

## Development

### Debug Mode

Enable debug mode in `config/config.php`:
```php
define('DEBUG_MODE', true);
```

This will display PHP errors and detailed error messages.

### Adding New API Endpoints

1. Create new file in `php/api/`
2. Follow the existing pattern:
```php
<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

header('Content-Type: application/json');
$db = Database::getInstance();

// Your API logic here
```

### Database Migrations

For schema changes, create numbered migration files:
```sql
-- database/migrations/001_add_ratings_table.sql
CREATE TABLE ratings (...);
```

## Security Notes

- Change default demo password in production
- Set `DEBUG_MODE` to `false` in production
- Use HTTPS in production
- Implement proper authentication system
- Sanitize all user inputs
- Use prepared statements (already implemented)

## Performance Tips

- Enable PHP OPcache
- Use MySQL query caching
- Minify CSS and JavaScript for production
- Enable gzip compression
- Consider CDN for fonts and assets
- Add database indexes for frequently queried columns

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## License

This is a prototype/educational project. Modify and use as needed.

## Credits

- **Design**: Inspired by editorial cinema publications
- **Fonts**: Newsreader (Google Fonts), JetBrains Mono
- **Color System**: OKLCH color space

## Contact

For questions or support, refer to the project documentation.
