# Al Omran Blog CMS

A production-ready, custom Blog CMS Dashboard for the Al Omran Training & Development Center.

## Features

- **Article Management** - Create, edit, publish articles in Arabic and English with rich text editor
- **Careers Management** - Manage job postings with bilingual support, rich text editors
- **SEO Tools** - SEO health scoring, SERP preview, keyword tracking, slug management, 301 redirects
- **OG Image Generator** - Browser-based social image creation tool
- **Media Library** - Upload, manage, and organize images with drag-and-drop
- **Analytics** - Content activity tracking ready for external integration
- **Settings** - Site configuration, default SEO settings, admin profile
- **RTL/LTR Support** - Full Arabic (RTL) and English (LTR) interface
- **Public API** - JSON endpoints for the existing website to consume
- **Auto-save** - Articles and careers auto-save via AJAX
- **Rate Limiting** - Login brute-force protection

## Requirements

- PHP 8.2+
- MySQL 8+ (or SQLite for local development)
- Shared hosting compatible (no VPS, Docker, or Node.js required)

## Installation

### Production (MySQL on Shared Hosting)

1. **Upload files** to your shared hosting via FTP or cPanel File Manager

2. **Create a MySQL database** via cPanel or your hosting control panel

3. **Import the database schema:**
   - Open phpMyAdmin
   - Select your database
   - Go to the Import tab
   - Upload `database/schema.sql`
   - Optionally import `database/seed.sql` for sample data

4. **Configure the database connection:**
   - Copy `config/config.example.php` to `config/config.php`
   - Edit `config/config.php` with your database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'your_database');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     ```
   - Update `APP_URL` to match your domain

5. **Set permissions:**
   - Ensure `uploads/` and `admin/cache/` directories are writable
   - Set `chmod 755` on directories, `chmod 644` on files

6. **Access the admin panel:**
   - Navigate to `https://yourdomain.com/admin/login.php`
   - Default credentials:
     - Username: `admin`
     - Password: `admin`
   - **CHANGE THIS PASSWORD IMMEDIATELY after first login**

7. **Configure the API for your website:**
   - The public API is at `/admin/api/articles.php` and `/admin/api/careers.php`
   - Your existing website can fetch published content via these endpoints

### Local Development (SQLite - No MySQL Required)

1. Ensure PHP has the `pdo_sqlite` extension enabled
2. Run the setup script:
   ```bash
   cd admin
   php setup.php
   ```
3. Start the PHP built-in server:
   ```bash
   cd admin
   php -S localhost:8000
   ```
4. Open `http://localhost:8000/login.php`
5. Login with `admin` / `admin`

## Project Structure

```
admin/
├── assets/css/admin.css      # CMS styles
├── assets/js/admin.js         # CMS JavaScript
├── includes/
│   ├── auth.php               # Authentication helpers
│   ├── database.php           # PDO connection (MySQL + SQLite)
│   ├── functions.php          # Helper functions (CSRF, uploads, SEO scoring, etc.)
│   ├── header.php             # Admin layout header + sidebar
│   ├── footer.php             # Admin layout footer
│   └── lang/en.php, ar.php   # UI translations
├── articles/                  # Articles CRUD (list, create, edit, delete)
├── careers/                   # Careers CRUD (list, create, edit, delete)
├── seo/                       # SEO tools (overview, SERP preview, keywords, slugs, health checker, OG generator)
├── media/                     # Media library (upload, manage, delete)
├── analytics/                 # Analytics dashboard
├── settings/                  # Site settings + admin profile
├── api/                       # Public JSON API endpoints
│   ├── articles.php           # GET published articles
│   ├── careers.php            # GET published careers
│   ├── media.php              # GET media listing
│   ├── seo.php                # GET SEO health data
│   ├── seo-score.php          # GET SEO score for article
│   ├── slug.php               # POST generate/check slugs
│   ├── upload.php             # POST upload media (auth required)
│   └── delete-media.php       # DELETE media (auth required)
├── cache/                     # Rate limit cache (auto-created)
├── login.php                  # Admin login page
├── logout.php                 # Logout handler
├── setup.php                  # SQLite setup script (local dev only)
└── index.php                  # Dashboard

config/
├── config.php                 # Your configuration (git-ignored)
└── config.example.php         # Example configuration template

database/
├── schema.sql                 # MySQL database schema (12 tables)
├── seed.sql                   # Sample data (admin user, articles, careers)
└── cms.sqlite                 # SQLite database (local dev only)

uploads/
├── articles/                  # Article images
├── og/                        # OG images
├── thumbnails/                # Thumbnails
└── .htaccess                  # Prevents script execution

/en/                           # English public website
/*.html                        # Arabic public website
```

## API Endpoints

### Articles (Public - No Auth Required)
```
GET /admin/api/articles.php?action=list&lang=en&page=1&per_page=20
GET /admin/api/articles.php?action=get&slug=article-slug&lang=en
```

### Careers (Public - No Auth Required)
```
GET /admin/api/careers.php?action=list&lang=en&page=1&per_page=20
GET /admin/api/careers.php?action=get&slug=job-slug&lang=en
```

### SEO (Public - No Auth Required)
```
GET /admin/api/seo.php?action=check&article_id=1&lang=en
GET /admin/api/seo.php?action=check_all
```

### Media (Auth Required)
```
GET  /admin/api/media.php?action=list&page=1
POST /admin/api/upload.php          (multipart/form-data: file)
POST /admin/api/delete-media.php    (delete_id)
```

### Slugs (Public)
```
POST /admin/api/slug.php   {"title": "...", "lang": "en"}
GET  /admin/api/slug.php?check=my-slug&lang=en
```

All endpoints return JSON with proper HTTP status codes.

## Database Schema

12 tables with foreign keys and utf8mb4 encoding:

| Table | Purpose |
|-------|---------|
| `users` | Admin accounts with role-based access |
| `articles` | Article status, author, featured image |
| `article_translations` | Bilingual content (en/ar), slugs, SEO fields |
| `seo_metadata` | OG/Twitter cards, canonical, indexing |
| `keywords` | Primary/secondary/related keywords per article |
| `media` | Uploaded files with metadata |
| `slug_history` | Tracks slug changes for 301 redirects |
| `redirects` | URL redirects (old -> new, 301/302) |
| `careers` | Job posting status |
| `career_translations` | Bilingual job content |
| `settings` | Key-value site configuration |
| `analytics_events` | View tracking for external integration |

## Security

- PDO prepared statements (no SQL injection)
- CSRF tokens on all forms
- Password hashing with `password_hash()` / `password_verify()`
- Session regeneration after login
- File upload validation (MIME type, extension, size)
- Output escaping with `htmlspecialchars()`
- Rate limiting on login (5 attempts per 5 minutes)
- `.htaccess` rules preventing script execution in uploads
- No sensitive data exposed in error messages

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## License

Proprietary - Al Omran Training & Development Center
