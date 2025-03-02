<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ResetAdminPasswordSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // Update admin password to a known value
        $newPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $db->table('users')
            ->where('email', 'admin@example.com')
            ->orWhere('username', 'admin')
            ->update(['password' => $newPassword]);
            
        echo "Admin password has been reset to 'admin123'\n";
    }
}