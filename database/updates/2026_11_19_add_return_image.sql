-- ============================================================
-- SPORTIX - Bổ sung trường `return_image` vào bảng `orders`
-- File: database/updates/2026_11_19_add_return_image.sql
--
-- Mục đích:
--   Lưu hình ảnh chụp sản phẩm còn nguyên tem mác khi khách hàng gửi yêu cầu hoàn hàng
--   để quản trị viên xác nhận hoàn tiền và điều phối nhân viên đến lấy lại hàng.
-- ============================================================

ALTER TABLE `orders`
    ADD COLUMN `return_image` VARCHAR(255) NULL AFTER `return_reason`;
