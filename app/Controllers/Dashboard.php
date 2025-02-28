<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\ActivityLogModel;

class Dashboard extends BaseController
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
        // Get IP address using the same logic as in ActivityLogModel
        $request = \Config\Services::request();
        $ipAddress = $request->getIPAddress();
        
        // If we get a private IP or localhost, try to get the real IP from proxy headers
        if ($ipAddress == '127.0.0.1' || $ipAddress == '::1' || substr($ipAddress, 0, 3) == '10.' || 
            substr($ipAddress, 0, 7) == '192.168') {
            
            // Try common proxy headers
            if ($request->getServer('HTTP_X_FORWARDED_FOR')) {
                $ipAddress = $request->getServer('HTTP_X_FORWARDED_FOR');
            } else if ($request->getServer('HTTP_CLIENT_IP')) {
                $ipAddress = $request->getServer('HTTP_CLIENT_IP');
            } else if ($request->getServer('REMOTE_ADDR')) {
                $ipAddress = $request->getServer('REMOTE_ADDR');
            }
            
            // If multiple IPs, get the first one
            if (strpos($ipAddress, ',') !== false) {
                $ipAddress = explode(',', $ipAddress)[0];
            }
        }
        
        // Get recently registered users - only select fields that exist in the table
        $recentUsers = $this->userModel
            ->select('id, username, email, created_at')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->find();

        // Get counts for user stats
        $totalUsers = $this->userModel->countAllResults();
        $activeUsers = $this->userModel->where('is_active', 1)->countAllResults();
        $inactiveUsers = $this->userModel->where('is_active', 0)->countAllResults();
        $onlineUsers = 1; // Current user is online; in a real app, you might track this differently
        
        $data = [
            'title' => 'Dashboard',
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'inactiveUsers' => $inactiveUsers,
            'onlineUsers' => $onlineUsers,
            'totalRoles' => $this->roleModel->countAllResults(),
            'recentUsers' => $recentUsers,
            'recentActivities' => $this->activityLogModel
                ->select('activity_logs.*, users.username')
                ->join('users', 'users.id = activity_logs.user_id', 'left')
                ->orderBy('activity_logs.created_at', 'DESC')
                ->limit(10)
                ->find(),
            'userIpAddress' => $ipAddress,
        ];
        
        return view('dashboard/index', $data);
    }
}