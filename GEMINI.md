# MJG ATK - Inventory Management System

## Project Overview

This is a Laravel-based inventory management system for MJG company, specifically designed to manage office stationery items across different company divisions. The system uses FilamentPHP v3 as its admin panel for managing resources through a user-friendly interface.

### Key Features

1. **Division-based Inventory Management**:
   - Track stationery items per company division
   - Define inventory settings for each division/item combination
   - Monitor current stock levels

2. **Stock Movement Tracking**:
   - Stock requests for increasing inventory
   - Stock usage for recording consumption
   - Complete history of all stock movements (newly implemented)

3. **Approval Workflows**:
   - Multi-level approval system for stock requests
   - Division head and IPC (Inventory Processing Center) approvals

4. **Filament Admin Panel**:
   - Comprehensive admin interface built with FilamentPHP v3
   - Role-based permissions using Spatie permissions package
   - Custom resources for all inventory entities

### Core Models

1. **CompanyDivision**: Represents different departments/divisions within the company
2. **OfficeStationeryCategory**: Categories for office items (e.g., Writing Tools, Paper Products)
3. **OfficeStationeryItem**: Individual stationery items (e.g., Pens, A4 Paper)
4. **DivisionStock**: Tracks current stock levels of items in each division
5. **DivisionInventorySetting**: Defines inventory policies (minimum/maximum stock) for division/item combinations
6. **StockRequest**: Requests to increase stock levels (with approval workflow)
7. **StockUsage**: Records of stock consumption
8. **StockHistory**: Complete history of all stock movements (newly implemented)

### Technology Stack

- **Backend**: Laravel 12.x (PHP 8.2+)
- **Admin Panel**: FilamentPHP v3
- **Frontend**: Blade templates with TailwindCSS
- **Database**: MySQL/MariaDB (using Laravel migrations)
- **Authentication**: Laravel's built-in auth with Spatie permissions
- **Development Tools**: 
  - Laravel Pint (code formatting)
  - PestPHP (testing)
  - Vite (asset bundling)

## Development Environment

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MySQL/MariaDB database
- Laravel Valet, Laravel Sail, or another local development environment

### Setup Instructions

1. **Clone the repository**:
   ```
   git clone <repository-url>
   cd mjg-atk
   ```

2. **Install PHP dependencies**:
   ```
   composer install
   ```

3. **Install Node dependencies**:
   ```
   npm install
   ```

4. **Copy and configure environment file**:
   ```
   cp .env.example .env
   php artisan key:generate
   ```
   
5. **Configure database** in `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mjg_atk
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Run database migrations**:
   ```
   php artisan migrate
   ```

7. **Seed the database** (optional):
   ```
   php artisan db:seed
   ```

8. **Create a Filament admin user**:
   ```
   php artisan make:filament-user
   ```

### Development Workflow

1. **Start development server**:
   ```
   php artisan serve
   ```

2. **Compile assets** (in another terminal):
   ```
   npm run dev
   ```

3. **Or use the combined dev command**:
   ```
   composer run dev
   ```

### Building for Production

1. **Compile and minify frontend assets**:
   ```
   npm run build
   ```

2. **Optimize autoloader**:
   ```
   composer install --optimize-autoloader --no-dev
   ```

3. **Run optimization commands**:
   ```
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

## Code Structure

### Key Directories

- `app/Models/`: Eloquent models for all entities
- `app/Filament/Resources/`: Filament admin resources for each model
- `app/Filament/Resources/*/Pages/`: CRUD pages for each resource
- `app/Filament/Resources/*/RelationManagers/`: Related data managers
- `database/migrations/`: Database schema definitions
- `resources/views/`: Blade templates
- `routes/`: Route definitions
- `tests/`: PestPHP test files

### Recent Implementation: Stock History Tracking

A new `StockHistory` model and resource have been implemented to track all stock movements:

1. **Model**: `app/Models/StockHistory.php`
   - Tracks division_stock_id, stock_usage_id, stock_request_id
   - Records user who performed the action
   - Stores quantity, before_quantity, after_quantity
   - Captures movement type (in, out, adjustment)

2. **Migration**: `database/migrations/*_create_stock_histories_table.php`
   - Foreign key constraints to related entities
   - Indexes for performance optimization

3. **Filament Resource**: `app/Filament/Resources/StockHistoryResource.php`
   - Read-only interface (no create/edit)
   - Detailed table view with filtering
   - Integration with related entities (Usage, Request)

4. **Relationships**: Added `histories()` relationship to:
   - `DivisionStock` model
   - `StockUsage` model
   - `StockRequest` model

5. **UI Integration**: Added StockHistoriesRelationManager to DivisionStockResource for viewing history within context

## Testing

### Running Tests

```
composer run test
```

### Writing Tests

- Tests are written using PestPHP
- Test files are located in the `tests/` directory
- Feature tests should cover all critical workflows

## Code Quality

### Code Formatting

The project uses Laravel Pint for code formatting:

```
./vendor/bin/pint
```

### Code Analysis

Static analysis can be performed using PHPStan or Psalm (if configured).

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and code formatting
5. Submit a pull request

## License

This project is open-sourced software licensed under the MIT license.