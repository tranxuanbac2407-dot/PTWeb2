<?php
require_once __DIR__ . '/../partials/guard.php';

$targetUser = $targetUser ?? null;
$csrfToken  = $csrfToken ?? '';
$flashError = $flashError ?? null;

$id       = (int) ($targetUser['id'] ?? 0);
$fullName = trim((string) ($targetUser['full_name'] ?? ''));
$username = trim((string) ($targetUser['username'] ?? ''));
$email    = trim((string) ($targetUser['email'] ?? ''));

$displayFullName = $fullName !== '' ? $fullName : '-';
$displayUsername = $username !== '' ? $username : '-';
$displayEmail    = $email !== '' ? $email : '-';
?>

<?php if (!empty($flashError)): ?>
    <div class="admin-alert admin-alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
        <span><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>
            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
            <span>Xác nhận xóa tài khoản khách hàng</span>
        </h3>
    </div>

    <div class="admin-card-body">
        <div class="admin-alert admin-alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
            <span>Hành động này không thể hoàn tác. Vui lòng kiểm tra kỹ thông tin tài khoản trước khi xác nhận xóa.</span>
        </div>

        <div class="table-responsive mb-3">
            <table class="admin-table">
                <tbody>
                    <tr>
                        <th style="width: 220px;">ID tài khoản</th>
                        <td><?= $id > 0 ? '#' . htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8') : '-' ?></td>
                    </tr>
                    <tr>
                        <th>Họ và tên</th>
                        <td><?= htmlspecialchars($displayFullName, ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Tên đăng nhập</th>
                        <td><?= htmlspecialchars($displayUsername, ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?= htmlspecialchars($displayEmail, ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <form action="<?= BASE_URL ?>?action=admin-user-delete" method="post" class="d-flex gap-2 flex-wrap">
            <input type="hidden" name="id" value="<?= $id ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash3" aria-hidden="true"></i>
                Xác nhận xóa
            </button>

            <a href="<?= BASE_URL ?>?action=admin-users" class="btn btn-secondary">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                Quay lại
            </a>
        </form>
    </div>
</div>