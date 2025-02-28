<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
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
        
        // For debugging - uncomment to log the current path and session status
        /*
        $uri = service('uri');
        $currentPath = $uri->getPath();
        log_message('debug', 'Auth Filter Path: ' . $currentPath . ' IsLoggedIn: ' . ($session->has('isLoggedIn') ? 'true' : 'false'));
        */
        
        if (!$session->has('isLoggedIn')) {
            // Get the current URI path
            $uri = service('uri');
            $currentPath = $uri->getPath();
            
            // Skip auth filter for auth routes to prevent redirect loops
            // Check with multiple patterns to be thorough
            if (
                strpos($currentPath, 'auth/') === 0 || 
                $currentPath === 'auth' || 
                $currentPath === '/' ||
                strpos($currentPath, 'auth') === 0
            ) {
                // Already on an auth page, don't redirect
                return;
            }
            
            // For non-auth routes, redirect to login with absolute URL
            return redirect()->to(site_url('auth/login'))->with('error', 'Please login first');
        }
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