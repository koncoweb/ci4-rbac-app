<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdditionalUsersSeeder extends Seeder
{
    public function run()
    {
        // Create users
        $userModel = new \App\Models\UserModel();
        $db = \Config\Database::connect();
        
        try {
            // 1. Create manager user - using EXACT same approach as AdminUserSeeder
            $managerUser = [
                'username' => 'manager',
                'email' => 'manager@example.com',
                'password' => password_hash('manager123', PASSWORD_DEFAULT),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            // Direct insert like AdminUserSeeder does
            $managerId = $userModel->insert($managerUser);
            
            // Direct role assignment like AdminUserSeeder does
            $db->table('user_roles')->insert([
                'user_id' => $managerId,
                'role_id' => 2,  // Manager role ID
            ]);
            
            echo "Manager user created with ID: {$managerId}\n";
            
            // 2. Create regular user - using EXACT same approach as AdminUserSeeder
            $regularUser = [
                'username' => 'user',
                'email' => 'user@example.com',
                'password' => password_hash('user123', PASSWORD_DEFAULT),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            // Direct insert like AdminUserSeeder does
            $userId = $userModel->insert($regularUser);
            
            // Direct role assignment like AdminUserSeeder does
            $db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => 3,  // User role ID
            ]);
            
            echo "Regular user created with ID: {$userId}\n";
            
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}