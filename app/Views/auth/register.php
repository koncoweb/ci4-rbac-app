<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
    <img class="mb-4 auth-logo" src="<?= base_url('assets/img/logo.png') ?>" alt="Logo" onerror="this.src='https://via.placeholder.com/100x100?text=CI4'">
    <h1 class="h3 mb-3 fw-normal">Register new account</h1>
    
    <?= form_open('auth/attemptRegister') ?>
        <div class="form-floating mb-2">
            <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?= old('username') ?>" required>
            <label for="username">Username</label>
        </div>
        <div class="form-floating mb-2">
            <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?= old('email') ?>" required>
            <label for="email">Email</label>
        </div>
        <div class="form-floating mb-2">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <label for="password">Password</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Confirm Password" required>
            <label for="password_confirm">Confirm Password</label>
        </div>
        
        <button class="w-100 btn btn-lg btn-primary" type="submit">Register</button>
    <?= form_close() ?>
    
    <div class="mt-3">
        <p>Already have an account? <a href="<?= base_url('auth/login') ?>">Login</a></p>
    </div>
<?= $this->endSection() ?>