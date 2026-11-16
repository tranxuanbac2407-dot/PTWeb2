<?php
require_once __DIR__ . '/../partials/guard.php';

$users        = $users ?? [];
$total        = count($users);
$flashSuccess = $flashSuccess ?? null;
$flashError   = $flashError ?? null;
?>

<?php if (!empty($flashSuccess)): ?>
    <div class="admin-alert admin-alert-success" role="alert">
        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
        <span><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="admin-alert admin-alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
        <span><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
    </div>
<?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>
            <i class="bi bi-people" aria-hidden="true"></i>
            <span>Danh sách tài khoản</span>
            <span class="admin-count-badge"><?= $total ?> tài khoản</span>
        </h3>
    </div>

    <?php if (!empty($users)): ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;" class="text-center">STT</th>
                        <th>Họ và tên</th>
                        <th>Tên đăng nhập</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th style="width: 140px;" class="text-center">Vai trò</th>
                        <th style="width: 140px;" class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $index => $user): ?>
                        <?php
                            $stt       = $index + 1;
                            $userId    = (int) ($user['id'] ?? 0);
                            $fullName  = trim((string) ($user['full_name'] ?? ''));
                            $username  = trim((string) ($user['username'] ?? ''));
                            $email     = trim((string) ($user['email'] ?? ''));
                            $phone     = trim((string) ($user['phone'] ?? ''));
                            $role      = (string) ($user['role'] ?? '');
                            $isAdmin   = $role === 'admin';
                            $roleLabel = $isAdmin ? 'Admin' : 'Khách hàng';

                            $displayFullName = $fullName !== '' ? $fullName : '-';
                            $displayUsername = $username !== '' ? $username : '-';
                            $displayEmail    = $email !== '' ? $email : '-';
                            $displayPhone    = $phone !== '' ? $phone : '-';
                        ?>
                        <tr>
                            <td class="text-center"><?= $stt ?></td>
                            <td><?= htmlspecialchars($displayFullName, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($displayUsername, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($displayEmail, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($displayPhone, ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-center">
                                <span class="admin-count-badge"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td class="text-center">
                                <?php if (!$isAdmin && $userId > 0): ?>
                                    <div class="admin-row-actions">
                                        <a href="<?= BASE_URL ?>?action=admin-user-delete&amp;id=<?= $userId ?>"
                                           class="admin-btn-action admin-btn-action-delete"
                                           title="Xóa tài khoản khách hàng">
                                            <i class="bi bi-trash3" aria-hidden="true"></i>
                                            <span>Xóa</span>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Admin</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="admin-empty">
            <i class="bi bi-people" aria-hidden="true"></i>
            <strong>Chưa có tài khoản nào trong hệ thống</strong>
            <span>Hiện tại chưa có dữ liệu tài khoản để hiển thị.</span>
        </div>
    <?php endif; ?>
</div>