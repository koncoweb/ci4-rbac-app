<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        $userModel = new \App\Models\UserModel();
        
        $adminUser = [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        
        $userId = $userModel->insert($adminUser);
        
        // Assign admin role to the admin user
        $db = \Config\Database::connect();
        $db->table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => 1,  // Admin role ID
        ]);
    }
}