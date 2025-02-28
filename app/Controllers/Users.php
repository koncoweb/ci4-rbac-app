<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\ActivityLogModel;

class Users extends BaseController
{
    protected $userModel;
    protected $roleModel;
    protected $activityLogModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        $this->activityLogModel = new ActivityLogModel();
    }
    
    public function index()
    {
        // Debug session roles
        log_message('debug', 'User roles in session: ' . json_encode(session()->get('roles')));
      
        // Get all users including soft-deleted ones
    $this->userModel->withDeleted();
    $users = $this->userModel->findAll();
    
    
        // Get all users with detailed debugging
        try {
            log_message('debug', 'Attempting to fetch all users from database');
            $users = $this->userModel->findAll();
            log_message('debug', 'User count from database: ' . count($users));
            
            if (empty($users)) {
                log_message('debug', 'No users found in database');
                // Try a direct database query as a fallback
                $db = \Config\Database::connect();
                $directUsers = $db->table('users')->get()->getResultArray();
                log_message('debug', 'Direct query user count: ' . count($directUsers));
            } else {
                log_message('debug', 'First user data: ' . json_encode(array_slice($users, 0, 1)));
            }
        } catch (\Exception $e) {
            log_message('error', 'Error fetching users: ' . $e->getMessage());
            $users = [];
        }
        
        // Get all user roles in a single query
        try {
            $db = \Config\Database::connect();
            log_message('debug', 'Attempting to fetch user roles');
            $userRoles = $db->table('user_roles')
                ->select('user_roles.user_id, roles.id as role_id, roles.name as role_name')
                ->join('roles', 'roles.id = user_roles.role_id')
                ->whereIn('user_roles.user_id', array_column($users, 'id'))
                ->get()
                ->getResultArray();
                
            log_message('debug', 'User roles count from database: ' . count($userRoles));
            
            // Group roles by user_id
            $rolesByUser = [];
            foreach ($userRoles as $role) {
                $rolesByUser[$role['user_id']][] = [
                    'id' => $role['role_id'],
                    'name' => $role['role_name']
                ];
            }
            
            log_message('debug', 'Roles by user count: ' . count($rolesByUser));
            
            // Attach roles to users
            foreach ($users as &$user) {
                $user['roles'] = $rolesByUser[$user['id']] ?? [];
                log_message('debug', 'User ID: ' . $user['id'] . ' has ' . count($user['roles']) . ' roles');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error fetching user roles: ' . $e->getMessage());
        }
        
        $data = [
            'title' => 'User Management',
            'users' => $users,
        ];
        
        log_message('debug', 'Final user count being sent to view: ' . count($users));
        
        return view('users/index', $data);
    }
    public function allUsers()
    {
        $db = \Config\Database::connect();
        $allUsers = $db->table('users')
            ->get()
            ->getResultArray();
            
        echo "Total users in database: " . count($allUsers);
        echo "<pre>";
        print_r($allUsers);
        echo "</pre>";
        exit;
    }
    public function sessionDebug()
{
    $data = [
        'title' => 'Session Debug',
        'session' => session()->get(),
    ];
    
    return view('debug', $data);
}
    
    public function new()
    {
        $data = [
            'title' => 'Create New User',
            'roles' => $this->roleModel->findAll(),
        ];
        
        return view('users/create', $data);
    }
    
    public function create()
    {
        $rules = [
            'username' => 'required|alpha_numeric|min_length[3]|max_length[30]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'roles' => 'required',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];
        
        // Begin transaction
        $db = \Config\Database::connect();
        $db->transBegin();
        
        try {
            // Insert user
            $userId = $this->userModel->insert($userData);
            
            // Assign roles
            $roles = $this->request->getPost('roles');
            if (is_array($roles)) {
                foreach ($roles as $roleId) {
                    $db->table('user_roles')->insert([
                        'user_id' => $userId,
                        'role_id' => $roleId,
                    ]);
                }
            }
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Failed to create user. Please try again.');
            }
            
            $db->transCommit();
            
            // Log activity with error handling
            try {
                $this->activityLogModel->logActivity(
                    user_id(), 
                    'Created user',
                    'Username: ' . $userData['username'] . ', Email: ' . $userData['email']
                );
            } catch (\Exception $e) {
                // Just log the error but don't stop the process
                log_message('error', 'Failed to log activity: ' . $e->getMessage());
            }
            
            return redirect()->to('users')->with('success', 'User created successfully.');
            
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }
    
    public function edit($id = null)
    {
        if ($id === null) {
            return redirect()->to('users')->with('error', 'User ID is required.');
        }
        
        $user = $this->userModel->getUserWithRoles($id);
        
        if (!$user) {
            return redirect()->to('users')->with('error', 'User not found.');
        }
        
        $data = [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => $this->roleModel->findAll(),
            'userRoles' => array_column($user['roles'] ?? [], 'id'),
        ];
        
        return view('users/edit', $data);
    }
    
    public function update($id = null)
    {
        if ($id === null) {
            return redirect()->to('users')->with('error', 'User ID is required.');
        }
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('users')->with('error', 'User not found.');
        }
        
        // Debug request data
        log_message('debug', 'Update request for user #' . $id . ': ' . json_encode($this->request->getPost()));
        
        $rules = [
            'username' => "required|alpha_numeric|min_length[3]|max_length[30]|is_unique[users.username,id,$id]",
            'email' => "required|valid_email|is_unique[users.email,id,$id]",
            'roles' => 'required',
        ];
        
        // Add password validation only if password is being changed
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[8]';
            $rules['password_confirm'] = 'matches[password]';
        }
        
        if (!$this->validate($rules)) {
            log_message('debug', 'Validation errors: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];
        
        // Add password only if it's being changed and not empty
        if ($this->request->getPost('password') && !empty($this->request->getPost('password'))) {
            $userData['password'] = $this->request->getPost('password');
        }
        
        // Debug user data
        log_message('debug', 'User data for update: ' . json_encode($userData));
        
        // Begin transaction
        $db = \Config\Database::connect();
        $db->transBegin();
        
        try {
            // Update user - force skip validation since we've already validated
            $this->userModel->skipValidation(true);
            $result = $this->userModel->update($id, $userData);
            log_message('debug', 'User update result: ' . ($result ? 'true' : 'false'));
            
            if ($result === false) {
                $errors = $this->userModel->errors();
                log_message('error', 'User model update errors: ' . json_encode($errors));
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Failed to update user data: ' . implode(', ', $errors));
            }
            
            // Remove existing roles
            $deleteResult = $db->table('user_roles')->where('user_id', $id)->delete();
            log_message('debug', 'Deleted roles result: ' . $deleteResult);
            
            // Assign new roles
            $roles = $this->request->getPost('roles');
            log_message('debug', 'Roles to assign: ' . json_encode($roles));
            
            if (is_array($roles)) {
                foreach ($roles as $roleId) {
                    $insertResult = $db->table('user_roles')->insert([
                        'user_id' => $id,
                        'role_id' => $roleId,
                    ]);
                    log_message('debug', 'Role insert result for role ' . $roleId . ': ' . ($insertResult ? 'true' : 'false'));
                }
            }
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                log_message('error', 'Transaction failed, rolling back');
                return redirect()->back()->withInput()->with('error', 'Failed to update user. Please try again.');
            }
            
            $db->transCommit();
            log_message('debug', 'Transaction committed successfully');
            
            // Log activity
            $this->activityLogModel->logActivity(
                user_id(),
                'Updated user',
                'Username: ' . $userData['username'] . ', Email: ' . $userData['email'] . ', ID: ' . $id
            );
            
            return redirect()->to('users')->with('success', 'User updated successfully.');
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Exception during user update: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->withInput()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }
    
    public function delete($id = null)
    {
        if ($id === null) {
            return redirect()->to('users')->with('error', 'User ID is required.');
        }
        
        // Prevent deleting own account
        if ($id == user_id()) {
            return redirect()->to('users')->with('error', 'You cannot delete your own account.');
        }
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('users')->with('error', 'User not found.');
        }
        
        // Begin transaction
        $db = \Config\Database::connect();
        $db->transBegin();
        
        try {
            // Delete activity logs for this user first
            $db->table('activity_logs')->where('user_id', $id)->delete();
            
            // Delete user roles
            $db->table('user_roles')->where('user_id', $id)->delete();
            
            // Delete user permanently instead of soft deleting
            $this->userModel->delete($id, true); // The second parameter 'true' forces permanent deletion
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->to('users')->with('error', 'Failed to delete user. Please try again.');
            }
            
            $db->transCommit();
            
            // Log activity for the deletion (using current user ID)
            try {
                $this->activityLogModel->logActivity(
                    user_id(), 
                    'Permanently deleted user',
                    'Username: ' . $user['username'] . ', Email: ' . $user['email'] . ', ID: ' . $id
                );
            } catch (\Exception $e) {
                // Just log the error but don't stop the process
                log_message('error', 'Failed to log activity: ' . $e->getMessage());
            }
            
            return redirect()->to('users')->with('success', 'User permanently deleted from the database.');
            
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('users')->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}