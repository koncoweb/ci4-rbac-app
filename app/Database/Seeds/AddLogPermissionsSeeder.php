<?php
// Add this script to: /Applications/MAMP/htdocs/ci4-rbac-app/app/Database/Seeds/AddLogPermissionsSeeder.php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AddLogPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissionsData = [
            [
                'name' => 'view-logs',
                'description' => 'View activity logs'
            ],
            [
                'name' => 'manage-logs',
                'description' => 'Manage and clear activity logs'
            ]
        ];

        // Check if permissions already exist
        foreach ($permissionsData as $permission) {
            $existing = $this->db->table('permissions')
                ->where('name', $permission['name'])
                ->get()
                ->getRowArray();
                
            if (!$existing) {
                $this->db->table('permissions')->insert($permission);
                echo "Added permission: " . $permission['name'] . "\n";
            } else {
                echo "Permission already exists: " . $permission['name'] . "\n";
            }
        }
        
        // Optionally: Assign permissions to admin role
        $adminRoleId = $this->db->table('roles')
            ->where('name', 'admin')
            ->get()
            ->getRowArray()['id'] ?? null;
            
        if ($adminRoleId) {
            foreach ($permissionsData as $permission) {
                $permId = $this->db->table('permissions')
                    ->where('name', $permission['name'])
                    ->get()
                    ->getRowArray()['id'] ?? null;
                    
                if ($permId) {
                    $existing = $this->db->table('role_permissions')
                        ->where('role_id', $adminRoleId)
                        ->where('permission_id', $permId)
                        ->get()
                        ->getRowArray();
                        
                    if (!$existing) {
                        $this->db->table('role_permissions')->insert([
                            'role_id' => $adminRoleId,
                            'permission_id' => $permId
                        ]);
                        echo "Assigned permission " . $permission['name'] . " to admin role\n";
                    } else {
                        echo "Permission " . $permission['name'] . " already assigned to admin role\n";
                    }
                }
            }
        }
    }
}