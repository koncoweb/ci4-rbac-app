<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\UserModel;

class ActivityLog extends BaseController
{
    protected $activityLogModel;
    protected $userModel;
    
    public function __construct()
    {
        $this->activityLogModel = new ActivityLogModel();
        $this->userModel = new UserModel();
    }
    
    public function index()
    {
        // Temporarily comment out permission check until permissions are properly set up
        /*
        if (!hasPermission('view-logs')) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to view logs');
        }
        */
        
        $pager = \Config\Services::pager();
        
        $page = $this->request->getVar('page') ?? 1;
        $perPage = 15;
        
        // Get total records count
        $total = $this->activityLogModel->countAllResults();
        
        // Calculate offset
        $offset = ($page - 1) * $perPage;
        
        // Get logs with pagination
        $logs = $this->activityLogModel->getLogsWithUsers($perPage, $offset);
        
        // If there are no logs, let's create a sample log
        if (empty($logs) && $page == 1) {
            // Create a sample log entry for testing
            $this->activityLogModel->logActivity(
                session()->get('id') ?? 1, 
                'Viewed activity logs'
            );
            
            // Fetch the logs again
            $logs = $this->activityLogModel->getLogsWithUsers($perPage, $offset);
            $total = $this->activityLogModel->countAllResults();
        }
        
        $data = [
            'title' => 'Activity Logs',
            'logs' => $logs,
            'pager' => $pager,
            'perPage' => $perPage,
            'total' => $total,
            'currentPage' => $page
        ];
        
        return view('activity_logs/index', $data);
    }
    
    public function clear()
    {
        // Only proceed with POST requests
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/activity-logs');
        }
        
        try {
            // Use direct database command to truncate the table
            $db = \Config\Database::connect();
            $result = $db->query('TRUNCATE TABLE activity_logs');
            
            // Get user ID from session with fallback
            $userId = session()->get('user_id');
            if (!$userId) {
                log_message('error', 'No user_id found in session for activity logging');
                $userId = 1; // Default to admin/system user if not found
            }
            
            // Log this action - create a new record after truncate
            try {
                $this->activityLogModel->logActivity(
                    $userId,
                    'Cleared all activity logs',
                    'All activity logs have been cleared'
                );
            } catch (\Exception $e) {
                log_message('error', 'Failed to log clear activity: ' . $e->getMessage());
            }
            
            return redirect()->to('/activity-logs')->with('success', 'All logs have been cleared successfully');
        } catch (\Exception $e) {
            log_message('error', 'Exception in clear logs: ' . $e->getMessage());
            return redirect()->to('/activity-logs')->with('error', 'Failed to clear logs: ' . $e->getMessage());
        }
    }
}