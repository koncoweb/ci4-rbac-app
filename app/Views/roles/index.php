<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Role Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= base_url('roles/new') ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Add New Role
            </a>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-tag me-1"></i>
            Roles List
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="rolesTable">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Created At</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($roles)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No roles found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($roles as $role): ?>
                                <tr>
                                    <td><?= $role['id'] ?></td>
                                    <td><?= esc($role['name']) ?></td>
                                    <td><?= esc($role['description'] ?? 'No description') ?></td>
                                    <td><?= date('Y-m-d H:i:s', strtotime($role['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('roles/edit/' . $role['id']) ?>" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <?php if (!in_array($role['name'], ['admin', 'user'])): // Prevent deleting core roles ?>
                                            <a href="<?= base_url('roles/delete/' . $role['id']) ?>" class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this role?');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
        $('#rolesTable').DataTable();
    });
</script>
<?= $this->endSection() ?>