<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDetailsToActivityLogs extends Migration
{
    public function up()
    {
        // Check if details column exists
        $fields = $this->db->getFieldData('activity_logs');
        $detailsExists = false;
        $ipAddressExists = false;
        
        foreach ($fields as $field) {
            if ($field->name === 'details') {
                $detailsExists = true;
            }
            if ($field->name === 'ip_address') {
                $ipAddressExists = true;
            }
        }
        
        // Add details column if it doesn't exist
        if (!$detailsExists) {
            $this->forge->addColumn('activity_logs', [
                'details' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'action'
                ]
            ]);
        }
        
        // Add ip_address column if it doesn't exist
        if (!$ipAddressExists) {
            $this->forge->addColumn('activity_logs', [
                'ip_address' => [
                    'type' => 'VARCHAR',
                    'constraint' => 45,
                    'null' => true,
                    'after' => 'details'
                ]
            ]);
        }
    }

    public function down()
    {
        // Remove columns if they exist
        $fields = $this->db->getFieldData('activity_logs');
        $detailsExists = false;
        $ipAddressExists = false;
        
        foreach ($fields as $field) {
            if ($field->name === 'details') {
                $detailsExists = true;
            }
            if ($field->name === 'ip_address') {
                $ipAddressExists = true;
            }
        }
        
        if ($detailsExists) {
            $this->forge->dropColumn('activity_logs', 'details');
        }
        
        if ($ipAddressExists) {
            $this->forge->dropColumn('activity_logs', 'ip_address');
        }
    }
}
