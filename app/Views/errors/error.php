<div class="error-page">
    <div class="error-kanji" aria-hidden="true"><?= $status >= 500 ? '壊' : ($status === 403 ? '禁' : '迷') ?></div>
    <h1 class="glitch error-code" data-text="<?= (int) $status ?>"><?= (int) $status ?></h1>
    <pre class="error-msg"><?= e($message) ?></pre>
    <a class="btn btn-big" href="<?= url('/') ?>" data-magnetic>Back to home 戻る</a>
</div>
