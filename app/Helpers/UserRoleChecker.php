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
     * @param string $divisionInitial
     * @return bool
     */
    public static function isInDivisionWithInitial(string $divisionInitial): bool
    {
        $user = auth()->user();
        return $user && $user->division && $user->division->initial === $divisionInitial;
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
     * Check if the user is an Admin in their division
     *
     * @return bool
     */
    public static function isDivisionAdmin(): bool
    {
        return self::hasRole('Admin');
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