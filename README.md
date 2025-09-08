# Netflix Dataset REST API - Laravel Test Assignment

A comprehensive REST API built with PHP/Laravel to access and manipulate Netflix dataset data, featuring relational database modeling, CSV data import, and Docker containerization.

## 🎯 Objective

This project demonstrates proficiency in:
- Relational database modeling (many-to-many relationships)
- Data import from CSV files
- REST API best practices (filtering, pagination, CRUD operations)
- Docker containerization
- Laravel framework best practices

## 📊 Dataset

The API uses 3 CSV files from the Netflix 2025 User Behavior dataset:
- `movies.csv` - Movie information (title, genre, release year, budget, etc.)
- `users.csv` - User profiles (demographics, subscription details)
- `reviews.csv` - User reviews and ratings for movies

## 🏗️ Database Schema

### Tables
- **users**: User profiles and subscription information
- **movies**: Movie catalog with metadata
- **reviews**: User reviews (many-to-many relationship between users and movies)

### Key Relationships
- Users ↔ Movies (many-to-many via reviews table)
- Each review belongs to one user and one movie
- Users can have multiple reviews
- Movies can have multiple reviews from different users

## 🚀 Quick Start with Docker

### Prerequisites
- Docker
- Docker Compose

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd testtask
   ```

2. **Build and start the containers**
   ```bash
   docker-compose up -d --build
   ```

3. **Install dependencies**
   ```bash
   docker-compose exec composer install
   ```

4. **Generate application key**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

5. **Run database migrations**
   ```bash
   docker-compose exec app php artisan migrate
   ```

6. **Import CSV data** (place CSV files in the project root)
   ```bash
   # Import users first
   docker-compose exec app php artisan import:csv users users.csv
   
   # Import movies
   docker-compose exec app php artisan import:csv movies movies.csv
   
   # Import reviews (must be done after users and movies)
   docker-compose exec app php artisan import:csv reviews reviews.csv
   ```

7. **Access the API**
   - API Base URL: http://localhost:8000/api
   - Web Interface: http://localhost:8000

## 📡 API Endpoints

### Movies
- `GET /api/movies` - List movies with filtering and pagination
- `GET /api/movies/{id}` - Get movie details with reviews and users

**Query Parameters for Movies:**
- `genre` - Filter by genre (partial match)
- `release_year` - Filter by release year
- `rating` - Filter by MPAA rating
- `country` - Filter by country (partial match)
- `language` - Filter by language (partial match)
- `sort_by` - Sort by field (title, release_year, duration, budget, revenue)
- `sort_order` - Sort order (asc, desc)
- `page` - Page number
- `per_page` - Items per page (max 100)

### Users
- `GET /api/users` - List users with their reviewed movies
- `GET /api/users/{id}` - Get user details with reviews and movies

**Query Parameters for Users:**
- `subscription_type` - Filter by subscription type
- `country` - Filter by country (partial match)
- `gender` - Filter by gender
- `age_min` - Minimum age filter
- `age_max` - Maximum age filter
- `sort_by` - Sort by field (user_id, age, join_date, monthly_revenue)
- `sort_order` - Sort order (asc, desc)
- `page` - Page number
- `per_page` - Items per page (max 100)

### Reviews
- `GET /api/reviews` - List reviews with filtering
- `GET /api/reviews/{id}` - Get review details
- `POST /api/reviews` - Create a new review
- `PUT /api/reviews/{id}` - Update a review
- `DELETE /api/reviews/{id}` - Delete a review

**Query Parameters for Reviews:**
- `user_id` - Filter by user ID
- `movie_id` - Filter by movie ID
- `rating` - Filter by exact rating
- `rating_min` - Minimum rating filter
- `rating_max` - Maximum rating filter
- `sort_by` - Sort by field (review_date, rating, helpfulness)
- `sort_order` - Sort order (asc, desc)
- `page` - Page number
- `per_page` - Items per page (max 100)

## 🛠️ Example API Calls

### Get Movies with Filtering
```bash
# Get action movies from 2023
curl "http://localhost:8000/api/movies?genre=Action&release_year=2023"

# Get movies sorted by release year (newest first) with pagination
curl "http://localhost:8000/api/movies?sort_by=release_year&sort_order=desc&per_page=10&page=1"
```

### Get Movie Details
```bash
# Get specific movie with reviews and users
curl "http://localhost:8000/api/movies/movie_123"
```

### Get Users with Filtering
```bash
# Get premium subscribers from USA
curl "http://localhost:8000/api/users?subscription_type=Premium&country=USA"

# Get users aged 25-35
curl "http://localhost:8000/api/users?age_min=25&age_max=35"
```

### Create a New Review
```bash
curl -X POST "http://localhost:8000/api/reviews" \
  -H "Content-Type: application/json" \
  -d '{
    "review_id": "new_review_123",
    "user_id": "user_456",
    "movie_id": "movie_789",
    "rating": 5,
    "review_text": "Excellent movie!",
    "review_date": "2024-01-15",
    "helpfulness": 0
  }'
```

### Update a Review
```bash
curl -X PUT "http://localhost:8000/api/reviews/review_123" \
  -H "Content-Type: application/json" \
  -d '{
    "rating": 4,
    "review_text": "Good movie, but not perfect.",
    "helpfulness": 5
  }'
```

### Delete a Review
```bash
curl -X DELETE "http://localhost:8000/api/reviews/review_123"
```

## 🧪 Testing

Run the test suite:
```bash
# Method 1: Using the test script (easiest)
./test.sh

# Method 2: Using environment variables directly
docker-compose exec app bash -c 'APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test'
```

The test suite includes:
- API endpoint functionality tests
- Filtering and pagination tests
- Data validation tests
- Error handling tests

## 🐳 Docker Services

The application includes the following Docker services:

- **app**: Laravel 12 application (PHP 8.4-FPM)
- **db**: MySQL 8.0 database
- **composer**: Composer dependency manager
- **artisan**: Laravel Artisan command runner

## 📁 Project Structure

```
├── app/
│   ├── Console/Commands/     # CSV import commands
│   ├── Http/
│   │   ├── Controllers/      # API controllers
│   │   └── Resources/        # API response resources
│   └── Models/               # Eloquent models
├── database/
│   └── migrations/           # Database migrations
├── docker/                   # Docker configuration files
├── routes/
│   └── api.php              # API routes
├── tests/                   # Unit and feature tests
├── docker-compose.yml       # Docker services configuration
└── README.md               # This file
```

## 🔧 Development Commands

### Database Operations
```bash
# Run migrations
docker-compose exec app php artisan migrate

# Rollback migrations
docker-compose exec app php artisan migrate:rollback

# Fresh migration (drops all tables and recreates)
docker-compose exec app php artisan migrate:fresh
```

### Data Import
```bash
# Import CSV data
docker-compose exec app php artisan import:csv {type} {file}
# Types: users, movies, reviews
```

### Laravel Commands
```bash
# Generate application key
docker-compose exec app php artisan key:generate

# Clear application cache
docker-compose exec app php artisan cache:clear

# List all routes
docker-compose exec app php artisan route:list
```

## 🏆 Features Implemented

### Core Requirements
- ✅ Relational database schema with proper foreign keys
- ✅ Database migrations
- ✅ CSV data import scripts
- ✅ REST API with filtering and pagination
- ✅ CRUD operations for reviews
- ✅ Docker containerization
- ✅ Comprehensive documentation

### Bonus Features
- ✅ Laravel Resource classes for API responses
- ✅ Unit tests for API endpoints
- ✅ Advanced filtering and sorting options
- ✅ Error handling and validation
- ✅ Clean, maintainable code structure

## 🚨 Error Handling

The API includes comprehensive error handling:
- **404**: Resource not found
- **422**: Validation errors with detailed messages
- **500**: Server errors with appropriate logging

## 📝 API Response Format

All API responses follow a consistent JSON format:

```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

## 📊 Import Logging

The system includes dedicated import logging for better debugging and monitoring.

### Log Files

- **General Laravel Log**: `storage/logs/laravel.log` - General application errors
- **Import Log**: `storage/logs/import.log` - Dedicated import activities, errors, and summaries

### Import Log Viewer

Use the provided script to easily view import logs:

```bash
# View help
./view-import-logs.sh help

# Follow logs in real-time
./view-import-logs.sh tail

# Show only errors and warnings
./view-import-logs.sh errors

# Show import summaries
./view-import-logs.sh summary

# Show last 20 entries
./view-import-logs.sh last

# Clear import log
./view-import-logs.sh clear

# Show log statistics
./view-import-logs.sh count
```

### Import Log Contents

The import log includes:

- **Import Start**: File information, headers, total records
- **Validation Errors**: Detailed validation failures with raw and transformed data
- **Write Errors**: Database write failures with full error details
- **Processing Errors**: General processing exceptions
- **Import Summary**: Success/failure statistics and performance metrics

Example log entry for validation error:
```json
{
  "row": 1,
  "errors": ["Age must be a number between 0 and 150"],
  "raw_data": {"user_id": "user_00001", "age": "999.0", ...},
  "transformed_data": {"user_id": "user_00001", "age": 999, ...},
  "timestamp": "2025-09-07T23:08:47.716007Z"
}
```

## 🤝 Contributing

This is a test assignment project. The code follows Laravel best practices for:
- Controller organization
- Model relationships
- Resource transformations
- Database migrations
- API design patterns

## 📄 License

This project is created for educational and assessment purposes.
