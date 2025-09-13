# User Role Checker Helper

This helper class provides a centralized way to check user roles and divisions throughout the application, eliminating the need to manually type repetitive conditions.

## Installation

The UserRoleChecker is automatically registered via the `StockNumberServiceProvider`. No additional setup is required.

## Usage

Instead of manually checking roles and divisions like this:

```php
// Old way - manual conditions
auth()->user()->hasRole('Head') && auth()->user()->division_id === $record->division_id

auth()->user()->division?->initial === 'IPC' && auth()->user()->hasRole('Admin')
```

You can now use the UserRoleChecker methods:

```php
// New way - using UserRoleChecker
UserRoleChecker::isDivisionHead() && UserRoleChecker::canApproveInDivision($record)

UserRoleChecker::isIpcAdmin()
```

## Available Methods

### Role Checking Methods

- `hasRole(string $role)`: Check if the user has a specific role
- `isDivisionHead()`: Check if the user is a Head in their division
- `isDivisionAdmin()`: Check if the user is an Admin in their division
- `isSuperAdmin()`: Check if the user is a Super Admin
- `isIpcAdmin()`: Check if the user is an IPC Admin
- `isIpcHead()`: Check if the user is an IPC Head

### Division Checking Methods

- `isInDivision(int $divisionId)`: Check if the user belongs to a specific division
- `isInDivisionWithInitial(string|array $divisionInitial)`: Check if the user belongs to a division with a specific initial. Accepts either a single string or an array of division initials. When an array is provided, returns true if the user belongs to any of the specified divisions.
- `canApproveInDivision($record)`: Check if the user can approve a request in their division
- `canViewRecord($record)`: Check if the user can view a record based on their role and division
- `canEditRecord($record)`: Check if the user can edit a record based on their role and division
- `getCurrentUserDivision()`: Get the current user's division
- `getCurrentUserDivisionId()`: Get the current user's division ID

## Examples

### In Filament Pages

```php
// Before
Action::make('approve_as_head')
    ->visible(fn($record) => $record->status === OfficeStationeryStockRequest::STATUS_PENDING && auth()->user()->hasRole('Head') && auth()->user()->division_id === $record->division_id)

// After
Action::make('approve_as_head')
    ->visible(fn($record) => $record->status === OfficeStationeryStockRequest::STATUS_PENDING && UserRoleChecker::isDivisionHead() && UserRoleChecker::canApproveInDivision($record))
```

### In Controllers

```php
// Before
if (auth()->user()->hasRole('Admin') && auth()->user()->division_id === $request->division_id) {
    // Allow action
}

// After
if (UserRoleChecker::isDivisionAdmin() && UserRoleChecker::canApproveInDivision($request)) {
    // Allow action
}
```

## Benefits

1. **Consistency**: All role and division checks use the same standardized methods
2. **Maintainability**: Changes to role checking logic only need to be made in one place
3. **Readability**: Code is more readable and self-documenting
4. **Reduced Errors**: Less chance of typos or inconsistencies in manual conditions
5. **Extensibility**: Easy to add new role checking methods as needed

## Adding New Methods

To add new methods to the UserRoleChecker:

1. Open `app/Helpers/UserRoleChecker.php`
2. Add your new method following the existing patterns
3. Use the method throughout your codebase

Example of adding a new method:

```php
/**
 * Check if the user is a GA Admin
 *
 * @return bool
 */
public static function isGaAdmin(): bool
{
    return self::isInDivisionWithInitial('GA') && self::hasRole('Admin');
}

/**
 * Check if the user is in either IPC or GA division
 *
 * @return bool
 */
public static function isIpcOrGaDivision(): bool
{
    return self::isInDivisionWithInitial(['IPC', 'GA']);
}