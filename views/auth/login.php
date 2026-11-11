<?php
$errors    = $errors ?? [];
$old       = $old ?? [];
$csrfToken = $csrfToken ?? '';
$success   = $success ?? null;

$esc = static function ($text) {
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
};
?>

<section class="auth-section">
    <div class="auth-card">
        <h1 class="auth-title">Đăng nhập</h1>
        <p class="auth-subtitle">Đăng nhập vào tài khoản SPORTIX của bạn.</p>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <?= $esc($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger" role="alert">
                <?= $esc($errors['general']) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>?action=login" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken) ?>">

            <div class="mb-3">
                <label class="form-label" for="login">Email hoặc tên đăng nhập</label>
                <input type="text"
                       class="form-control<?= isset($errors['login']) ? ' is-invalid' : '' ?>"
                       id="login" name="login" value="<?= $esc($old['login'] ?? '') ?>"
                       autocomplete="username">
                <?php if (isset($errors['login'])): ?>
                    <div class="invalid-feedback"><?= $esc($errors['login']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Mật khẩu</label>
                <input type="password"
                       class="form-control<?= isset($errors['password']) ? ' is-invalid' : '' ?>"
                       id="password" name="password" autocomplete="current-password">
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback"><?= $esc($errors['password']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-sportix w-100">Đăng nhập</button>
        </form>

        <p class="auth-switch">
            Chưa có tài khoản?
            <a href="<?= BASE_URL ?>?action=register">Đăng ký ngay</a>
        </p>
    </div>
</section>
