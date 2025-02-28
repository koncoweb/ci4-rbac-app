<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
    <img class="mb-4 auth-logo" src="<?= base_url('assets/img/logo.png') ?>" alt="Logo" onerror="this.src='https://via.placeholder.com/100x100?text=CI4'">
    <h1 class="h3 mb-3 fw-normal">Please sign in</h1>
    
    <?= form_open('auth/attemptLogin') ?>
        <div class="form-floating">
            <input type="text" class="form-control" id="login_id" name="login_id" placeholder="Username or Email" value="<?= old('login_id') ?>" required>
            <label for="login_id">Username or Email</label>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <label for="password">Password</label>
        </div>
        
        <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
    <?= form_close() ?>
    
    <div class="mt-3">
        <p>Don't have an account? <a href="<?= base_url('auth/register') ?>">Register</a></p>
        <p><a href="<?= base_url('auth/forgotPassword') ?>">Forgot password?</a></p>
    </div>
<?= $this->endSection() ?>

