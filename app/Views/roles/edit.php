<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Edit Role</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="<?= base_url('roles') ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Roles
            </a>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-edit me-1"></i>
            Edit Role: <?= esc($role['name']) ?>
        </div>
        <div class="card-body">
           
        
        <?php if (session()->has('errors')): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach (session('errors') as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger">
        <?= session('error') ?>
    </div>
<?php endif; ?>
            
            <form action="<?= base_url('roles/update/' . $role['id']) ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="mb-3">
                    <label for="name" class="form-label">Role Name</label>
                    <input type="text" class="form-control" id="name" name="name" 
                           value="<?= old('name', $role['name']) ?>" required
                           <?= in_array($role['name'], ['admin', 'user']) ? 'readonly' : '' ?>>
                    <div class="form-text">Role name should be alphanumeric with spaces.</div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= old('description', $role['description']) ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Permissions</label>
                    <div class="row">
                        <?php foreach ($permissions as $permission): ?>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" 
                                           value="<?= $permission['id'] ?>" id="permission_<?= $permission['id'] ?>"
                                           <?= in_array($permission['id'], $rolePermissions) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="permission_<?= $permission['id'] ?>">
                                        <?= esc($permission['name']) ?>
                                        <?php if (!empty($permission['description'])): ?>
                                            <i class="fas fa-info-circle text-info" 
                                               data-bs-toggle="tooltip" title="<?= esc($permission['description']) ?>"></i>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Role
                    </button>
                    <a href="<?= base_url('roles') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
<?= $this->endSection() ?>