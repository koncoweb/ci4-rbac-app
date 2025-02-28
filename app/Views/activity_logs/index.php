<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?= $title ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php /* Temporarily removed permission check */ ?>
        <form action="<?= base_url('activity-logs/clear') ?>" method="post" class="me-2" onsubmit="return confirm('Are you sure you want to clear all logs? This action cannot be undone.');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-danger">
                <i class="fas fa-trash-alt me-1"></i> Clear All Logs
            </button>
        </form>
        <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
</div>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->has('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No activity logs found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= $log['id'] ?></td>
                                <td>
                                    <?php if (!empty($log['username'])): ?>
                                        <?= esc($log['username']) ?> (<?= esc($log['email']) ?>)
                                    <?php else: ?>
                                        <span class="text-muted">Unknown User</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($log['action']) ?></td>
                                <td>
                                    <?php if (!isset($log['details'])): ?>
                                        <span class="text-muted">No details</span>
                                    <?php elseif (strlen($log['details']) > 100): ?>
                                        <span data-bs-toggle="tooltip" title="<?= esc($log['details']) ?>">
                                            <?= esc(substr($log['details'], 0, 100)) ?>...
                                        </span>
                                    <?php else: ?>
                                        <?= esc($log['details']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($log['ip_address']) ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($log['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if (!empty($logs)): ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    Showing <?= ($currentPage - 1) * $perPage + 1 ?> to <?= min(($currentPage - 1) * $perPage + count($logs), $total) ?> of <?= $total ?> entries
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('activity-logs?page=' . ($currentPage - 1)) ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php 
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min(ceil($total / $perPage), $currentPage + 2);
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= base_url('activity-logs?page=' . $i) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < ceil($total / $perPage)): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('activity-logs?page=' . ($currentPage + 1)) ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>

<?= $this->endSection() ?>