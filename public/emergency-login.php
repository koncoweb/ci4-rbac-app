<?php
// Start direct PHP session
session_start();

// Set CodeIgniter session variables (use the ones your app expects)
$_SESSION['isLoggedIn'] = true;
$_SESSION['user_id'] = 1; // Usually admin ID
$_SESSION['username'] = 'admin';
$_SESSION['email'] = 'admin@example.com'; // Add email
$_SESSION['profile_image'] = 'default.jpg'; // Add profile image
$_SESSION['roles'] = ['admin', 'operator']; // Added operator role

echo "<h1>Emergency Login</h1>";
echo "<p>Session variables have been set:</p>";
echo "<ul>";
echo "<li>isLoggedIn: true</li>";
echo "<li>user_id: 1</li>";
echo "<li>username: admin</li>";
echo "<li>email: admin@example.com</li>";
echo "<li>profile_image: default.jpg</li>";
echo "<li>roles: admin, operator</li>";
echo "</ul>";

echo "<p><a href='/ci4-rbac-app/public/index.php/dashboard'>Go to Dashboard</a></p>";
echo "<p><a href='/ci4-rbac-app/public/index.php/auth/login'>Go to Login</a></p>";