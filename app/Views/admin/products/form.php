<?php
$isEdit = $product !== null;
$val = fn (string $field, mixed $fromProduct = '') => old($field, $isEdit ? $fromProduct : '');
?>
<a class="muted small" href="<?= url('/admin/products') ?>">← Products</a>
<h1><?= $isEdit ? 'Edit product' : 'New product' ?></h1>

<form method="post" action="<?= url($isEdit ? '/admin/products/' . $product['id'] : '/admin/products') ?>" class="form narrow-left">
    <?= csrf_field() ?>
    <label>Name
        <input name="name" value="<?= e($val('name', $product['name'] ?? '')) ?>" required>
        <?php if ($err = error_for('name')): ?><small class="error"><?= e($err) ?></small><?php endif ?>
    </label>
    <label>Category
        <select name="category_id" required>
            <?php $selected = (string) $val('category_id', $product['category_id'] ?? ''); ?>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat['id'] ?>" <?= $selected === (string) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach ?>
        </select>
        <?php if ($err = error_for('category_id')): ?><small class="error"><?= e($err) ?></small><?php endif ?>
    </label>
    <div class="row">
        <label>Price (PHP)
            <input type="number" step="0.01" min="0" name="price" value="<?= e($val('price', isset($product) ? number_format($product['price_cents'] / 100, 2, '.', '') : '')) ?>" required>
            <?php if ($err = error_for('price')): ?><small class="error"><?= e($err) ?></small><?php endif ?>
        </label>
        <label>Stock
            <input type="number" min="0" name="stock" value="<?= e($val('stock', $product['stock'] ?? '')) ?>" required>
            <?php if ($err = error_for('stock')): ?><small class="error"><?= e($err) ?></small><?php endif ?>
        </label>
    </div>
    <label>Image URL
        <input type="url" name="image_url" value="<?= e($val('image_url', $product['image_url'] ?? '')) ?>">
    </label>
    <label>Description
        <textarea name="description" rows="5"><?= e($val('description', $product['description'] ?? '')) ?></textarea>
        <?php if ($err = error_for('description')): ?><small class="error"><?= e($err) ?></small><?php endif ?>
    </label>
    <button class="btn" type="submit"><?= $isEdit ? 'Save changes' : 'Create product' ?></button>
</form>
