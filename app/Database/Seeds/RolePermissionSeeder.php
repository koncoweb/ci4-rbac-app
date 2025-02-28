<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Get all permissions
        $permissions = $db->table('permissions')->get()->getResultArray();
        
        // 1. Assign all permissions to admin role (ID: 1)
        $adminRolePermissions = [];
        foreach ($permissions as $permission) {
            $adminRolePermissions[] = [
                'role_id' => 1,
                'permission_id' => $permission['id'],
            ];
        }
        $db->table('role_permissions')->insertBatch($adminRolePermissions);
        
        // 2. Assign selected permissions to manager role (ID: 2)
        $managerPermissions = [
            'users.view', 'users.create', 'users.edit',
            'dashboard.view', 'logs.view'
        ];
        
        $managerRolePermissions = [];
        foreach ($permissions as $permission) {
            if (in_array($permission['name'], $managerPermissions)) {
                $managerRolePermissions[] = [
                    'role_id' => 2,
                    'permission_id' => $permission['id'],
                ];
            }
        }
        $db->table('role_permissions')->insertBatch($managerRolePermissions);
        
        // 3. Assign basic permissions to user role (ID: 3)
        $userPermissions = [
            'dashboard.view'
        ];
        
        $userRolePermissions = [];
        foreach ($permissions as $permission) {
            if (in_array($permission['name'], $userPermissions)) {
                $userRolePermissions[] = [
                    'role_id' => 3,
                    'permission_id' => $permission['id'],
                ];
            }
        }
        $db->table('role_permissions')->insertBatch($userRolePermissions);
    }
}