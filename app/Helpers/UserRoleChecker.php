<?php

namespace App\Helpers;

use App\Models\User;
use App\Models\CompanyDivision;

class UserRoleChecker
{
    /**
     * Check if the user has a specific role
     *
     * @param string|array $role
     * @return bool
     */
    public static function hasRole(string|array $role): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole($role);
    }

    /**
     * Check if the user belongs to a specific division
     *
     * @param int $divisionId
     * @return bool
     */
    public static function isInDivision(int $divisionId): bool
    {
        $user = auth()->user();
        return $user && $user->division_id === $divisionId;
    }

    /**
     * Check if the user belongs to a division with a specific initial
     *
     * @param string|array $divisionInitial
     * @return bool
     */
    public static function isInDivisionWithInitial(string|array $divisionInitial): bool
    {
        // Get the authenticated user
        $user = auth()->user();
        
        // Return false if no user is authenticated
        if (!$user) {
            return false;
        }
        
        // Get user's division initial using null-safe operator
        $userDivisionInitial = $user->division?->initial;
        
        // If no division assigned to user, return false
        if (!$userDivisionInitial) {
            return false;
        }
        
        // Handle both string and array inputs
        if (is_array($divisionInitial)) {
            // Check if user's division initial is in the array of initials
            return in_array($userDivisionInitial, $divisionInitial, true);
        }
        
        // Handle string input (backward compatibility)
        return $userDivisionInitial === $divisionInitial;
    }

    /**
     * Check if the user is a Super Admin
     *
     * @return bool
     */
    public static function isSuperAdmin(): bool
    {
        return self::hasRole('Super Admin');
    }
    
    /**
     * Check if the user is an Admin in their division
     *
     * @return bool
     */
    public static function isDivisionAdmin(): bool
    {
        return self::hasRole('Admin');
    }

    /**
     * Check if the user is a Head in their division
     *
     * @return bool
     */
    public static function isDivisionHead(): bool
    {
        return self::hasRole('Head');
    }

    /**
     * Check if the user is an IPC Admin
     *
     * @return bool
     */
    public static function isIpcAdmin(): bool
    {
        return self::isInDivisionWithInitial('IPC') && self::hasRole('Admin');
    }
    
    /**
     * Check if the user is an IPC Head
    *
    * @return bool
    */
    public static function isIpcHead(): bool
    {
        return self::isInDivisionWithInitial('IPC') && self::hasRole('Head');
    }
    
    /**
     * Check if the user is an GA Admin
     *
     * @return bool
     */
    public static function isGaAdmin(): bool
    {
        return self::isInDivisionWithInitial('GA') && self::hasRole('Admin');
    }
    
    /**
     * Check if the user is an HCG Head
     *
     * @return bool
     */
    public static function isHcgHead(): bool
    {
        return self::isInDivisionWithInitial('HCG') && self::hasRole('Head');
    }
    
    /**
     * Check if the user is in any Marketing division
     *
     * @return bool
     */
    public static function isInMarketingDivision(): bool
    {
        // Get the authenticated user
        $user = auth()->user();
        
        // Return false if no user is authenticated
        if (!$user) {
            return false;
        }
        
        // Get user's division name using null-safe operator
        $userDivisionName = $user->division?->name;
        
        // If no division assigned to user, return false
        if (!$userDivisionName) {
            return false;
        }
        
        // Check if the division name contains "Marketing"
        return stripos($userDivisionName, 'Marketing') !== false;
    }

    /**
     * Check if the user is an Marketing Support Head
     *
     * @return bool
     */
    public static function isMksHead(): bool
    {
        return self::isInDivisionWithInitial('MKS') && self::hasRole('Head');
    }

    /**
     * Check if the user can approve a request in their division
     *
     * @param mixed $record
     * @return bool
     */
    public static function canApproveInDivision($record): bool
    {
        $user = auth()->user();
        return $user && $user->division_id === $record->division_id;
    }

    /**
     * Check if the creator division is the same as the record division_id
     *
     * @param mixed $record
     * @return bool
     */
    public static function getCreatorDivisionId($record)
    {
        $user = auth()->user();
        return $user && $user->division_id === $record->division_id;
    }

    /**
     * Check if the user is the requester
     *
     * @param mixed $record
     * @return bool
     */
    public static function getRequesterId($record)
    {
        $user = auth()->user();
        return $user && $user->id === $record->requested_by;
    }

    /**
     * Check if the user can view a record based on their role and division
     *
     * @param mixed $record
     * @return bool
     */
    public static function canViewRecord($record): bool
    {
        $user = auth()->user();
        
        // Super admins can view everything
        if (self::isSuperAdmin()) {
            return true;
        }
        
        // Users can view records from their own division
        return $user && $user->division_id === $record->division_id;
    }

    /**
     * Check if the user can edit a record based on their role and division
     *
     * @param mixed $record
     * @return bool
     */
    public static function canEditRecord($record): bool
    {
        $user = auth()->user();
        
        // Super admins can edit everything
        if (self::isSuperAdmin()) {
            return true;
        }
        
        // Division admins can edit records from their own division
        return self::isDivisionAdmin() && $user && $user->division_id === $record->division_id;
    }

    /**
     * Get the current user's division
     *
     * @return CompanyDivision|null
     */
    public static function getCurrentUserDivision()
    {
        $user = auth()->user();
        return $user ? $user->division : null;
    }

    /**
     * Get the current user's division ID
     *
     * @return int|null
     */
    public static function getCurrentUserDivisionId()
    {
        $user = auth()->user();
        return $user ? $user->division_id : null;
    }


}