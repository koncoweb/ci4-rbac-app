<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table      = 'activity_logs';
    protected $primaryKey = 'id';
    
    protected $useAutoIncrement = true;
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    
    protected $allowedFields = [
        'user_id', 
        'action', 
        'details', 
        'ip_address', 
        'user_agent',
        'created_at'
    ];
    
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = '';
    
    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
    
    /**
     * Log an activity
     *
     * @param int $userId User ID
     * @param string $action Action performed
     * @param string $details Additional details about the action
     * @return bool
     */
    public function logActivity($userId, $action, $details = '')
    {
        $request = \Config\Services::request();
        
        // Check if the details and ip_address columns exist
        $db = \Config\Database::connect();
        $tableFields = $db->getFieldData('activity_logs');
        $columns = array_column($tableFields, 'name');
        
        // Base data that should always work
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Add optional fields if they exist
        if (in_array('details', $columns)) {
            $data['details'] = $details;
        }
        
        if (in_array('ip_address', $columns)) {
            // Fix IP address detection
            // Try different methods to get the correct IP
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
            
            $data['ip_address'] = $ipAddress;
        }
        
        if (in_array('user_agent', $columns)) {
            $data['user_agent'] = $request->getUserAgent()->getAgentString();
        }
        
        return $this->insert($data);
    }
    
    /**
     * Get logs with user information
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLogsWithUsers($limit = 20, $offset = 0)
    {
        // Create a fresh builder query to avoid any previous joins
        $builder = $this->db->table('activity_logs');
        
        try {
            // Check if the details and ip_address columns exist
            $db = \Config\Database::connect();
            $tableFields = $db->getFieldData('activity_logs');
            $columns = array_column($tableFields, 'name');
            
            // Base fields that should always exist
            $selectFields = 'activity_logs.id, activity_logs.user_id, activity_logs.action, activity_logs.created_at, u.username, u.email';
            
            // Add optional fields if they exist
            if (in_array('details', $columns)) {
                $selectFields .= ', activity_logs.details';
            }
            
            if (in_array('ip_address', $columns)) {
                $selectFields .= ', activity_logs.ip_address';
            }
            
            if (in_array('user_agent', $columns)) {
                $selectFields .= ', activity_logs.user_agent';
            }
            
            // Select all needed fields with explicit table aliases
            $results = $builder->select($selectFields)
                ->join('users as u', 'u.id = activity_logs.user_id', 'left')
                ->orderBy('activity_logs.created_at', 'DESC')
                ->limit($limit, $offset)
                ->get()
                ->getResultArray();
                
            return $results;
        } catch (\Exception $e) {
            // If the query fails, try a simpler version
            $results = $builder->select('activity_logs.id, activity_logs.user_id, activity_logs.action, activity_logs.created_at')
                ->limit($limit, $offset)
                ->get()
                ->getResultArray();
                
            return $results;
        }
    }
}