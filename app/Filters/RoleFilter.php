<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // If not logged in, redirect to login
        if (!$session->has('isLoggedIn')) {
            return redirect()->to('auth/login')->with('error', 'Please login first');
        }
        
        // Get user roles from session
        $userRoles = $session->get('roles');
        
        // If no roles specified in filter or no user roles, deny access
        if (empty($arguments) || empty($userRoles)) {
            return redirect()->to('dashboard')->with('error', 'You do not have permission to access this page');
        }
        
        // Check if user has any of the required roles
        foreach ($arguments as $role) {
            if (in_array($role, $userRoles)) {
                return; // Allow access if user has any of the required roles
            }
        }
        
        // If user doesn't have any of the required roles, deny access
        return redirect()->to('dashboard')->with('error', 'You do not have permission to access this page');
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}