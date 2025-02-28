<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    
    protected $allowedFields    = [
        'username', 'email', 'password', 'profile_image', 'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'username' => 'required|alpha_numeric|min_length[3]|max_length[30]|is_unique[users.username,id,{id}]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'permit_empty|min_length[8]',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $beforeInsert   = ['hashPassword'];
    protected $beforeUpdate   = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (! isset($data['data']['password'])) {
            return $data;
        }

        $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);

        return $data;
    }
    
    /**
     * Get user with roles
     */
    public function getUserWithRoles($id)
    {
        $user = $this->find($id);
        
        if (!$user) {
            return null;
        }
        
        $db = \Config\Database::connect();
        
        $roles = $db->table('user_roles')
            ->select('roles.id, roles.name, roles.description')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('user_roles.user_id', $id)
            ->get()
            ->getResultArray();
            
        $user['roles'] = $roles;
        
        return $user;
    }
    
    /**
     * Assign role to user
     */
    public function assignRole($userId, $roleId)
    {
        $db = \Config\Database::connect();
        
        // Check if the role assignment already exists
        $existing = $db->table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->get()
            ->getRow();
            
        if (!$existing) {
            $db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleId
            ]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Remove role from user
     */
    public function removeRole($userId, $roleId)
    {
        $db = \Config\Database::connect();
        
        $db->table('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->delete();
            
        return true;
    }
}