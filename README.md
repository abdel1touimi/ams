# Article Management System

A RESTful API built with Symfony 6.4 and MongoDB for managing articles and user authentication.

(missing frontend integration)

## Features

- User authentication with JWT
- Article management (CRUD operations)
- MongoDB integration
- Comprehensive validation
- Test coverage with PHPUnit
- Docker-based development environment

## Prerequisites

- Docker and Docker Compose
- Make (optional, but recommended)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/abdel1touimi/ams.git
cd ams
```

2. Copy the environment files:
```bash
cp .env.template .env
```

3. Update the environment variables in `.env`:
- Set `JWT_PASSPHRASE` to a secure random string
- Adapt the values as needed

4. Initialize the project using Make:
```bash
make init
```

This command will:
- Build and start Docker containers
- Install Composer dependencies
- Generate JWT keys
- Clear Symfony cache


## Project Structure

```
backend/
├── src/
│   ├── Controller/         # API endpoints
│   ├── Document/          # MongoDB document classes
│   ├── DTO/               # Data Transfer Objects
│   ├── Exception/         # Custom exceptions
│   ├── Repository/        # Data access layer
│   ├── Response/          # API response formatting
│   ├── Serializer/        # Data serialization
│   ├── Service/           # Business logic
│   └── Validator/         # Input validation
├── tests/
│   ├── Unit/             # Unit tests
│   └── Functional/       # Functional tests
.
.
.
docker-compose.yml and other files/floders to run the project
```

## API Endpoints

### Authentication
- `POST /api/register` - Register a new user
- `POST /api/login` - Login and get JWT token
- `GET /api/me` - Get current user profile
- `PUT /api/me` - Update user profile
- `PUT /api/me/password` - Change password

### Articles
- `GET /api/articles` - List user's articles
- `POST /api/articles` - Create new article
- `PUT /api/articles/{id}` - Update article
- `DELETE /api/articles/{id}` - Delete article

## Development

### Available Make Commands

```bash
# Start development environment
make dev

# View logs
make logs

# Access Symfony container
make symfony-bash

# Access MongoDB shell
make mongo-shell

# Run tests
make test
make test-unit
make test-functional
make test-coverage

# Stop containers
make down

# Clean up everything
make clean
```

### Running Tests

The project includes comprehensive test coverage:

```bash
# Run all tests
make test

# Run specific test suites
make test-unit
make test-functional

# Generate coverage report
make test-coverage
```

### Database Configuration

MongoDB connection settings can be configured in:
- `.env` for development
- `backend/.env` for the Symfony environment
- `backend/.env.test` for testing

## Security

- JWT authentication is implemented using `lexik/jwt-authentication-bundle`
- Password hashing uses Symfony's password hasher
- CORS is configured using `nelmio/cors-bundle`
- Input validation is implemented for all endpoints

## Error Handling

The API uses standardized error responses:

```json
{
    "success": false,
    "message": "Error message",
    "data": {
        "field": "Specific error description"
    }
}
```

Common HTTP status codes:
- 200: Success
- 201: Resource created
- 400: Bad request
- 401: Unauthorized
- 422: Validation error
- 404: Resource not found

