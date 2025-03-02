<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard</h1>
    </div>
    
    <!-- User Statistics Cards -->
    <div class="row mb-4">
        <!-- Online Users Card -->
        <div class="col-md-3 mb-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="display-4 me-3"><?= $onlineUsers ?></div>
                    <div>
                        <h5 class="card-title mb-0">Online Users</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Users Card -->
        <div class="col-md-3 mb-4">
            <div class="card bg-info text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="display-4 me-3"><?= $totalUsers ?></div>
                    <div>
                        <h5 class="card-title mb-0">Total Users</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Active Users Card -->
        <div class="col-md-3 mb-4">
            <div class="card bg-warning text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="display-4 me-3"><?= $activeUsers ?></div>
                    <div>
                        <h5 class="card-title mb-0">Active Users</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Inactive Users Card -->
        <div class="col-md-3 mb-4">
            <div class="card bg-danger text-white h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="display-4 me-3"><?= $inactiveUsers ?></div>
                    <div>
                        <h5 class="card-title mb-0">Inactive Users</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <!-- Recently Registered Section -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-plus me-2"></i>Recently Registered
                        <i class="fas fa-info-circle text-muted ms-2" data-bs-toggle="tooltip" title="Users who recently joined"></i>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Date Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentUsers)): ?>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?= esc($user['username']) ?></td>
                                            <td><?= esc($user['email']) ?></td>
                                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No recent users</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Online Users Section -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>Online Users
                        <i class="fas fa-info-circle text-muted ms-2" data-bs-toggle="tooltip" title="Currently online users"></i>
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Currently, No User login other than you</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activities Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Recent Activities
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Activity</th>
                            <th>Details</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentActivities)): ?>
                            <?php foreach ($recentActivities as $activity): ?>
                                <tr>
                                    <td><?= esc($activity['username'] ?? 'System') ?></td>
                                    <td><?= esc($activity['activity_type'] ?? $activity['action'] ?? 'Unknown') ?></td>
                                    <td><?= esc($activity['details']) ?></td>
                                    <td><?= date('M d, Y H:i', strtotime($activity['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No recent activities</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>