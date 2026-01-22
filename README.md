# Medical Auth Microservice

Authentication microservice for the medical appointments system. Handles user registration, login, JWT tokens, and user profiles.

## Requirements

- PHP >= 8.1
- Composer
- MySQL/MariaDB

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Configure environment variables:
   ```bash
   cp .env.example .env
   ```
4. Edit `.env` with your database credentials
5. Run migrations:
   ```bash
   mysql -u root -p medical_auth < database/schema.sql
   ```

## Running

### Development
```bash
composer start
```
Server will be available at `http://localhost:8001`

### Production
Configure a web server (Apache/Nginx) pointing to the `public/` folder.

## Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | User registration |
| POST | `/api/auth/login` | User login |
| POST | `/api/auth/refresh` | Refresh token |
| GET | `/api/auth/me` | Get current user profile |
| PUT | `/api/auth/password` | Change password |

## Project Structure

```
medical-auth-msvc/
├── public/             # Front Controller (API entry point)
├── config/             # Configuration files
├── app/
│   ├── Core/           # Infrastructure (Router, Request, Response, etc.)
│   ├── Middleware/     # Authentication middlewares
│   ├── Controllers/    # HTTP Controllers
│   ├── Services/       # Business logic
│   ├── Repositories/   # Data access layer
│   ├── Entities/       # Domain entities
│   ├── DTOs/           # Data Transfer Objects
│   └── Mappers/        # Entity <-> DTO Mappers
├── database/           # SQL schemas and seeds
└── docs/               # Documentation (backlog)
```

## Authentication

This service uses JWT (JSON Web Tokens) for authentication:

- **Access Token**: Valid for 1 hour (configurable)
- **Refresh Token**: Valid for 7 days (configurable)

### Required Headers
```
Authorization: Bearer <access_token>
Content-Type: application/json
```

## Shared JWT Secret

Other microservices (like `medical-appointments-msvc`) validate tokens locally using the same `JWT_SECRET`. No HTTP calls between services are needed for token validation.

**Important:** Both services must use the same `JWT_SECRET` value.

## Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| APP_PORT | Server port | 8001 |
| DB_HOST | Database host | 127.0.0.1 |
| DB_DATABASE | Database name | medical_auth |
| JWT_SECRET | Secret key for JWT | - |
| JWT_ACCESS_EXPIRATION | Access token expiration (sec) | 3600 |
| JWT_REFRESH_EXPIRATION | Refresh token expiration (sec) | 604800 |

## Documentation

See [docs/backlog.md](docs/backlog.md) for complete user stories.
