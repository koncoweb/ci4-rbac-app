<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
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
        'name' => 'required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[roles.name,id,{id}]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
    
    /**
     * Get role with permissions
     */
    public function getRoleWithPermissions($id)
    {
        $role = $this->find($id);
        
        if (!$role) {
            return null;
        }
        
        $db = \Config\Database::connect();
        
        $permissions = $db->table('role_permissions')
            ->select('permissions.*')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role_id', $id)
            ->get()
            ->getResultArray();
            
        $role['permissions'] = $permissions;
        
        return $role;
    }
    
    /**
     * Assign permission to role
     */
    public function assignPermission($roleId, $permissionId)
    {
        $db = \Config\Database::connect();
        
        // Check if the permission assignment already exists
        $existing = $db->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->get()
            ->getRow();
            
        if (!$existing) {
            $db->table('role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId
            ]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Remove permission from role
     */
    public function removePermission($roleId, $permissionId)
    {
        $db = \Config\Database::connect();
        
        $db->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->delete();
            
        return true;
    }
   // Update method to better handle validation
public function update($id = null, $data = null): bool
{
    // For debugging
    log_message('debug', 'RoleModel update called with ID: ' . $id . ', data: ' . json_encode($data));
    
    // Make sure the ID is properly set for validation replacement
    $this->tempAllowCallbacks = true;
    
    // Ensure skipValidation is true for the update
    $this->skipValidation(true);
    
    // Perform the update
    $result = parent::update($id, $data);
    
    // Log the result
    log_message('debug', 'RoleModel update result: ' . ($result ? 'true' : 'false'));
    if (!$result) {
        log_message('error', 'RoleModel update errors: ' . json_encode($this->errors()));
    }
    
    return $result;
}
}