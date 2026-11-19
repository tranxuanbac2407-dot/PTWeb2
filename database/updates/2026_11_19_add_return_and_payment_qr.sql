-- ============================================================
-- SPORTIX - Cập nhật bảng `orders` phục vụ chức năng Hoàn hàng & Thanh toán QR
-- File: database/updates/2026_11_19_add_return_and_payment_qr.sql
--
-- Bổ sung:
--   1. `return_reason`: Lưu lý do khách hàng yêu cầu hoàn hàng
--   2. `payment_status`: Trạng thái thanh toán (unpaid, paid, refunded)
--
-- Cách chạy:
--   mysql -u root ptweb < database/updates/2026_11_19_add_return_and_payment_qr.sql
-- hoặc chạy trực tiếp trong phpMyAdmin -> database `ptweb` -> SQL.
-- ============================================================

ALTER TABLE `orders`
    ADD COLUMN `return_reason` TEXT NULL AFTER `order_status`,
    ADD COLUMN `payment_status` VARCHAR(50) DEFAULT 'unpaid' AFTER `payment_method`;
