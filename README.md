# Event Manager API

## Overview
A Symfony-based API for event management, providing secure endpoints for event operations, user authentication, and registration management.

## Features
- 🔐 JWT Authentication
- 🔄 Token refresh mechanism
- 📝 Event CRUD operations
- 👥 User management
- 📊 Event registration handling
- 🛡️ Role-based access control
- 📚 OpenAPI documentation

## Tech Stack
- PHP 8.2
- Symfony 7.0
- MySQL 8.0
- Doctrine ORM
- JWT Authentication
- OpenAPI/Swagger
- Docker

## Prerequisites
- Docker and Docker Compose

## Installation

1. Clone the repository
```bash
git clone https://github.com/ainemehdi6/events-management-api
cd events-management-api
```

2. Configure environment variables
   Create a `.env.dev` file:
```env
DATABASE_URL="mysql://root:root@db:3306/event_manager?serverVersion=8.0"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase
```

3. Build and Up containers
```bash
docker compose build --no-cache

docker compose up -d
```

4. Enter the php container
```bash
make exec
```

5. Install dependencies
```bash
composer install
```

6. Generate JWT keys
```bash
php bin/console lexik:jwt:generate-keypair
```

7. Run migrations
```bash
php bin/console doctrine:migrations:migrate
```

## Project Structure
```
src/
├── Controller/     # API endpoints
├── Entity/         # Database entities
├── Repository/     # Database queries
├── Service/        # Business logic
├── DTO/            # Data transfer objects
└── Transformer/    # Data transformers
```

## API Documentation
Access the OpenAPI documentation at `http://localhost:8080`

## Available Commands
- `php bin/console doctrine:migrations:migrate` - Run database migrations
- `php bin/console doctrine:migrations:diff` - Generate migration
- `php bin/console cache:clear` - Clear cache
- `php bin/console debug:router` - List all routes

## Testing
```bash
php bin/phpunit
```