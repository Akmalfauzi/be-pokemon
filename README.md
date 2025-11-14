# Pokemon API Backend

A powerful Laravel 12 backend API that brings the Pokemon world to your applications. Seamlessly integrated with PokeAPI for real Pokemon data, complete with favorites management, advanced search capabilities, and rock-solid testing.

## Features

- **RESTful API** with complete Pokemon management operations
- **PokeAPI Integration** - Fetch real Pokemon data from the official Pokemon API
- **Pokemon Favorites** - Add/remove Pokemon from personal favorites
- **Advanced Search & Filtering** - Search by name, filter by type, abilities
- **Pagination Support** - Efficient data handling with customizable pagination
- **Comprehensive Validation** - Robust input validation with custom error messages
- **Caching System** - Performance optimization with intelligent caching
- **Full Test Coverage** - 89 passing tests covering all functionality
- **Clean Architecture** - Repository pattern, service layer, API resources
- **Database Migrations** - Structured database schema with SQLite support

## Requirements

- **PHP** >= 8.2
- **Composer**
- **Node.js** & **NPM** (for frontend assets)
- **SQLite** (default), **MySQL** (alternative), or **MongoDB** (NoSQL)

## Installation

1. **Install Dependencies**
```bash
composer install
npm install
```

2. **Environment Configuration**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Database Setup**

**For SQLite (Default):**
```bash
php artisan migrate
```

**For MySQL:**
```bash
# Update your .env file:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Then run migrations
php artisan migrate
```

**For MongoDB:**
```bash
# Install MongoDB PHP driver first:
# Ubuntu/Debian: sudo apt-get install php-mongodb
# macOS: brew install mongodb && pecl install mongodb

# Update your .env file:
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=your_database

# Also update config/database.php to add MongoDB connection:
'connections' => [
    // ... other connections

    'mongodb' => [
        'driver' => 'mongodb',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', 27017),
        'database' => env('DB_DATABASE'),
        'options' => [
            'database' => 'admin' // sets the authentication database required by some versions of MongoDB
        ]
    ],
],

# Then run migrations
php artisan migrate
```

4. **Run Migrations (with optional seeding)**
```bash
# Run migrations only
php artisan migrate

# Or run migrations with basic seeding
php artisan migrate --seed
```

5. **Build Frontend Assets**
```bash
npm run build
```

6. **Start Development Server**
```bash
# Full development environment (PHP, queue, logs, Vite)
composer run dev

# Or only the PHP server
php artisan serve
```

## API Documentation

### Base URL
```
http://localhost:8000/api
```

### Pokemon Endpoints

#### Get All Pokemons
```http
GET /api/pokemons
```

**Query Parameters:**
- `page` (integer) - Page number (default: 1)
- `per_page` (integer) - Items per page (default: 20, max: 50)
- `search` (string) - Search by Pokemon name
- `type` (string) - Filter by Pokemon type

**Example:**
```bash
curl "http://localhost:8000/api/pokemons?page=1&per_page=10&search=pikachu&type=electric"
```

**Response:**
```json
{
  "success": true,
  "message": "Pokemons retrieved successfully",
  "data": {
    "data": [
      {
        "id": 25,
        "name": "pikachu",
        "pokedex_number": 25,
        "image_url": "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/25.png",
        "types": ["electric"],
        "abilities": ["static", "lightning-rod"],
        "height": 4,
        "weight": 60,
        "hp": 35,
        "attack": 55,
        "defense": 40,
        "special_attack": 50,
        "special_defense": 50,
        "speed": 90,
        "base_experience": 112
      }
    ],
    "total": 1000,
    "current_page": 1,
    "per_page": 10,
    "last_page": 100
  }
}
```

#### Get Pokemon by ID/Name
```http
GET /api/pokemons/{pokemon}
```

**Example:**
```bash
curl "http://localhost:8000/api/pokemons/25"
curl "http://localhost:8000/api/pokemons/pikachu"
```

#### Get Favorites
```http
GET /api/pokemons/favorites
```

**Query Parameters:**
- `search` (string) - Search favorites by name
- `abilities` (string|array) - Filter by abilities (comma-separated or array)

**Example:**
```bash
curl "http://localhost:8000/api/pokemons/favorites?search=pikachu&abilities=static"
```

#### Get Favorite Abilities
```http
GET /api/pokemons/favorites/abilities
```

**Example:**
```bash
curl "http://localhost:8000/api/pokemons/favorites/abilities"
```

#### Get Pokemons by Ability
```http
GET /api/pokemons/by-ability/{ability}
```

**Example:**
```bash
curl "http://localhost:8000/api/pokemons/by-ability/static"
```

### Favorites Management

#### Add to Favorites
```http
POST /api/pokemons/{pokemon}/favorite
```

**Example:**
```bash
curl -X POST "http://localhost:8000/api/pokemons/25/favorite"
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Pokemon added to favorites successfully",
  "data": {
    "id": 25,
    "name": "pikachu",
    "pokedex_number": 25,
    "types": ["electric"],
    "abilities": ["static", "lightning-rod"],
    // ... other pokemon data
  }
}
```

#### Remove from Favorites
```http
DELETE /api/pokemons/{pokemon}/favorite
```

**Example:**
```bash
curl -X DELETE "http://localhost:8000/api/pokemons/25/favorite"
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Pokemon removed from favorites successfully"
}
```

### Error Response Format

All error responses follow this structure:

```json
{
  "success": false,
  "message": "Error description",
  "data": null
}
```

**Common HTTP Status Codes:**
- `200` - Success
- `201` - Created (for successful POST requests)
- `404` - Not Found
- `409` - Conflict (for duplicate favorites)
- `422` - Validation Error
- `500` - Internal Server Error

## Database Schema

### favorite_pokemons Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Primary key |
| `pokemon_id` | integer (unique) | Pokemon ID from PokeAPI |
| `pokemon_name` | string | Pokemon name |
| `pokemon_data` | json | Complete Pokemon data as JSON |
| `created_at` | timestamp | Creation timestamp |
| `updated_at` | timestamp | Last update timestamp |

## Testing

### Running Tests

```bash
# Run all tests
composer run test
# or
php artisan test

# Run specific test file
php artisan test tests/Feature/PokemonApiFeatureTest.php

# Run tests with filter
php artisan test --filter PokemonApiFeatureTest
```

## Project Architecture

### Directory Structure

```
app/
├── Contracts/
│   ├── ApiResponseHandlerContract.php
│   ├── FavoriteRepositoryContract.php
│   └── PokeApiServiceContract.php
├── Handlers/
│   └── JsonApiResponseHandler.php
├── Http/
│   ├── Controllers/
│   │   ├── API/
│   │   │   └── PokemonController.php      # API endpoint handlers
│   │   └── Controller.php
│   ├── Requests/
│   │   ├── AddFavoritePokemonRequest.php
│   │   └── RemoveFavoritePokemonRequest.php
│   └── Resources/
│       ├── FavoritePokemonResource.php
│       └── PokemonResource.php
├── Models/
│   ├── FavoritePokemon.php              # Eloquent model
│   ├── Mongo/
│   │   └── FavoritePokemonMongo.php      # MongoDB model
│   └── User.php
├── Providers/
│   └── AppServiceProvider.php
├── Repositories/
│   ├── Eloquent/
│   │   └── FavoritePokemonRepository.php
│   └── Mongo/
│       └── FavoritePokemonMongoRepository.php
└── Services/
    └── PokeApiService.php                # PokeAPI integration

database/
├── factories/
│   ├── FavoritePokemonFactory.php
│   └── UserFactory.php
├── migrations/
│   ├── 0001_01_01_000000_create_cache_table.php
│   ├── 0001_01_01_000001_create_users_table.php
│   ├── 0001_01_01_000002_create_jobs_table.php
│   ├── 2025_11_12_070146_create_personal_access_tokens_table.php
│   └── 2025_11_13_150350_create_favorite_pokemons_table.php
└── seeders/
    └── DatabaseSeeder.php

tests/
├── Feature/
│   ├── ExampleTest.php
│   └── PokemonApiFeatureTest.php         # API endpoint tests
├── Unit/
│   ├── ExampleTest.php
│   ├── FavoritePokemonRepositoryTest.php
│   ├── PokeApiServiceTest.php
│   ├── Requests/
│   │   ├── AddFavoritePokemonRequestTest.php
│   │   └── RemoveFavoritePokemonRequestTest.php
│   └── Resources/
│       ├── FavoritePokemonResourceTest.php
│       └── PokemonResourceTest.php
└── TestCase.php
```

### Design Patterns

- **Repository Pattern** - Abstract data access layer
- **Service Layer** - Business logic separation
- **API Resources** - Response transformation
- **Form Request Validation** - Input validation
- **Dependency Injection** - Loose coupling
- **Contract Programming** - Interface-based design

## Development Commands

### Available Commands

```bash
# Setup and Installation
composer run setup              # Complete project setup

# Development Server
composer run dev                 # Start full dev environment (PHP, queue, logs, Vite)
php artisan serve               # Start only PHP server
npm run dev                     # Start Vite for frontend assets

# Testing
composer run test               # Run all tests
./vendor/bin/pint              # Format code with Laravel Pint
./vendor/bin/pint --test      # Check code style

# Database Operations
php artisan migrate             # Run database migrations
php artisan migrate:fresh       # Fresh database with all migrations
php artisan migrate --seed      # Run migrations with basic seeding

# Cache Management
php artisan cache:clear         # Clear application cache
php artisan config:clear        # Clear configuration cache
php artisan route:clear         # Clear route cache
```

## Configuration

### Environment Variables

Key environment variables in `.env`:

```env
# Database
DB_CONNECTION=sqlite          # or mysql
DB_DATABASE=database/database.sqlite  # or your MySQL database

# Cache
CACHE_DRIVER=file             # or redis, memcached

# Application
APP_ENV=local                # or production
APP_DEBUG=true               # false in production
```

### Database Configuration

**SQLite (Default):**
```env
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

**MySQL (Alternative):**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**MongoDB (NoSQL):**
```env
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=your_database
```

## Deployment

### Production Setup

1. **Environment Setup**
```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

2. **Database Setup**
```bash
php artisan migrate --force
php artisan migrate --seed --force
```

3. **Asset Compilation**
```bash
npm run build
npm run prod
```

4. **Queue Processing** (if using)
```bash
php artisan queue:work --daemon
```

## API Examples

### Common Usage Patterns

1. **Paginated Pokemon List**
```bash
curl "http://localhost:8000/api/pokemons?page=2&per_page=5"
```

2. **Search and Filter**
```bash
curl "http://localhost:8000/api/pokemons?search=char&type=fire"
```

3. **Manage Favorites**
```bash
# Add to favorites
curl -X POST "http://localhost:8000/api/pokemons/6/favorite"

# View favorites
curl "http://localhost:8000/api/pokemons/favorites"

# Remove from favorites
curl -X DELETE "http://localhost:8000/api/pokemons/6/favorite"
```

4. **Advanced Filtering**
```bash
# Get favorites with specific abilities
curl "http://localhost:8000/api/pokemons/favorites?abilities=static,overgrow"

# Get all Pokemons by ability
curl "http://localhost:8000/api/pokemons/by-ability/fire"
```

## License

This project is open-sourced software licensed under the MIT license.
