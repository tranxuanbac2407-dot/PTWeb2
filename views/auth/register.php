<?php
$errors    = $errors ?? [];
$old       = $old ?? [];
$csrfToken = $csrfToken ?? '';

$val = static function ($key) use ($old) {
    return htmlspecialchars((string) ($old[$key] ?? ''), ENT_QUOTES, 'UTF-8');
};
$esc = static function ($text) {
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
};
?>

<section class="auth-section">
    <div class="auth-card">
        <h1 class="auth-title">Đăng ký tài khoản</h1>
        <p class="auth-subtitle">Tạo tài khoản SPORTIX để mua sắm nhanh chóng và theo dõi đơn hàng.</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger" role="alert">
                <?= $esc($errors['general']) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= BASE_URL ?>?action=register" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $esc($csrfToken) ?>">

            <div class="mb-3">
                <label class="form-label" for="full_name">Họ và tên</label>
                <input type="text"
                       class="form-control<?= isset($errors['full_name']) ? ' is-invalid' : '' ?>"
                       id="full_name" name="full_name" value="<?= $val('full_name') ?>"
                       autocomplete="name">
                <?php if (isset($errors['full_name'])): ?>
                    <div class="invalid-feedback"><?= $esc($errors['full_name']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="username">Tên đăng nhập</label>
                <input type="text"
                       class="form-control<?= isset($errors['username']) ? ' is-invalid' : '' ?>"
                       id="username" name="username" value="<?= $val('username') ?>"
                       autocomplete="username">
                <?php if (isset($errors['username'])): ?>
                    <div class="invalid-feedback"><?= $esc($errors['username']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email"
                       class="form-control<?= isset($errors['email']) ? ' is-invalid' : '' ?>"
                       id="email" name="email" value="<?= $val('email') ?>"
                       autocomplete="email">
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback"><?= $esc($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="phone">Số điện thoại <span class="text-muted">(không bắt buộc)</span></label>
                <input type="text"
                       class="form-control<?= isset($errors['phone']) ? ' is-invalid' : '' ?>"
                       id="phone" name="phone" value="<?= $val('phone') ?>"
                       autocomplete="tel">
                <?php if (isset($errors['phone'])): ?>
                    <div class="invalid-feedback"><?= $esc($errors['phone']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Mật khẩu</label>
                <input type="password"
                       class="form-control<?= isset($errors['password']) ? ' is-invalid' : '' ?>"
                       id="password" name="password" autocomplete="new-password">
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback"><?= $esc($errors['password']) ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password_confirm">Nhập lại mật khẩu</label>
                <input type="password"
                       class="form-control<?= isset($errors['password_confirm']) ? ' is-invalid' : '' ?>"
                       id="password_confirm" name="password_confirm" autocomplete="new-password">
                <?php if (isset($errors['password_confirm'])): ?>
                    <div class="invalid-feedback"><?= $esc($errors['password_confirm']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-sportix w-100">Đăng ký</button>
        </form>

        <p class="auth-switch">
            Đã có tài khoản?
            <a href="<?= BASE_URL ?>?action=login">Đăng nhập</a>
        </p>
    </div>
</section>
