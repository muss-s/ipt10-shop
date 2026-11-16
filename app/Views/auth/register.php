<div class="narrow">
    <h1>Create account</h1>
    <form method="post" action="<?= url('/register') ?>" class="form">
        <?= csrf_field() ?>
        <?php foreach (['name' => ['Name', 'text'], 'email' => ['Email', 'email'], 'password' => ['Password (8+ characters)', 'password'], 'password_confirmation' => ['Confirm password', 'password']] as $field => [$label, $type]): ?>
            <label><?= e($label) ?>
                <input type="<?= $type ?>" name="<?= $field ?>" value="<?= $type === 'password' ? '' : e(old($field)) ?>" required>
                <?php if ($err = error_for($field)): ?><small class="error"><?= e($err) ?></small><?php endif ?>
            </label>
        <?php endforeach ?>
        <button class="btn" type="submit">Sign up</button>
    </form>
    <p class="muted">Already registered? <a href="<?= url('/login') ?>">Log in</a>.</p>
</div>
