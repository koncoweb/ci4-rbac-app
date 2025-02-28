<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    
    protected $allowedFields    = ['name', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'name' => 'required|min_length[3]|max_length[100]|is_unique[permissions.name,id,{id}]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
    
    // Update method to better handle validation
    public function update($id = null, $data = null): bool
    {
        // For debugging
        log_message('debug', 'PermissionModel update called with ID: ' . $id . ', data: ' . json_encode($data));
        
        // Make sure the ID is properly set for validation replacement
        $this->tempAllowCallbacks = true;
        
        // Ensure skipValidation is true for the update
        $this->skipValidation(true);
        
        // Perform the update
        $result = parent::update($id, $data);
        
        // Log the result
        log_message('debug', 'PermissionModel update result: ' . ($result ? 'true' : 'false'));
        if (!$result) {
            log_message('error', 'PermissionModel update errors: ' . json_encode($this->errors()));
        }
        
        return $result;
    }
}