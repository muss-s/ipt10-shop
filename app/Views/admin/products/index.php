<div class="row between">
    <h1>Products</h1>
    <a class="btn" href="<?= url('/admin/products/create') ?>">+ New product</a>
</div>
<table class="table">
    <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($products as $p): ?>
        <tr>
            <td><a href="<?= url('/products/' . $p['slug']) ?>"><?= e($p['name']) ?></a></td>
            <td><?= e($p['category_name'] ?? '—') ?></td>
            <td><?= peso((int) $p['price_cents']) ?></td>
            <td class="<?= $p['stock'] <= 5 ? 'error' : '' ?>"><?= (int) $p['stock'] ?></td>
            <td class="row">
                <a class="btn btn-small btn-ghost" href="<?= url('/admin/products/' . $p['id'] . '/edit') ?>">Edit</a>
                <form method="post" action="<?= url('/admin/products/' . $p['id']) ?>" onsubmit="return confirm('Delete this product?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button class="linklike danger" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
