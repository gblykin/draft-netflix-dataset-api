#!/bin/bash

echo "🎬 Netflix Dataset API Setup Script"
echo "=================================="

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker and try again."
    exit 1
fi

echo "🐳 Building Docker containers..."
docker-compose up -d --build

echo "📦 Installing Composer dependencies..."
docker-compose exec composer install --no-interaction --prefer-dist --optimize-autoloader

echo "🔑 Generating application key..."
docker-compose exec app php artisan key:generate

echo "🗄️ Running database migrations..."
docker-compose exec app php artisan migrate --force

echo "✅ Setup completed!"
echo ""
echo "📡 API is now available at: http://localhost:8000/api"
echo "🌐 Web interface at: http://localhost:8000"
echo ""
echo "📊 To import CSV data, place your CSV files in the project root and run:"
echo "   docker-compose exec app php artisan import:csv users users.csv"
echo "   docker-compose exec app php artisan import:csv movies movies.csv"
echo "   docker-compose exec app php artisan import:csv reviews reviews.csv"
echo ""
echo "🧪 To run tests:"
echo "   docker-compose exec app php artisan test"

