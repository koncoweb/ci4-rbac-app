<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Permissions Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= base_url('permissions/new') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> New Permission
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
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-key me-1"></i>
            Permissions List
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="permissionsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($permissions as $permission): ?>
                            <tr>
                                <td><?= $permission['id'] ?></td>
                                <td><?= esc($permission['name']) ?></td>
                                <td><?= esc($permission['description'] ?? '') ?></td>
                                <td><?= $permission['created_at'] ?></td>
                                <td><?= $permission['updated_at'] ?></td>
                                <td>
                                    <a href="<?= base_url('permissions/edit/' . $permission['id']) ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="<?= base_url('permissions/delete/' . $permission['id']) ?>" class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this permission?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($permissions)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No permissions found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#permissionsTable').DataTable({
            responsive: true
        });
    });
</script>
<?= $this->endSection() ?>