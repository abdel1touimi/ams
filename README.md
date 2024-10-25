# Article Management System

A full-stack application built with Symfony 6.4 backend (MongoDB) and Next.js frontend for managing articles with user authentication.

## Features

### Backend
- RESTful API built with Symfony 6.4
- MongoDB integration for data storage
- JWT authentication
- Article management (CRUD operations)
- Comprehensive validation
- Test coverage with PHPUnit
- Docker-based development environment

### Frontend
- Modern UI built with Next.js
- TypeScript support
- Tailwind CSS for styling
- Responsive design
- JWT-based authentication
- Real-time form validation
- Article management interface

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
cp backend/.env.template backend/.env
```

3. Update the environment variables in `.env`:
- Adjust values as needed (keys, ports, database credentials, etc.)

4. Initialize the project using Make:
```bash
make init
```

This command will:
- Build and start Docker containers
- Install Composer dependencies
- Generate JWT keys
- Clear Symfony cache
- Install frontend dependencies
- Build frontend assets (in production mode)

## Project Structure

```
├── backend/                # Symfony backend application
│   ├── src/
│   │   ├── Controller/    # API endpoints
│   │   ├── Document/      # MongoDB document classes
│   │   ├── DTO/           # Data Transfer Objects
│   │   ├── Exception/     # Custom exceptions
│   │   ├── Repository/    # Data access layer
│   │   ├── Response/      # API response formatting
│   │   ├── Serializer/    # Data serialization
│   │   ├── Service/       # Business logic
│   │   └── Validator/     # Input validation
│   └── tests/
│       ├── Unit/          # Unit tests
│       └── Functional/    # Functional tests
│
├── frontend/              # Next.js frontend application
│   └── src
│       ├── app/           # Main application layout
│       ├── components/    # Reusable components
│       ├── context/       # Context providers
│       ├── services/      # API services
│       ├── types/         # TypeScript types
│       └── utils/         # Utility functions
│
├── configs/              # Configuration files
│   ├── nginx/           # Nginx configuration
│   ├── php/             # PHP configuration
│   └── node/            # Node.js configuration
│
├── docker-compose.yml    # Docker composition
└── Makefile              # Make commands
```

## Accessing the Application

After running `make up`, you can access:
- Frontend: `http://localhost`
- Backend API: `http://localhost/api`

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
- `GET /api/articles/{id}` - Get article details
- `PUT /api/articles/{id}` - Update article
- `DELETE /api/articles/{id}` - Delete article

## Development

### Available Make Commands

```bash
# Start development environment
make up

# Stop environment
make down

# View logs
make logs

# Backend Commands
make symfony-bash         # Access Symfony container
make composer-install     # Install PHP dependencies
make symfony-cache-clear  # Clear Symfony cache

# Frontend Commands
make frontend-dev        # Start frontend development server
make frontend-build      # Build frontend for production
make frontend-lint       # Run frontend linting
make frontend-lint-fix   # Fix frontend linting issues

# Database Commands
make mongo-shell        # Access MongoDB shell

# Testing Commands
make test              # Run all tests
make test-unit         # Run unit tests
make test-functional   # Run functional tests
make test-coverage     # Generate test coverage report

# Utility Commands
make clean            # Clean up everything
make restart          # Restart all containers
make validate         # Validate project configuration
```

### Development Workflow

1. Start the development environment:
```bash
make up
```

2. For frontend development with hot reloading:
```bash
make frontend-dev
```

3. For backend development, you can access the Symfony container:
```bash
make symfony-bash
```

### Testing

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

## Configuration

### Backend Configuration
- Main configuration: `backend/.env`
- Test configuration: `backend/.env.test`
- PHP configuration: `configs/php/php.ini`
- Nginx configuration: `configs/nginx/default.conf`

### Frontend Configuration
- Environment variables: `.env`
- Next.js configuration: `frontend/next.config.js`
- TypeScript configuration: `frontend/tsconfig.json`
- Nginx configuration: `configs/nginx/default.conf`

### Database Configuration
MongoDB connection settings can be configured in:
- `.env` for development
- `backend/.env` for the Symfony environment
- `backend/.env.test` for testing

## Security

- JWT authentication using `lexik/jwt-authentication-bundle`
- Password hashing using Symfony's password hasher
- CORS configuration using `nelmio/cors-bundle`
- Input validation on both frontend and backend
- Secure HTTP headers with Nginx configuration

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
