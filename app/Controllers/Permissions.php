<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PermissionModel;
use App\Models\ActivityLogModel;

class Permissions extends BaseController
{
    protected $permissionModel;
    protected $activityLogModel;
    
    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
        $this->activityLogModel = new ActivityLogModel();
    }
    
    public function index()
    {
        $data = [
            'title' => 'Permissions Management',
            'permissions' => $this->permissionModel->findAll(),
        ];
        
        return view('permissions/index', $data);
    }
    
    public function new()
    {
        $data = [
            'title' => 'Create Permission',
        ];
        
        return view('permissions/create', $data);
    }
    
    public function create()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]|is_unique[permissions.name]',
            'description' => 'permit_empty|max_length[255]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $permissionData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ];
        
        // Begin transaction
        $db = \Config\Database::connect();
        $db->transBegin();
        
        try {
            // Insert permission
            $this->permissionModel->insert($permissionData);
            $permissionId = $this->permissionModel->getInsertID();
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Failed to create permission. Please try again.');
            }
            
            $db->transCommit();
            
            // Log activity
            $this->activityLogModel->logActivity(
                user_id(), 
                'Created permission',
                'Name: ' . $permissionData['name'] . ', ID: ' . $permissionId
            );
            
            return redirect()->to('permissions')->with('success', 'Permission created successfully.');
            
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to create permission: ' . $e->getMessage());
        }
    }
    
    public function edit($id = null)
    {
        if ($id === null) {
            return redirect()->to('permissions')->with('error', 'Permission ID is required.');
        }
        
        $permission = $this->permissionModel->find($id);
        
        if (!$permission) {
            return redirect()->to('permissions')->with('error', 'Permission not found.');
        }
        
        $data = [
            'title' => 'Edit Permission',
            'permission' => $permission,
        ];
        
        return view('permissions/edit', $data);
    }
    
    public function update($id = null)
{
    if ($id === null) {
        return redirect()->to('permissions')->with('error', 'Permission ID is required.');
    }
    
    $permission = $this->permissionModel->find($id);
    
    if (!$permission) {
        return redirect()->to('permissions')->with('error', 'Permission not found.');
    }
    
    // Debug logging
    log_message('debug', 'Permission update request data: ' . json_encode($this->request->getPost()));
    
    $rules = [
        'name' => "required|min_length[3]|max_length[100]|is_unique[permissions.name,id,$id]",
        'description' => 'permit_empty|max_length[255]',
    ];
    
    if (!$this->validate($rules)) {
        log_message('debug', 'Permission validation errors: ' . json_encode($this->validator->getErrors()));
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }
    
    $permissionData = [
        'name' => $this->request->getPost('name'),
        'description' => $this->request->getPost('description'),
    ];
    
    // Debug logging
    log_message('debug', 'Permission data for update: ' . json_encode($permissionData));
    
    // Begin transaction
    $db = \Config\Database::connect();
    $db->transBegin();
    
    try {
        // Update permission
        $this->permissionModel->skipValidation(true);
        $result = $this->permissionModel->update($id, $permissionData);
        log_message('debug', 'Permission update result: ' . ($result ? 'true' : 'false'));
        
        if ($result === false) {
            // Check for errors
            $errors = $this->permissionModel->errors();
            log_message('error', 'Permission model update errors: ' . json_encode($errors));
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to update permission: ' . implode(', ', $errors));
        }
        
        if ($db->transStatus() === false) {
            $db->transRollback();
            log_message('error', 'Transaction failed in permission update');
            return redirect()->back()->withInput()->with('error', 'Failed to update permission. Please try again.');
        }
        
        $db->transCommit();
        
        // Log activity
        $this->activityLogModel->logActivity(
            user_id(), 
            'Updated permission',
            'Name: ' . $permissionData['name'] . ', ID: ' . $id
        );
        
        return redirect()->to('permissions')->with('success', 'Permission updated successfully.');
        
    } catch (\Exception $e) {
        log_message('error', 'Exception in permission update: ' . $e->getMessage());
        $db->transRollback();
        return redirect()->back()->withInput()->with('error', 'Failed to update permission: ' . $e->getMessage());
    }
}
    
    public function delete($id = null)
    {
        if ($id === null) {
            return redirect()->to('permissions')->with('error', 'Permission ID is required.');
        }
        
        $permission = $this->permissionModel->find($id);
        
        if (!$permission) {
            return redirect()->to('permissions')->with('error', 'Permission not found.');
        }
        
        // Check if permission is in use by any roles
        $db = \Config\Database::connect();
        $rolePermissions = $db->table('role_permissions')
            ->where('permission_id', $id)
            ->countAllResults();
        
        if ($rolePermissions > 0) {
            return redirect()->to('permissions')->with('error', 'Cannot delete permission as it is assigned to one or more roles. Remove it from all roles first.');
        }
        
        // Begin transaction
        $db->transBegin();
        
        try {
            // Delete permission
            $this->permissionModel->delete($id);
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->to('permissions')->with('error', 'Failed to delete permission. Please try again.');
            }
            
            $db->transCommit();
            
            // Store permission name for logging before it's gone
            $permissionName = $permission['name'];
            
            // Log activity with proper error handling
            try {
                $userId = session()->get('user_id');
                if (!$userId) {
                    log_message('error', 'No user_id found in session for activity logging');
                    $userId = 1; // Default to admin/system user if not found
                }
                
                $this->activityLogModel->logActivity(
                    $userId, 
                    'Deleted permission',
                    'Name: ' . $permissionName . ', ID: ' . $id
                );
            } catch (\Exception $e) {
                // Just log the error but continue with the process
                log_message('error', 'Failed to log activity for permission deletion: ' . $e->getMessage());
            }
            
            return redirect()->to('permissions')->with('success', 'Permission deleted successfully.');
            
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('permissions')->with('error', 'Failed to delete permission: ' . $e->getMessage());
        }
    }
}