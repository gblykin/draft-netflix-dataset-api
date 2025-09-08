<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netflix Dataset API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #e50914;
            text-align: center;
            margin-bottom: 30px;
        }
        .endpoint {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #e50914;
        }
        .method {
            font-weight: bold;
            color: #28a745;
        }
        .method.post { color: #007bff; }
        .method.put { color: #ffc107; }
        .method.delete { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Netflix Dataset REST API</h1>
        <p>Welcome to the Netflix Dataset API. Below are the available endpoints:</p>
        
        <h2>Movies</h2>
        <div class="endpoint">
            <span class="method">GET</span> /api/movies - List movies with filtering and pagination
        </div>
        <div class="endpoint">
            <span class="method">GET</span> /api/movies/{id} - Get movie details with reviews
        </div>
        
        <h2>Users</h2>
        <div class="endpoint">
            <span class="method">GET</span> /api/users - List users with their reviewed movies
        </div>
        <div class="endpoint">
            <span class="method">GET</span> /api/users/{id} - Get user details with reviews
        </div>
        
        <h2>Reviews</h2>
        <div class="endpoint">
            <span class="method">GET</span> /api/reviews - List reviews with filtering
        </div>
        <div class="endpoint">
            <span class="method">GET</span> /api/reviews/{id} - Get review details
        </div>
        <div class="endpoint">
            <span class="method post">POST</span> /api/reviews - Create a new review
        </div>
        <div class="endpoint">
            <span class="method put">PUT</span> /api/reviews/{id} - Update a review
        </div>
        <div class="endpoint">
            <span class="method delete">DELETE</span> /api/reviews/{id} - Delete a review
        </div>
        
        <h2>Query Parameters</h2>
        <p><strong>Filtering:</strong> genre, release_year, rating, country, subscription_type, age_min, age_max, etc.</p>
        <p><strong>Sorting:</strong> sort_by, sort_order (asc/desc)</p>
        <p><strong>Pagination:</strong> page, per_page (max 100)</p>
        
        <p style="text-align: center; margin-top: 30px; color: #666;">
            Laravel Test Assignment - Netflix Dataset API
        </p>
    </div>
</body>
</html>

