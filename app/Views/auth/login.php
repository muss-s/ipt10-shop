<div class="narrow">
    <h1>Log in</h1>
    <form method="post" action="<?= url('/login') ?>" class="form">
        <?= csrf_field() ?>
        <label>Email
            <input type="email" name="email" value="<?= e(old('email')) ?>" required autofocus>
            <?php if ($err = error_for('email')): ?><small class="error"><?= e($err) ?></small><?php endif ?>
        </label>
        <label>Password
            <input type="password" name="password" required>
            <?php if ($err = error_for('password')): ?><small class="error"><?= e($err) ?></small><?php endif ?>
        </label>
        <button class="btn" type="submit">Log in</button>
    </form>
    <p class="muted">No account? <a href="<?= url('/register') ?>">Sign up</a>.</p>
</div>
