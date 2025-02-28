<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - CI4 RBAC Application</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom styles -->
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        main {
            flex: 1;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            font-weight: 500;
            color: #333;
        }
        
        .sidebar .nav-link.active {
            color: #2470dc;
        }
        
        .sidebar .nav-link:hover {
            color: #007bff;
        }
        
        .navbar-brand {
            padding-top: .100rem;
            padding-bottom: .100rem;
            font-size: 2.2rem;
            background-color: rgba(0, 0, 0, .25);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
            width: 100%;
            max-width: 280px;
            min-height: 70px;
            display: flex;
            align-items: center;
        }
        
        .navbar-toggler {
            top: .25rem;
            right: 1rem;
        }
        
        /* Improved dropdown styles */
        .dropdown-menu {
            position: absolute !important;
            z-index: 1000;
            min-width: 10rem;
            transform: none !important;
        }
        
        .dropdown-toggle::after {
            margin-left: 0.5em;
            vertical-align: middle;
            transition: transform 0.2s ease;
        }
        
        /* Fix for dropdown container */
        .navbar-nav .nav-item.dropdown {
            position: static !important;
        }
        
        /* For medium screens and up, make dropdown relative */
        @media (min-width: 768px) {
            .navbar-nav .nav-item.dropdown {
                position: relative !important;
            }
        }
        
        /* Ensure dropdown toggle indicator rotates without affecting layout */
        .dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }
        
        /* Fix header layout */
        .navbar {
            position: relative;
            width: 100%;
            padding: 1.25rem 0;
            min-height: 70px;
        }
        
        /* Make navbar header take full width */
        .navbar-dark.bg-dark {
            width: 100vw;
            max-width: 100%;
        }
        
        /* Larger profile image and text */
        .nav-item.dropdown .nav-link img {
            width: 45px !important;
            height: 45px !important;
            object-fit: cover;
        }
        
        .nav-item.dropdown .nav-link span {
            font-size: 1.2rem;
            margin-left: 10px;
        }
        
        .user-dropdown img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                position: static;
                padding-top: 0;
                box-shadow: none;
            }
            
            .sidebar-sticky {
                height: auto;
            }
            
            .navbar-brand {
                padding-left: 1rem;
                background-color: transparent;
                box-shadow: none;
            }
            /* Improved dropdown styles */
            .dropdown-menu {
                position: absolute;
                z-index: 1000;
                min-width: 10rem;
            }
            
            .dropdown-toggle::after {
                margin-left: 0.5em;
                vertical-align: middle;
            }
}
        }
    </style>
    
</head>
<body>
<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-4 py-3 fs-6" href="<?= base_url('dashboard') ?>">CI4 RBAC App</a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="navbar-nav ms-auto pe-4">
        <div class="nav-item text-nowrap dropdown">
            <?php 
            $profileImage = !empty(session()->get('profile_image')) 
                ? base_url(session()->get('profile_image')) 
                : base_url('assets/img/profiles/default.png');
            ?>
            <a class="nav-link px-3 dropdown-toggle text-white d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?= $profileImage ?>" alt="Profile" class="rounded-circle me-2" style="width: 45px; height: 45px; object-fit: cover;" onerror="this.src='<?= base_url('assets/img/profiles/default.png') ?>'">
                <span class="d-none d-md-inline" style="font-size: 1.2rem;"><?= session()->get('username') ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end mt-2 shadow">
                <li><a class="dropdown-item" href="<?= base_url('profile') ?>"><i class="fas fa-user me-2"></i>My Profile</a></li>
                <li><a class="dropdown-item" href="<?= base_url('profile/change-password') ?>"><i class="fas fa-key me-2"></i>Change Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= base_url('auth/logout') ?>"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</header>

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3 sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= uri_string() == 'dashboard' ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        
                        <?php if (has_role(['admin', 'manager'])): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(uri_string(), 'users') === 0 ? 'active' : '' ?>" href="<?= base_url('users') ?>">
                                <i class="fas fa-users me-2"></i> Users
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (has_role('admin')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(uri_string(), 'roles') === 0 ? 'active' : '' ?>" href="<?= base_url('roles') ?>">
                                <i class="fas fa-user-tag me-2"></i> Roles
                            </a>
                        </li>
                        <?php /* Temporarily display for all admins until permissions are set up */ ?>
                       
                        
                        <li class="nav-item">
                            <a class="nav-link <?= uri_string() == 'permissions' || str_starts_with(uri_string(), 'permissions/') ? 'active' : '' ?>" href="<?= base_url('permissions') ?>">
                                <i class="fas fa-key"></i>
                                Permissions
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(uri_string(), 'profile') === 0 ? 'active' : '' ?>" href="<?= base_url('profile') ?>">
                                <i class="fas fa-user-circle me-2"></i> My Profile
                            </a>
                        </li>
                    </ul>
                    
                    <?php if (has_role('admin')): ?>
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                        <span>Admin Tools</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                            <a class="nav-link <?= uri_string() == 'activity-logs' ? 'active' : '' ?>" href="<?= base_url('activity-logs') ?>">
                                <i class="fas fa-history me-2"></i>
                                Activity Logs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos(uri_string(), 'settings') === 0 ? 'active' : '' ?>" href="<?= base_url('settings') ?>">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                        </li>
                    </ul>
                    <?php endif; ?>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <?php if (session()->getFlashdata('error')) : ?>
                    <div class="alert alert-danger">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>
                
                <?php if (session()->getFlashdata('success')) : ?>
                    <div class="alert alert-success">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>
                
                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <span class="text-muted">&copy; <?= date('Y') ?> CI4 RBAC Application</span>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>