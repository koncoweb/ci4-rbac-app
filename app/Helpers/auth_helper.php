<?php

/**
 * Check if user is logged in
 *
 * @return bool
 */
function is_logged_in()
{
    return session()->has('isLoggedIn');
}

/**
 * Get current user ID
 *
 * @return int|null
 */
function user_id()
{
    return session()->get('user_id');
}

/**
 * Check if user has a specific role
 *
 * @param string|array $roles Role name or array of role names
 * @return bool
 */
function has_role($roles)
{
    if (!is_logged_in()) {
        return false;
    }
    
    $userRoles = session()->get('roles');
    
    if (empty($userRoles)) {
        return false;
    }
    
    if (is_string($roles)) {
        return in_array($roles, $userRoles);
    }
    
    // Check if user has any of the roles in the array
    foreach ($roles as $role) {
        if (in_array($role, $userRoles)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Check if user has permission via their roles
 *
 * @param string $permission Permission name
 * @return bool
 */
function has_permission($permission)
{
    if (!is_logged_in()) {
        return false;
    }
    
    $db = \Config\Database::connect();
    $userId = user_id();
    
    $permissionExists = $db->table('user_roles')
        ->join('role_permissions', 'role_permissions.role_id = user_roles.role_id')
        ->join('permissions', 'permissions.id = role_permissions.permission_id')
        ->where('user_roles.user_id', $userId)
        ->where('permissions.name', $permission)
        ->countAllResults();
    
    return $permissionExists > 0;
}

/**
 * Alias for has_permission - to support camelCase naming convention
 *
 * @param string $permission Permission name
 * @return bool
 */
function hasPermission($permission)
{
    return has_permission($permission);
}

/**
 * Set a flash message to be displayed on the next page load
 * 
 * @param string $type The type of message (success, error, info, warning)
 * @param string $message The message content
 * @return void
 */
function set_flash_message($type, $message) {
    $session = \Config\Services::session();
    $session->setFlashdata($type, $message);
}