-- ============================================================
-- SPORTIX - Bổ sung trường `refund_note` và `return_reject_reason` vào bảng `orders`
-- File: database/updates/2026_11_26_add_return_reject_reason.sql
-- ============================================================

ALTER TABLE `orders`
    ADD COLUMN IF NOT EXISTS `refund_note` TEXT NULL AFTER `return_image`,
    ADD COLUMN IF NOT EXISTS `return_reject_reason` TEXT NULL AFTER `refund_note`;
