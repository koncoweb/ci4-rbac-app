<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PasswordTestSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Get admin user
        $adminUser = $db->table('users')
            ->where('username', 'admin')
            ->get()
            ->getRowArray();
            
        echo "Admin password hash: " . $adminUser['password'] . "\n";
        echo "Verification with 'admin123': " . (password_verify('admin123', $adminUser['password']) ? 'SUCCESS' : 'FAILED') . "\n\n";
        
        // Create a test password with the same method
        $testPassword = 'manager123';
        $testHash = password_hash($testPassword, PASSWORD_DEFAULT);
        echo "Test password hash: " . $testHash . "\n";
        echo "Verification with 'manager123': " . (password_verify($testPassword, $testHash) ? 'SUCCESS' : 'FAILED') . "\n\n";
        
        // Update manager password directly in the database
        $db->table('users')
            ->where('username', 'manager')
            ->update(['password' => $testHash]);
            
        echo "Updated manager password to match test hash\n";
        
        // Update user password directly in the database
        $userTestHash = password_hash('user123', PASSWORD_DEFAULT);
        $db->table('users')
            ->where('username', 'user')
            ->update(['password' => $userTestHash]);
            
        echo "Updated user password to match test hash\n";
    }
}