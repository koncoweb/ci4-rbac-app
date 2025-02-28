<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
    <img class="mb-4 auth-logo" src="<?= base_url('assets/img/logo.png') ?>" alt="Logo" onerror="this.src='https://via.placeholder.com/100x100?text=CI4'">
    <h1 class="h3 mb-3 fw-normal">Reset Password</h1>
    
    <p class="mb-3">Enter your email address and we'll send you instructions to reset your password.</p>
    
    <?= form_open('auth/resetPassword') ?>
        <div class="form-floating mb-3">
            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
            <label for="email">Email</label>
        </div>
        
        <button class="w-100 btn btn-lg btn-primary" type="submit">Send Reset Link</button>
    <?= form_close() ?>
    
    <div class="mt-3">
        <p><a href="<?= base_url('auth/login') ?>">Back to login</a></p>
    </div>
<?= $this->endSection() ?>