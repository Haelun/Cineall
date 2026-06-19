# CineAll - Quick Start Guide

Get CineAll up and running in 5 minutes!

## 🚀 Quick Installation

### 1. Database Setup (2 minutes)

Open your MySQL client (phpMyAdmin, MySQL Workbench, or command line) and run:

```sql
-- Create database
CREATE DATABASE cineall CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE cineall;

-- Import schema (copy and paste contents of database/schema.sql)
-- Then import seed data (copy and paste contents of database/seed_data.sql)
```

**OR** using command line:
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed_data.sql
```

### 2. Configure Application (1 minute)

Edit `config/config.php`:

```php
define('DB_HOST', 'localhost');      // Your MySQL host
define('DB_NAME', 'cineall');        // Database name
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');               // Your MySQL password
```

### 3. Access the Application

Open your browser and navigate to:
```
http://localhost/cineall/
```

That's it! 🎉

## 📁 Project File Structure

```
cineall/
├── index.php              ← Main entry point
├── config/
│   ├── config.php         ← UPDATE DATABASE CREDENTIALS HERE
│   └── database.php       ← Database connection (don't modify)
├── database/
│   ├── schema.sql         ← Database structure
│   └── seed_data.sql      ← Sample data
├── css/                   ← All stylesheets (modular)
│   ├── variables.css      ← Colors, fonts, spacing
│   ├── base.css           ← Base styles
│   ├── components.css     ← UI components
│   └── pages.css          ← Page layouts
├── js/                    ← All JavaScript (modular)
│   ├── api.js             ← API communication
│   ├── components.js      ← UI component rendering
│   ├── pages.js           ← Page rendering
│   └── app.js             ← Main app logic
└── php/
    ├── api/               ← API endpoints
    │   ├── movies.php     ← Movie operations
    │   ├── platforms.php  ← Platform operations
    │   ├── genres.php     ← Genre operations
    │   └── user.php       ← User operations
    └── includes/
        ├── header.php     ← Page header
        └── footer.php     ← Page footer
```

## 🎨 Easy Customization

### Change Colors

Edit `css/variables.css`:

```css
:root {
    --bg: #0A0908;                    /* Background */
    --fg: #F4EFE6;                    /* Text color */
    --accent: oklch(0.78 0.14 70);    /* Accent (amber) */
}
```

Try different accent colors:
- **Blue**: `oklch(0.78 0.14 220)`
- **Green**: `oklch(0.78 0.14 140)`
- **Purple**: `oklch(0.78 0.14 280)`
- **Red**: `oklch(0.78 0.14 30)`

### Add New Movies

Run SQL query:

```sql
INSERT INTO movies (movie_key, title, year, runtime, rating, director, critic_score, audience_score, synopsis, tagline, scheme_color_1, scheme_color_2, accent_color)
VALUES (
    'm13',
    'Your Movie Title',
    2025,
    120,
    'PG-13',
    'Director Name',
    85,
    90,
    'Your movie synopsis here...',
    'Your tagline',
    'oklch(0.30 0.06 180)',
    'oklch(0.16 0.04 200)',
    'oklch(0.80 0.12 190)'
);
```

### Add Movie Genres

```sql
-- Link movie to genres
INSERT INTO movie_genres (movie_id, genre_id)
VALUES
    (13, 1),  -- Drama
    (13, 3);  -- Sci-Fi
```

### Add Cast Members

```sql
INSERT INTO cast_members (movie_id, name, display_order)
VALUES
    (13, 'Actor Name 1', 1),
    (13, 'Actor Name 2', 2),
    (13, 'Actor Name 3', 3);
```

### Add Streaming Availability

```sql
INSERT INTO availability (movie_id, platform_id, kind, price_from)
VALUES
    (13, 1, 'subscription', NULL),  -- Streamline
    (13, 2, 'rent', 4.99);          -- Vista+ (rent)
```

## 🛠️ Common Modifications

### Add a New Page

1. **Add route in `js/app.js`:**
```javascript
case 'mypage':
    await Pages.mypage(contentEl);
    break;
```

2. **Create page function in `js/pages.js`:**
```javascript
async mypage(container) {
    container.innerHTML = `
        <div style="padding: 48px;">
            <h1>My Custom Page</h1>
            <p>Your content here...</p>
        </div>
    `;
}
```

3. **Add navigation link in `php/includes/header.php`:**
```html
<a href="#" class="top-nav__link" data-nav="mypage">My Page</a>
```

### Modify a Component

Edit `js/components.js`. For example, to change poster appearance:

```javascript
poster(movie, options = {}) {
    // Modify the rendering logic here
    // Return HTML string
}
```

### Add New API Endpoint

Create `php/api/myendpoint.php`:

```php
<?php
require_once '../../config/config.php';
require_once '../../config/database.php';

header('Content-Type: application/json');
$db = Database::getInstance();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'myaction':
        $data = $db->query("SELECT * FROM my_table");
        echo json_encode(['success' => true, 'data' => $data]);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>
```

## 📝 Quick Tips

### Debugging

Enable debug mode in `config/config.php`:
```php
define('DEBUG_MODE', true);
```

Check browser console (F12) for JavaScript errors.

### Database Issues

Test connection:
```php
// Add to index.php temporarily
require_once 'config/config.php';
require_once 'config/database.php';
$db = Database::getInstance();
echo "Connected!";
```

### CSS Not Loading

Clear browser cache: `Ctrl+F5` (Windows) or `Cmd+Shift+R` (Mac)

### API Not Working

Check:
1. PHP version: `php -v` (need 7.4+)
2. MySQL running: `mysql -u root -p`
3. File permissions: `chmod 755 cineall/`

## 🎯 What You Can Edit Easily

### ✅ Easy to Modify:
- Colors (`css/variables.css`)
- Component styling (`css/components.css`, `css/pages.css`)
- Page content (`js/pages.js`)
- Add movies, platforms, genres (database)
- Text and labels (all files)

### ⚠️ Requires Understanding:
- API logic (`php/api/*.php`)
- Database schema (`database/schema.sql`)
- Component rendering (`js/components.js`)
- Routing (`js/app.js`)

### ❌ Don't Modify Unless You Know What You're Doing:
- Database connection (`config/database.php`)
- Core API structure
- JavaScript module dependencies

## 🐛 Troubleshooting

### "Database connection error"
➜ Check credentials in `config/config.php`

### "Page not loading"
➜ Clear cache, check browser console (F12)

### "Movies not showing"
➜ Verify seed data: `SELECT COUNT(*) FROM movies;`

### "API errors"
➜ Enable `DEBUG_MODE` and check error messages

## 📚 Next Steps

1. Read full `README.md` for detailed documentation
2. Explore the database structure
3. Try modifying colors and styles
4. Add your own movies
5. Create custom pages

## 💡 Pro Tips

- Keep backups of your database
- Test changes on development environment first
- Use browser DevTools (F12) to debug
- Comment your code when making changes
- Follow the existing code patterns

---

**Need Help?** Check the full README.md or examine existing code for examples.

**Happy Coding!** 🎬
