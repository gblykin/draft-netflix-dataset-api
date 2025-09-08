#!/bin/bash

echo "🧪 Running Laravel Tests with SQLite in-memory database..."
echo "========================================================="

docker-compose exec app bash -c 'APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test'

echo ""
echo "✅ Tests completed!"
echo ""
echo "💡 To run tests manually, use:"
echo "   docker-compose exec app bash -c 'APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test'"

