<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LogPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissionModel = new \App\Models\PermissionModel();
        $rolePermissionModel = new \App\Models\RolePermissionModel();
        
        // Debug output
        echo "Starting LogPermissionsSeeder...\n";
        
        // Define the permissions for logs
        $permissions = [
            [
                'name' => 'view-logs',
                'description' => 'View system activity logs'
            ],
            [
                'name' => 'manage-logs',
                'description' => 'Manage and clear system activity logs'
            ]
        ];
        
        // Add permissions if they don't exist
        $addedPermissionIds = [];
        foreach ($permissions as $permission) {
            // Check if permission already exists
            $existingPermission = $permissionModel->where('name', $permission['name'])->first();
            
            if (!$existingPermission) {
                echo "Adding permission: {$permission['name']}\n";
                $permissionId = $permissionModel->insert($permission);
                $addedPermissionIds[] = $permissionId;
            } else {
                echo "Permission already exists: {$permission['name']}\n";
                $addedPermissionIds[] = $existingPermission['id'];
            }
        }
        
        // Assign permissions to admin role
        $roleModel = new \App\Models\RoleModel();
        $adminRole = $roleModel->where('name', 'admin')->first();
        
        if ($adminRole) {
            echo "Found admin role with ID: {$adminRole['id']}\n";
            
            // Assign all permissions to admin role
            foreach ($addedPermissionIds as $permissionId) {
                // Check if permission is already assigned
                $existingRolePermission = $rolePermissionModel
                    ->where('role_id', $adminRole['id'])
                    ->where('permission_id', $permissionId)
                    ->first();
                
                if (!$existingRolePermission) {
                    echo "Assigning permission ID {$permissionId} to admin role\n";
                    $rolePermissionModel->insert([
                        'role_id' => $adminRole['id'],
                        'permission_id' => $permissionId
                    ]);
                } else {
                    echo "Permission ID {$permissionId} is already assigned to admin role\n";
                }
            }
        } else {
            echo "Admin role not found. Cannot assign permissions.\n";
        }
        
        echo "LogPermissionsSeeder completed successfully.\n";
    }
}