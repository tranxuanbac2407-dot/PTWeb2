-- ============================================================
-- SPORTIX - Cập nhật bảng `users` phục vụ chức năng Đăng ký / Đăng nhập
-- File: database/updates/2026_11_11_add_user_auth_fields.sql
--
-- Bối cảnh: bảng `users` hiện tại chỉ có (id, username, password, role).
-- Chức năng đăng ký cần thêm họ tên, email và (tùy chọn) số điện thoại.
--
-- Cách chạy (KHÔNG xóa / KHÔNG tạo lại database):
--   mysql -u root ptweb < database/updates/2026_11_11_add_user_auth_fields.sql
-- hoặc dán nội dung file này vào phpMyAdmin -> database `ptweb` -> SQL.
--
-- Lưu ý: chỉ chạy file này MỘT lần.
-- ============================================================

-- 1) Bổ sung các cột còn thiếu.
--    `email` cho phép NULL để không phá vỡ các tài khoản đang có sẵn.
ALTER TABLE `users`
    ADD COLUMN `full_name` varchar(255) COLLATE utf8mb4_general_ci NULL AFTER `id`,
    ADD COLUMN `email`     varchar(255) COLLATE utf8mb4_general_ci NULL AFTER `username`,
    ADD COLUMN `phone`     varchar(20)  COLLATE utf8mb4_general_ci NULL AFTER `email`;

-- 2) Email phải là duy nhất (MySQL cho phép nhiều giá trị NULL trên cột UNIQUE).
ALTER TABLE `users`
    ADD UNIQUE KEY `email` (`email`);

-- 3) Mã hóa mật khẩu của các tài khoản mẫu đang lưu dạng văn bản thuần.
--    Mật khẩu gốc của 3 tài khoản mẫu (admin / abc / xyz) là: 123456
--    Bỏ qua bước này nếu dữ liệu của bạn đã được mã hóa bằng password_hash().
UPDATE `users`
    SET `password` = '$2y$10$Z4HLa38QgTpPPCeg5zl3we0aVAc2oaulBj0XBS4Mf1pyNNAOgWX2G'
    WHERE `password` = '123456';
