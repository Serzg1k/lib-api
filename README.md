# Library API (Yii2 Advanced + Docker)

A small RESTful API for managing a library of books, built on **Yii2 Advanced** and shipped with **Docker (Nginx + PHP-FPM + MariaDB)**. Authentication via **JWT**. Responses are JSON in REST style with pagination envelopes (`_items`, `_links`, `_meta`).

---

## Quick Start

### Prerequisites
- Docker + Docker Compose
- Git (optional)

### 1) Clone & enter the project
```bash
git clone git@github.com:Serzg1k/lib-api.git library-api
cd library-api
```

### 2) Environment

There are **two easy ways** to set environment variables used by Docker and Yii:

**Option A — rename template (recommended):**
1. In the project root, find `.env_example` (shipped with the repo).
2. Rename it to **`.env`** (same folder as `docker-compose.yml`).
   ```bash
   mv .env_example .env
   ```

**Option B — create `.env` from scratch:**
Create a new file named **`.env`** next to `docker-compose.yml` with the following content:
```env
APP_PORT=8080

DB_HOST=db
DB_NAME=library
DB_USER=app
DB_PASS=app
DB_PORT=3306
```

**What these mean:**

| Variable   | Default | Purpose |
|------------|---------|---------|
| `APP_PORT` | `8080`  | Host port to expose Nginx (`http://localhost:${APP_PORT}`). Change if 8080 is busy. |
| `DB_HOST`  | `db`    | Docker Compose service name of the database container. |
| `DB_NAME`  | `library` | Database name used by the app. |
| `DB_USER`  | `app`   | DB user created inside the DB container. |
| `DB_PASS`  | `app`   | Password for `DB_USER`. |
| `DB_PORT`  | `3306`  | DB port inside the Docker network. You usually don’t need to publish it to the host. |

> **Notes**
> - If your host already runs MySQL on `3306`, **do not** publish DB port from the container, or change the host mapping (e.g., `3307:3306`). The app talks to DB internally by `DB_HOST=db` and doesn’t need host port.
> - Works with **MySQL 8** and **MariaDB 11**. For MariaDB, the `.env` stays the same — only the container image differs.
> - Yii reads these vars in `common/config/db.php`. You can switch to SQLite by editing the DSN there if you prefer a file DB for local development.

### 3) Start the stack
```bash
docker compose pull
docker compose up -d
```

- Nginx will serve **http://localhost:${APP_PORT}** (default `http://localhost:8080`).
- PHP-FPM container is called `php`, DB is `db` (MariaDB 11 or MySQL 8 depending on your compose).

### 4) Install PHP dependencies (Composer)
```bash
docker compose exec php bash -lc "composer install"
```

### 5) Initialize Yii2 Advanced (first time only)
```bash
docker compose exec php php /app/init --env=Development --overwrite=All
```

### 6) Run DB migrations
```bash
docker compose exec php php /app/yii migrate --interactive=0
```

### 7) Smoke check
```bash
curl "http://localhost:8080/books"
```

You should get JSON (empty list with pagination meta if there are no books).

---

## Project Layout (key files)

```
library-api/
  api/
    config/
      main.php
    controllers/
      BookController.php
      AuthController.php
      UserController.php
    web/
      index.php
  common/
    config/
      db.php
  console/
    migrations/
      mYYYYMMDDHHmmss_*.php
  docker-compose.yml
  Dockerfile
  nginx.conf
  .env
  README.md (this file)
```

- **Nginx** serves from `/app/api/web` and forwards PHP to `php:9000`.
- **DB** DSN is read from env in `common/config/db.php`.

---

## API

Base URL: `http://localhost:8080`

### Auth & Users
- `POST /users` — register (body: `login`, `password`, `email`)  
  Response `200` with created user.
- `POST /auth/login` — login (body: `login`, `password`)  
  Response `200` with `{ token, token_type: "Bearer", expires_in }`.
- `GET /users/{id}` — profile (authorized only, `Authorization: Bearer <JWT>`).

### Books
- `GET /books` — list with pagination (`page`, `per-page`) and HATEOAS envelopes:
  - `_links` → `self`, `first`, `next`, `last`
  - `_meta` → `totalCount`, `pageCount`, `currentPage`, `perPage`
- `POST /books` — create (authorized)
- `GET /books/{id}` — show
- `PUT /books/{id}` — update (only owner)
- `DELETE /books/{id}` — delete (only owner)

#### Example cURL
```bash
# Register
curl -X POST http://localhost:8080/users \
  -H "Content-Type: application/json" \
  -d '{"login":"alice","password":"secret123","email":"alice@example.com"}'

# Login → get JWT
curl -X POST http://localhost:8080/auth/login \
  -H "Content-Type: application/json" \
  -d '{"login":"alice","password":"secret123"}'

# Create a book
curl -X POST http://localhost:8080/books \
  -H "Authorization: Bearer <JWT>" -H "Content-Type: application/json" \
  -d '{"title":"Clean Code","author":"Robert C. Martin","published_year":2008}'

# List books (HATEOAS)
curl "http://localhost:8080/books?page=1&per-page=10"
```

### Validation & Errors
- Invalid input → `422` with error map.
- Unauthorized → `401` JSON error.
- Forbidden (edit/delete not owned) → `403` JSON error.
- Not found → `404` JSON error.

---

## Pagination Envelopes

Controller uses Yii `Serializer` with `collectionEnvelope`. The list response looks like:
```json
{
  "items": [ { "id": 1, "title": "..." } ],
  "_links": {
    "self":  { "href": "http://localhost:8080/books?page=1&per-page=2" },
    "first": { "href": "http://localhost:8080/books?page=1&per-page=2" },
    "next":  { "href": "http://localhost:8080/books?page=2&per-page=2" },
    "last":  { "href": "http://localhost:8080/books?page=5&per-page=2" }
  },
  "_meta": {
    "totalCount": 10,
    "pageCount": 5,
    "currentPage": 1,
    "perPage": 2
  }
}
```

---

## Running Tests (Codeception)

We keep an isolated suite for API under `api/tests`.

### Smoke run (inside container)
```bash
docker compose exec php vendor/bin/codecept run -c api api -vv
```

- Suite config: `api/codeception.yml`
- Actor: `api/tests/_support/ApiTester.php`
- Tests: `api/tests/api/*.php`
- Modules: `Asserts`, `REST` (`PhpBrowser` dependency).

> By default, tests hit `http://nginx` from inside the PHP container. If you run tests from your host, change the suite URL to `http://localhost:8080` in `api/codeception.yml`.

### Creating a new test
```bash
docker compose exec php vendor/bin/codecept generate:cest -c api api MyFeature
```

---

## Docker Cheatsheet

- Logs:
  ```bash
  docker compose logs -f nginx
  docker compose logs -f php
  docker compose logs -f db
  ```
- Recreate DB volume (⚠️ wipes data):
  ```bash
  docker compose down -v
  docker compose up -d
  ```
- Common DB issues:
  - Port 3306 busy → remove `ports: ["3306:3306"]` from DB service or change to `3307:3306`.
  - `getaddrinfo for db failed` → ensure service name is `db` and PHP env `DB_HOST=db`.
  - DB not healthy → check `docker compose logs -f db`.

---

## Configuration Notes

- **Nginx** (`nginx.conf`)
  - `root /app/api/web;`
  - `try_files $uri $uri/ /index.php?$query_string;`
- **PHP** (`Dockerfile`)
  - PHP-FPM 8.x + extensions (`pdo_mysql`, `intl`, `zip`).
  - Composer available in container.
- **DB** (`common/config/db.php`)
  - Reads DSN/creds from env: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
  - Switch to SQLite by setting `dsn` to `sqlite:@common/runtime/library.sqlite` if needed.


---

## Troubleshooting

- **404 from Apache/Nginx (not Yii)**: make sure Docker is used; locally ensure rewrite is enabled. In Docker Nginx, `try_files` handles pretty URLs.
- **`Could not open input file: yii`**: run with absolute path `/app/yii` inside container.
- **`Class 'Yii' not found` in tests**: use REST tests without Yii2 module (already configured) or fix `entryScript` path if enabling it.
- **`422` on register in tests**: users already exist → use unique logins/emails in tests or reset DB volume.

