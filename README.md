# API Project

Project Laravel API minimal yang sudah dibersihkan dari komponen web/frontend.

## Database Configuration

Project ini menggunakan PostgreSQL dengan konfigurasi:

```
DB_CONNECTION_NMS=pgsql
DB_HOST_NMS=localhost
DB_PORT_NMS=51098
DB_DATABASE_NMS=dms_clone
DB_USERNAME_NMS=postgres
DB_PASSWORD_NMS=K4nc1ang@k4n4il0
```

## Installation

```bash
composer install
php artisan config:cache
php artisan serve
```

**Note**: Project ini menggunakan database `dms_clone` yang sudah ada dan digunakan bersama dengan project lain. Tidak perlu menjalankan migration.

## API Endpoints

- `GET /api/health` - Health check endpoint
- `GET /api/user` - Get authenticated user (requires auth)

## Structure

Project ini hanya berisi:
- API routes (`routes/api.php`)
- Controllers minimal
- Models minimal (User)
- Database configuration untuk PostgreSQL (pgsql_nms)
