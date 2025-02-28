<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\ActivityLogModel;

class Profile extends BaseController
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
        // Only logged in users can access profile
        if (!is_logged_in()) {
            return redirect()->to('auth/login')->with('error', 'Please login to access your profile.');
        }
        
        // Get current user data
        $userId = user_id();
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return redirect()->to('dashboard')->with('error', 'User profile not found.');
        }
        
        // Get user roles
        $db = \Config\Database::connect();
        $roleIds = $db->table('user_roles')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();
        
        $roles = [];
        if (!empty($roleIds)) {
            $roleIds = array_column($roleIds, 'role_id');
            $roles = $this->roleModel->whereIn('id', $roleIds)->findAll();
        }
        
        // Get recent activities
        $activities = $this->activityLogModel->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->findAll();
        
        // Map activity log fields for the view
        foreach ($activities as &$activity) {
            // Make sure all required fields exist to avoid undefined index errors
            $activity['activity'] = $activity['action'] ?? 'Unknown action';
            $activity['table_name'] = $activity['entity'] ?? 'Unknown entity';
            $activity['row_id'] = $activity['entity_id'] ?? null;
        }
        
        $data = [
            'title' => 'My Profile',
            'user' => $user,
            'roles' => $roles,
            'activities' => $activities,
        ];
        
        return view('profile/index', $data);
    }
    
    public function edit()
    {
        // Only logged in users can access profile
        if (!is_logged_in()) {
            return redirect()->to('auth/login')->with('error', 'Please login to access your profile.');
        }
        
        // Get current user data
        $userId = user_id();
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return redirect()->to('dashboard')->with('error', 'User profile not found.');
        }
        
        $data = [
            'title' => 'Edit Profile',
            'user' => $user,
        ];
        
        return view('profile/edit', $data);
    }
    
    public function update()
    {
        // Only logged in users can access profile
        if (!is_logged_in()) {
            return redirect()->to('auth/login')->with('error', 'Please login to update your profile.');
        }
        
        $userId = user_id();
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return redirect()->to('dashboard')->with('error', 'User profile not found.');
        }
        
        // Debug logging
        log_message('debug', 'Profile update request data: ' . json_encode($this->request->getPost()));
        
        $rules = [
            'username' => "required|alpha_numeric|min_length[3]|max_length[30]|is_unique[users.username,id,$userId]",
            'email' => "required|valid_email|is_unique[users.email,id,$userId]",
        ];
        
        if (!$this->validate($rules)) {
            log_message('debug', 'Profile validation errors: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
        ];
        
        // Handle profile image upload
        $profileImage = $this->request->getFile('profile_image');
        if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
            // Define upload path
            $uploadPath = 'assets/img/profiles';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            
            // Generate unique filename
            $newName = $userId . '_' . time() . '.' . $profileImage->getExtension();
            
            // Move the uploaded file
            if ($profileImage->move($uploadPath, $newName)) {
                // Add image path to user data
                $userData['profile_image'] = $uploadPath . '/' . $newName;
                
                // Delete old profile image if exists
                if (!empty($user['profile_image']) && file_exists($user['profile_image']) && $user['profile_image'] != 'assets/img/profiles/default.png') {
                    unlink($user['profile_image']);
                }
                
                log_message('debug', 'Profile image uploaded: ' . $userData['profile_image']);
            } else {
                log_message('error', 'Failed to upload profile image: ' . $profileImage->getErrorString());
            }
        }
        
        // Debug logging
        log_message('debug', 'Profile data for update: ' . json_encode($userData));
        
        // Begin transaction
        $db = \Config\Database::connect();
        $db->transBegin();
        
        try {
            // Update user
            $this->userModel->skipValidation(true);
            $result = $this->userModel->update($userId, $userData);
            log_message('debug', 'Profile update result: ' . ($result ? 'true' : 'false'));
            
            if ($result === false) {
                // Check for errors
                $errors = $this->userModel->errors();
                log_message('error', 'User model update errors: ' . json_encode($errors));
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Failed to update profile: ' . implode(', ', $errors));
            }
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                log_message('error', 'Transaction failed in profile update');
                return redirect()->back()->withInput()->with('error', 'Failed to update profile. Please try again.');
            }
            
            $db->transCommit();
            
            // Log activity
            $this->activityLogModel->logActivity(
                $userId, 
                'Updated profile',
                'User ID: ' . $userId
            );
            
            return redirect()->to('profile')->with('success', 'Profile updated successfully.');
            
        } catch (\Exception $e) {
            log_message('error', 'Exception in profile update: ' . $e->getMessage());
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }
    
    public function changePassword()
    {
        // Only logged in users can access profile
        if (!is_logged_in()) {
            return redirect()->to('auth/login')->with('error', 'Please login to change your password.');
        }
        
        $data = [
            'title' => 'Change Password',
        ];
        
        return view('profile/change_password', $data);
    }
    
    public function updatePassword()
    {
        // Only logged in users can access profile
        if (!is_logged_in()) {
            return redirect()->to('auth/login')->with('error', 'Please login to update your password.');
        }
        
        $userId = user_id();
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return redirect()->to('dashboard')->with('error', 'User profile not found.');
        }
        
        $rules = [
            'current_password' => 'required',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        // Verify current password
        $currentPassword = $this->request->getPost('current_password');
        if (!password_verify($currentPassword, $user['password'])) {
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }
        
        $userData = [
            'password' => $this->request->getPost('password'),
        ];
        
        // Begin transaction
        $db = \Config\Database::connect();
        $db->transBegin();
        
        try {
            // Update password
            $this->userModel->skipValidation(true);
            $result = $this->userModel->update($userId, $userData);
            
            if ($result === false) {
                $errors = $this->userModel->errors();
                $db->transRollback();
                return redirect()->back()->with('error', 'Failed to update password: ' . implode(', ', $errors));
            }
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->with('error', 'Failed to update password. Please try again.');
            }
            
            $db->transCommit();
            
            // Log activity
            $this->activityLogModel->logActivity(
                $userId, 
                'Changed password',
                'User ID: ' . $userId
            );
            
            return redirect()->to('profile')->with('success', 'Password changed successfully.');
            
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Failed to update password: ' . $e->getMessage());
        }
    }
}