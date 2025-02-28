<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ActivityLogModel;

class Auth extends BaseController
{
    protected $userModel;
    protected $activityLogModel;
    protected $session;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->activityLogModel = new ActivityLogModel();
        $this->session = session();
    }
    
    public function index()
    {
        return redirect()->to('auth/login');
    }
    
    public function login()
    {
        if ($this->session->has('user_id')) {
            return redirect()->to('dashboard');
        }
        
        return view('auth/login', ['title' => 'Login']);
    }
    
    public function attemptLogin()
    {
        $rules = [
            'login_id' => 'required',
            'password' => 'required',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $login_id = $this->request->getPost('login_id');
        $password = $this->request->getPost('password');
        
        // Check if login_id is email or username
        $user = $this->userModel->where('email', $login_id)
                                ->orWhere('username', $login_id)
                                ->first();
        
        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'User not found');
        }
        
        if (!password_verify($password, $user['password'])) {
            // For debugging
            log_message('debug', 'Password verification failed');
            log_message('debug', 'Input password: ' . $password);
            log_message('debug', 'Stored hash: ' . $user['password']);
            
            // Log failed login attempt
            try {
                $this->activityLogModel->logActivity(
                    null, 
                    'Failed login attempt',
                    'Username: ' . $login_id
                );
            } catch (\Exception $e) {
                log_message('error', 'Error logging failed login: ' . $e->getMessage());
            }
            
            return redirect()->back()->withInput()->with('error', 'Invalid password');
        }
        
        if (!$user['is_active']) {
            return redirect()->back()->withInput()->with('error', 'Account is inactive. Please contact administrator');
        }
        
        // Get user roles
        $db = \Config\Database::connect();
        $roles = $db->table('user_roles')
                   ->select('roles.name')
                   ->join('roles', 'roles.id = user_roles.role_id')
                   ->where('user_roles.user_id', $user['id'])
                   ->get()
                   ->getResultArray();
                   
        $roleNames = array_column($roles, 'name');
        
        // Set session data
        $this->session->set([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'profile_image' => $user['profile_image'],
            'roles' => $roleNames,
            'isLoggedIn' => true,
        ]);
        
        // Log successful login
        try {
            $this->activityLogModel->logActivity(
                $user['id'], 
                'Successful login',
                'Username: ' . $user['username'] . ', IP: ' . $this->request->getIPAddress()
            );
        } catch (\Exception $e) {
            log_message('error', 'Error logging successful login: ' . $e->getMessage());
            // Continue with login process even if logging fails
        }
        
        return redirect()->to('dashboard');
    }
    
    public function register()
    {
        if ($this->session->has('user_id')) {
            return redirect()->to('dashboard');
        }
        
        return view('auth/register', ['title' => 'Register']);
    }
    
    public function attemptRegister()
    {
        $rules = [
            'username' => 'required|alpha_numeric|min_length[3]|max_length[30]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
        ];
        
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        
        $userData = [
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'is_active' => 1,
        ];
        
        // Begin transaction
        $db = \Config\Database::connect();
        $db->transBegin();
        
        try {
            // Insert user
            $userId = $this->userModel->insert($userData);
            
            // Assign default 'user' role (ID: 3)
            $db->table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => 3,  // User role ID
            ]);
            
            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Registration failed. Please try again.');
            }
            
            $db->transCommit();
            
            // Log activity
            $this->activityLogModel->logActivity(
                $userId, 
                'User registration',
                'Username: ' . $userData['username'] . ', Email: ' . $userData['email']
            );
            
            return redirect()->to('auth/login')->with('success', 'Registration successful. Please login.');
            
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }
    
    public function logout()
    {
        $userId = $this->session->get('user_id');
        
        // Log logout
        if ($userId) {
            $this->activityLogModel->logActivity(
                $userId, 
                'User logout',
                'Username: ' . session()->get('username')
            );
        }
        
        $this->session->destroy();
        return redirect()->to('auth/login')->with('success', 'You have been logged out successfully');
    }
    
    public function forgotPassword()
    {
        return view('auth/forgot_password', ['title' => 'Forgot Password']);
    }
    
    public function resetPassword()
    {
        // This would typically involve sending an email with a reset link
        // For simplicity, we'll just show a message
        $email = $this->request->getPost('email');
        
        $user = $this->userModel->where('email', $email)->first();
        
        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Email not found');
        }
        
        // In a real application, you would generate a token, store it, and send an email
        // For now, we'll just show a success message
        
        return redirect()->to('auth/login')->with('success', 'If your email is registered, you will receive password reset instructions');
    }
}