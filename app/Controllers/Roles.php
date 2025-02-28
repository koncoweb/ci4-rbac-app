<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Models\ActivityLogModel;

class Roles extends BaseController
{
    protected $roleModel;
    protected $permissionModel;
    protected $activityLogModel;
    
    public function __construct()
    {
        $this->roleModel = new RoleModel();
        $this->permissionModel = new PermissionModel();
        $this->activityLogModel = new ActivityLogModel();
    }
    
    public function index()
    {
        $data = [
            'title' => 'Role Management',
            'roles' => $this->roleModel->findAll(),
        ];
        
        return view('roles/index', $data);
    }
    
    public function new()
    {
        $data = [
            'title' => 'Create New Role',
            'permissions' => $this->permissionModel->findAll(),
        ];
        
        return view('roles/create', $data);
    }
    
    public function create()
    {
        $rules = [
            'name' => 'required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[roles.name]',
            'description' => 'permit_empty|max_length[255]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $roleData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ];
        
        // Begin transaction
        $db = \Config\Database::connect();
        $db->transBegin();
        
        try {
            // Insert role
            $roleId = $this->roleModel->insert($roleData);
            
            // Assign permissions
            $permissions = $this->request->getPost('permissions');
            if (is_array($permissions)) {
                foreach ($permissions as $permissionId) {
                    $db->table('role_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ]);
                }
            }
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Failed to create role. Please try again.');
            }
            
            $db->transCommit();
            
            // Log activity
            $this->activityLogModel->logActivity(
                user_id(),
                'Created role',
                'Role: ' . $this->request->getPost('name') . ', ID: ' . $roleId
            );
            
            return redirect()->to('roles')->with('success', 'Role created successfully.');
            
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }
    
    public function edit($id = null)
    {
        if ($id === null) {
            return redirect()->to('roles')->with('error', 'Role ID is required.');
        }
        
        $role = $this->roleModel->getRoleWithPermissions($id);
        
        if (!$role) {
            return redirect()->to('roles')->with('error', 'Role not found.');
        }
        
        $data = [
            'title' => 'Edit Role',
            'role' => $role,
            'permissions' => $this->permissionModel->findAll(),
            'rolePermissions' => array_column($role['permissions'] ?? [], 'id'),
        ];
        
        return view('roles/edit', $data);
    }
    
    public function update($id = null)
    {
        if ($id === null) {
            return redirect()->to('roles')->with('error', 'Role ID is required.');
        }
        
        $role = $this->roleModel->find($id);
        
        if (!$role) {
            return redirect()->to('roles')->with('error', 'Role not found.');
        }
        
        // Debug logging
        log_message('debug', 'Role update request data: ' . json_encode($this->request->getPost()));
        
        $rules = [
            'name' => "required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[roles.name,id,$id]",
            'description' => 'permit_empty|max_length[255]',
        ];
        
        if (!$this->validate($rules)) {
            log_message('debug', 'Role validation errors: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $roleData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ];
        
        // Debug logging
        log_message('debug', 'Role data for update: ' . json_encode($roleData));
        
        // Begin transaction
        $db = \Config\Database::connect();
        $db->transBegin();
        
        try {
            // Update role - explicitlyskip validation since we've already validated
            $this->roleModel->skipValidation(true);
            $result = $this->roleModel->update($id, $roleData);
            log_message('debug', 'Role update result: ' . ($result ? 'true' : 'false'));
            
            if ($result === false) {
                // Check for errors
                $errors = $this->roleModel->errors();
                log_message('error', 'Role model update errors: ' . json_encode($errors));
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Failed to update role: ' . implode(', ', $errors));
            }
            
            // Remove existing permissions
            $deleteResult = $db->table('role_permissions')->where('role_id', $id)->delete();
            log_message('debug', 'Delete permissions result: ' . ($deleteResult ? 'true' : 'false'));
            
            // Assign new permissions
            $permissions = $this->request->getPost('permissions');
            log_message('debug', 'Permissions received: ' . json_encode($permissions));
            
            if (is_array($permissions)) {
                foreach ($permissions as $permissionId) {
                    $insertResult = $db->table('role_permissions')->insert([
                        'role_id' => $id,
                        'permission_id' => $permissionId,
                    ]);
                    log_message('debug', "Insert permission $permissionId result: " . ($insertResult ? 'true' : 'false'));
                }
            } else {
                log_message('debug', 'No permissions submitted or permissions not in array format');
            }
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                log_message('error', 'Transaction failed in role update');
                return redirect()->back()->withInput()->with('error', 'Failed to update role. Please try again.');
            }
            
            $db->transCommit();
            
            // Log activity
            $this->activityLogModel->logActivity(
                user_id(),
                'Updated role',
                'Role: ' . $this->request->getPost('name') . ', ID: ' . $id
            );
            
            return redirect()->to('roles')->with('success', 'Role updated successfully.');
            
        } catch (\Exception $e) {
            log_message('error', 'Exception in role update: ' . $e->getMessage());
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to update role: ' . $e->getMessage());
        }
    }


    public function delete($id = null)
    {
        if ($id === null) {
            return redirect()->to('roles')->with('error', 'Role ID is required.');
        }
        
        // Check if any users have this role
        $db = \Config\Database::connect();
        $usersWithRole = $db->table('user_roles')
            ->where('role_id', $id)
            ->countAllResults();
            
        if ($usersWithRole > 0) {
            return redirect()->to('roles')->with('error', 'Cannot delete role that is assigned to users.');
        }
        
        $role = $this->roleModel->find($id);
        
        if (!$role) {
            return redirect()->to('roles')->with('error', 'Role not found.');
        }
        
        // Begin transaction
        $db = \Config\Database::connect();
        $db->transBegin();
        
        try {
            // Delete role permissions first (maintain referential integrity)
            $db->table('role_permissions')->where('role_id', $id)->delete();
            
            // Delete role
            $this->roleModel->delete($id);
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->to('roles')->with('error', 'Failed to delete role. Please try again.');
            }
            
            $db->transCommit();
            
            // Log activity
            $this->activityLogModel->logActivity(
                user_id(),
                'Deleted role',
                'Role: ' . $role['name'] . ', ID: ' . $id
            );
            
            return redirect()->to('roles')->with('success', 'Role deleted successfully.');
            
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('roles')->with('error', 'Failed to delete role: ' . $e->getMessage());
        }
    }
}