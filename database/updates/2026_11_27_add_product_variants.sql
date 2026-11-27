-- SPORTIX Stage 2.3: tồn kho theo biến thể màu + size
-- File này chỉ được tạo; không tự chạy. Bạn chạy khi sẵn sàng.

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `product_variants` (
  `variant_id` int NOT NULL,
  `product_id` int NOT NULL,
  `color` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD UNIQUE KEY `uq_product_variant` (`product_id`,`color`,`size`),
  ADD KEY `fk_product_variants_products` (`product_id`);

ALTER TABLE `order_items`
  ADD COLUMN `variant_id` int DEFAULT NULL AFTER `product_id`,
  ADD KEY `fk_order_items_variants` (`variant_id`);

ALTER TABLE `orders`
  ADD COLUMN `stock_restored` tinyint(1) NOT NULL DEFAULT '0';

ALTER TABLE `product_variants`
  MODIFY `variant_id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_product_variants_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_variants` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`variant_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

COMMIT;
