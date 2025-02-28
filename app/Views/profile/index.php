<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">My Profile</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= base_url('profile/edit') ?>" class="btn btn-sm btn-primary me-2">
                <i class="fas fa-edit"></i> Edit Profile
            </a>
            <a href="<?= base_url('profile/change-password') ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-key"></i> Change Password
            </a>
        </div>
    </div>
    
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-1"></i>
                    Account Information
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <?php 
                        $profileImage = !empty($user['profile_image']) ? base_url($user['profile_image']) : base_url('assets/img/profiles/default.png');
                        ?>
                        <img src="<?= $profileImage ?>" alt="Profile Image" class="img-thumbnail rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        <h5><?= esc($user['username']) ?></h5>
                    </div>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Email:</strong> <?= esc($user['email']) ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Status:</strong> 
                            <?php if ($user['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Member Since:</strong> 
                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-tag me-1"></i>
                    Roles & Permissions
                </div>
                <div class="card-body">
                    <?php if (!empty($roles)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($roles as $role): ?>
                                <li class="list-group-item">
                                    <span class="badge bg-primary"><?= esc($role['name']) ?></span>
                                    <?php if (!empty($role['description'])): ?>
                                        <p class="small text-muted mb-0"><?= esc($role['description']) ?></p>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No roles assigned</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-history me-1"></i>
                    Recent Activities
                </div>
                <div class="card-body">
                    <?php if (!empty($activities)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Activity</th>
                                        <th>Related To</th>
                                        <th>Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>
    <?php foreach ($activities as $activity): ?>
        <tr>
            <td><?= esc($activity['activity']) ?></td>
            <td>
                <?= esc($activity['table_name']) ?>
                <?php if (isset($activity['row_id']) && $activity['row_id']): ?>
                    #<?= $activity['row_id'] ?>
                <?php endif; ?>
            </td>
            <td><?= $activity['created_at'] ?></td>
        </tr>
    <?php endforeach; ?>
</tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No recent activities</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>