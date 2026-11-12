-- ============================================================
-- SPORTIX - Thêm dữ liệu mẫu Danh mục & Sản phẩm cho Trang chủ
-- File: database/updates/2026_11_12_seed_categories_and_products.sql
--
-- Cách chạy:
--   Dán nội dung file này vào phpMyAdmin -> chọn database `ptweb` -> tab SQL -> Go.
-- ============================================================

-- 1. Thêm danh mục sản phẩm thể thao mẫu (nếu chưa có)
INSERT INTO `categories` (`categories_id`, `categories_name`, `description`) VALUES
(1, 'Áo thể thao nam', 'Áo thun, áo khoác, áo nỉ tập luyện co giãn 4 chiều thoáng khí'),
(2, 'Áo thể thao nữ', 'Áo bra, croptop, áo tank-top thời trang tôn dáng'),
(3, 'Quần thể thao', 'Quần jogger, legging, quần short thể thao năng động'),
(4, 'Giày & Dép thể thao', 'Giày chạy bộ, giày sneaker và dép đúc thể thao êm ái'),
(5, 'Bộ đồ thể thao', 'Set đồ thể thao, track-suit tập gym, chạy bộ đồng bộ cá tính'),
(6, 'Phụ kiện thể thao', 'Balo, túi tập, mũ lưỡi trai, tất và bình nước thể thao')
ON DUPLICATE KEY UPDATE `categories_name` = VALUES(`categories_name`), `description` = VALUES(`description`);

-- 2. Thêm sản phẩm mẫu
INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `price`, `description`, `image`, `size`, `color`, `stock_quantity`) VALUES
(1, 1, 'Áo Thun Thể Thao Nam Pro Dry-Fit Co Giãn 4 Chiều', 289000.00, 'Chất liệu thun lạnh cao cấp, thấm hút mồ hôi siêu tốc, phù hợp chạy bộ và tập gym.', 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=500&auto=format&fit=crop&q=80', 'M,L,XL,XXL', 'Đen, Xanh Navy, Trắng', 50),
(2, 2, 'Áo Bra Thể Thao Tập Gym & Yoga Nâng Đỡ Tối Đa', 249000.00, 'Thiết kế ôm sát nhẹ nhàng, mút đệm thoáng khí, dây chéo lưng cá tính.', 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=500&auto=format&fit=crop&q=80', 'S,M,L', 'Hồng Pastel, Đen, Xanh Rêu', 45),
(3, 3, 'Quần Jogger Thể Thao Phối Lưới Thoáng Khí', 359000.00, 'Chất vải thun dù cao cấp, bo gấu co giãn, túi khóa kéo tiện lợi.', 'https://images.unsplash.com/photo-1552902865-b72c031ac5ea?w=500&auto=format&fit=crop&q=80', 'M,L,XL', 'Xám Đậm, Đen', 35),
(4, 4, 'Giày Chạy Bộ Nam Nữ SPORTIX Ultralight Êm Chân', 799000.00, 'Đế đệm bọt khí trợ lực, thân dệt knit thoáng khí 360 độ.', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&auto=format&fit=crop&q=80', '39,40,41,42,43', 'Đỏ Đen, Trắng Cam', 60),
(5, 1, 'Áo Khoác Gió Thể Thao Chống Nước & Cản Gió', 499000.00, 'Vải gió tráng bạc 2 lớp, cản gió và chống nước mưa phùn hiệu quả.', 'https://images.unsplash.com/photo-1544441893-675973e31985?w=500&auto=format&fit=crop&q=80', 'M,L,XL', 'Đen, Xanh Rêu', 30),
(6, 3, 'Quần Short Thể Thao 2 Lớp Chống Lộ Cho Nữ', 199000.00, 'Thiết kế 2 lớp thông minh: lớp lót ôm đùi an toàn và lớp ngoài thoáng mát.', 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=500&auto=format&fit=crop&q=80', 'S,M,L', 'Đen, Tím Nhạt', 55),
(7, 5, 'Set Đồ Thể Thao Nam Nữ Dynamic Đường Phố', 589000.00, 'Phong cách sporty-chic thời thượng, mặc tập luyện hay dạo phố đều đẹp.', 'https://images.unsplash.com/photo-1483721074576-579a571a29b3?w=500&auto=format&fit=crop&q=80', 'M,L,XL', 'Xám Khói, Đen', 25),
(8, 6, 'Balo Thể Thao Chống Nước Có Ngăn Chứa Giày', 329000.00, 'Nhiều ngăn đa năng, đáy chứa giày riêng biệt chống bám mùi.', 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=500&auto=format&fit=crop&q=80', 'Freesize', 'Đen, Xám Ghi', 40),
(9, 1, 'Áo Polo Thể Thao Nam Vải Pique Cao Cấp', 320000.00, 'Cổ bẻ lịch sự, chất vải dệt mắt chim sang trọng, thấm hút mồ hôi tốt.', 'https://images.unsplash.com/photo-1625910513413-7222d3d95711?w=500&auto=format&fit=crop&q=80', 'M,L,XL,XXL', 'Trắng, Xanh Đậm, Đen', 80),
(10, 4, 'Giày Sneaker Thể Thao Phối Màu Streetwear Unisex', 850000.00, 'Thiết kế chunky retro thời thượng, đệm đế êm ái tôn chiều cao.', 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=500&auto=format&fit=crop&q=80', '36,37,38,39,40,41,42', 'Trắng Xám, Đen Trắng', 65),
(11, 3, 'Quần Legging Thể Thao Nữ Cạp Cao Định Hình', 280000.00, 'Cạp cao giấu bụng, chất vải dệt kim mềm mịn co giãn 4 chiều.', 'https://images.unsplash.com/photo-1506629082955-511b1aa562c8?w=500&auto=format&fit=crop&q=80', 'S,M,L', 'Đen, Xanh Rêu', 70),
(12, 1, 'Áo Thun Sát Nách Nam Chuyên Tập Gym Cơ Bắp', 179000.00, 'Khoét nách sâu thoải mái vận động, vải cotton compact co giãn cao.', 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=500&auto=format&fit=crop&q=80', 'M,L,XL', 'Đen, Xám Trắng', 85)
ON DUPLICATE KEY UPDATE `product_name` = VALUES(`product_name`), `price` = VALUES(`price`);

-- 3. Tạo đơn hàng và chi tiết đơn hàng mẫu để tính sản phẩm bán chạy
INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `receiver_name`, `phone`, `shipping_address`, `total_amount`, `payment_method`, `order_status`) VALUES
(1, 1, NOW(), 'Nguyễn Văn An', '0987654321', 'Số 13 Trịnh Văn Bô, Nam Từ Liêm, Hà Nội', 1170000.00, 'cod', 'completed'),
(2, 2, NOW(), 'Trần Thị Mai', '0912345678', 'Quận Cầu Giấy, Hà Nội', 850000.00, 'momo', 'completed')
ON DUPLICATE KEY UPDATE `receiver_name` = VALUES(`receiver_name`);

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `subtotal`) VALUES
(1, 1, 9, 'Áo Polo Thể Thao Nam Vải Pique Cao Cấp', 320000.00, 2, 640000.00),
(2, 1, 12, 'Áo Thun Sát Nách Nam Chuyên Tập Gym Cơ Bắp', 179000.00, 3, 537000.00),
(3, 2, 10, 'Giày Sneaker Thể Thao Phối Màu Streetwear Unisex', 850000.00, 1, 850000.00),
(4, 2, 11, 'Quần Legging Thể Thao Nữ Cạp Cao Định Hình', 280000.00, 2, 560000.00)
ON DUPLICATE KEY UPDATE `quantity` = VALUES(`quantity`);
