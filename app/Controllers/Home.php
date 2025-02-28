<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        // Check login status
        if (!session()->get('isLoggedIn')) {
            // Instead of redirect, render the login view directly to avoid potential redirect loops
            return view('auth/login', ['title' => 'Login']);
        }
        
        // Otherwise redirect to dashboard
        return redirect()->to('dashboard');
    }

    public function test_db()
    {
        // Display PHP info for database extensions
        echo "<h1>PHP Database Extension Information</h1>";
        echo "<p>PHP Version: " . phpversion() . "</p>";
        echo "<p>MySQLi Extension: " . (extension_loaded('mysqli') ? 'Loaded' : 'Not Loaded') . "</p>";
        echo "<p>PDO Extension: " . (extension_loaded('pdo') ? 'Loaded' : 'Not Loaded') . "</p>";
        echo "<p>PDO MySQL Extension: " . (extension_loaded('pdo_mysql') ? 'Loaded' : 'Not Loaded') . "</p>";
        
        // Display database configuration
        echo "<h1>Database Configuration</h1>";
        $config = new \Config\Database();
        echo "<p>Hostname: " . $config->default['hostname'] . "</p>";
        echo "<p>Username: " . $config->default['username'] . "</p>";
        echo "<p>Database: " . $config->default['database'] . "</p>";
        echo "<p>Port: " . $config->default['port'] . "</p>";
        echo "<p>Driver: " . $config->default['DBDriver'] . "</p>";
        
        // Test the connection
        try {
            $db = \Config\Database::connect();
            $query = $db->query("SHOW DATABASES");
            $databases = $query->getResult();
            
            echo "<h1>Database Connection Test</h1>";
            echo "<p style='color:green'>Successfully connected to MySQL server!</p>";
            
            // Show all databases
            echo "<h2>Available Databases:</h2>";
            echo "<ul>";
            foreach ($databases as $database) {
                $dbName = current(get_object_vars($database));
                echo "<li>" . $dbName . ($dbName == $config->default['database'] ? " <strong>(Target DB)</strong>" : "") . "</li>";
            }
            echo "</ul>";
            
            // Try to select the database
            try {
                $dbName = $config->default['database'];
                $db->query("USE `{$dbName}`");
                echo "<p style='color:green'>Successfully connected to database: <strong>{$dbName}</strong></p>";
                
                // Show tables
                $query = $db->query("SHOW TABLES");
                $tables = $query->getResult();
                
                echo "<h2>Tables in {$dbName}:</h2>";
                if (count($tables) > 0) {
                    echo "<ul>";
                    foreach ($tables as $table) {
                        $tableName = current(get_object_vars($table));
                        echo "<li>" . $tableName . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>No tables found in the database.</p>";
                }
            } catch (\Exception $e) {
                echo "<p style='color:red'>The database '{$dbName}' exists but could not be selected: " . $e->getMessage() . "</p>";
            }
            
        } catch (\Exception $e) {
            echo "<h1>Database Connection Error</h1>";
            echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
            echo "<p>Error Code: " . $e->getCode() . "</p>";
            echo "<p>Error in file: " . $e->getFile() . " (Line: " . $e->getLine() . ")</p>";
        }
    }
}