<?php

class HomeController extends BaseController
{
    /**
     * Trang chủ website SPORTIX.
     * Tải dữ liệu Banner, Danh mục nổi bật, Sản phẩm mới và Sản phẩm bán chạy.
     */
    public function index()
    {
        $categoryModel = new CategoryModel();
        $productModel  = new ProductModel();

        // 1. Dữ liệu Banner trình chiếu
        $banners = [
            [
                'id'          => 1,
                'eyebrow'     => 'BỘ SƯU TẬP MỚI 2026',
                'title'       => 'Bứt Phá Giới Hạn <span class="accent">Nâng Tầm Phong Cách</span>',
                'subtitle'    => 'Trang phục & phụ kiện thể thao chính hãng, thiết kế khí động học giúp tối ưu hiệu suất thi đấu và tập luyện.',
                'link'        => BASE_URL . '?action=products',
                'btn_text'    => 'Khám phá ngay',
                'badge'       => 'HOT COLLECTION',
                'theme'       => 'banner-theme-dark',
                'image_bg'    => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=1400&auto=format&fit=crop&q=80',
            ],
            [
                'id'          => 2,
                'eyebrow'     => 'ƯU ĐÃI ĐẶC BIỆT',
                'title'       => 'Đại Tiệc Thể Thao <span class="accent">Giảm Đến 50%</span>',
                'subtitle'    => 'Hàng trăm sản phẩm áo thun, quần jogger và giày chạy bộ đang có mức giá hấp dẫn nhất mùa giải.',
                'link'        => BASE_URL . '?action=products&amp;filter=sale',
                'btn_text'    => 'Săn sale liền tay',
                'badge'       => 'SALE 50%',
                'theme'       => 'banner-theme-fire',
                'image_bg'    => 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?w=1400&auto=format&fit=crop&q=80',
            ],
            [
                'id'          => 3,
                'eyebrow'     => 'GYM & RUNNING GEAR',
                'title'       => 'Chinh Phục Mọi <span class="accent">Đường Chạy</span>',
                'subtitle'    => 'Công nghệ vải Dry-Fit thấm hút mồ hôi siêu tốc, sợi co giãn 4 chiều cho chuyển động tự do không giới hạn.',
                'link'        => BASE_URL . '?action=products&amp;category=nam',
                'btn_text'    => 'Xem bộ sưu tập',
                'badge'       => 'PRO RUNNING',
                'theme'       => 'banner-theme-blue',
                'image_bg'    => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=1400&auto=format&fit=crop&q=80',
            ],
        ];

        // 2. Danh mục nổi bật
        $featuredCategories = $categoryModel->getFeaturedCategories(6);

        // 3. Sản phẩm mới
        $newProducts = $productModel->getNewProducts(8);

        // 4. Sản phẩm bán chạy
        $bestSellingProducts = $productModel->getBestSellingProducts(8);

        $title = 'SPORTIX | Thời trang thể thao nam & nữ chính hãng';
        $view  = 'client/home';

        require_once PATH_VIEW_MAIN;
    }

    public function search()
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $products = [];

        if ($keyword !== '') {
            $productModel = new ProductModel();
            $products = $productModel->searchByKeyword($keyword);
        }

        $title = 'Tìm kiếm "' . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') . '" | SPORTIX';
        $view  = 'client/search';

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Xem chi tiết sản phẩm và các thuộc tính (size, color, tồn kho, sản phẩm liên quan).
     */
    public function productDetail()
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $productModel = new ProductModel();
        $product = $productModel->getById($id);

        if (!$product) {
            $_SESSION['flash_error'] = 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.';
            header('Location: ' . BASE_URL);
            exit;
        }

        // Parse danh sách kích thước (Size)
        $sizes = [];
        if (!empty($product['size'])) {
            $rawSizes = explode(',', (string) $product['size']);
            foreach ($rawSizes as $s) {
                $trimmed = trim($s);
                if ($trimmed !== '') {
                    $sizes[] = $trimmed;
                }
            }
        }

        // Parse danh sách màu sắc (Color)
        $colors = [];
        if (!empty($product['color'])) {
            $rawColors = explode(',', (string) $product['color']);
            foreach ($rawColors as $c) {
                $trimmed = trim($c);
                if ($trimmed !== '') {
                    $colors[] = $trimmed;
                }
            }
        }

        // Lấy sản phẩm liên quan cùng danh mục
        $categoryId = (int) ($product['category_id'] ?? 0);
        $relatedProducts = $productModel->getRelatedProducts($categoryId, $id, 4);

        $title = htmlspecialchars($product['product_name']) . ' | SPORTIX';
        $view  = 'client/product-detail';

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Thêm sản phẩm vào giỏ hàng.
     */
    public function addToCart()
    {
        $productId = (int) ($_POST['product_id'] ?? $_GET['add'] ?? 0);
        $quantity  = (int) ($_POST['quantity'] ?? 1);
        if ($quantity <= 0) {
            $quantity = 1;
        }

        $size   = trim($_POST['size'] ?? '');
        $color  = trim($_POST['color'] ?? '');
        $buyNow = !empty($_POST['buy_now']);

        if ($productId <= 0) {
            header('Location: ' . BASE_URL);
            exit;
        }

        $productModel = new ProductModel();
        $product = $productModel->getById($productId);

        if (!$product) {
            $_SESSION['flash_error'] = 'Sản phẩm không tồn tại.';
            header('Location: ' . BASE_URL);
            exit;
        }

        // Tự động gán thuộc tính mặc định nếu chưa chọn khi thêm nhanh
        if ($size === '' && !empty($product['size'])) {
            $rawSizes = explode(',', (string) $product['size']);
            $size = trim($rawSizes[0] ?? '');
        }
        if ($color === '' && !empty($product['color'])) {
            $rawColors = explode(',', (string) $product['color']);
            $color = trim($rawColors[0] ?? '');
        }

        // Nếu sản phẩm không có thuộc tính size hoặc color
        if ($size === '') {
            $size = 'Freesize';
        }
        if ($color === '') {
            $color = 'Tiêu chuẩn';
        }

        // Tạo khóa định danh duy nhất cho từng biến thể thuộc tính trong giỏ
        $cartKey = $productId . '_' . md5($size . '_' . $color);

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id'    => $productId,
                'name'          => $product['product_name'],
                'price'         => (float) $product['price'],
                'image'         => $product['image'] ?? '',
                'size'          => $size,
                'color'         => $color,
                'quantity'      => $quantity,
                'category_name' => $product['categories_name'] ?? 'Thời trang thể thao',
            ];
        }

        $_SESSION['flash_success'] = 'Đã thêm "' . htmlspecialchars($product['product_name']) . '" (' . $size . ', ' . $color . ') vào giỏ hàng!';

        if ($buyNow) {
            header('Location: ' . BASE_URL . '?action=cart');
        } else {
            header('Location: ' . BASE_URL . '?action=product-detail&id=' . $productId);
        }
        exit;
    }

    /**
     * Xem giỏ hàng của khách hàng.
     */
    public function cart()
    {
        $cart = $_SESSION['cart'] ?? [];
        $subtotal = 0;
        $totalQuantity = 0;

        foreach ($cart as $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $subtotal += ((float) ($item['price'] ?? 0)) * $qty;
            $totalQuantity += $qty;
        }

        // Tính phí vận chuyển: Miễn phí nếu tạm tính >= 500k, ngược lại 30.000đ (0đ nếu giỏ rỗng)
        $shippingFee = ($subtotal > 0 && $subtotal < 500000) ? 30000 : 0;
        $totalAmount = $subtotal + $shippingFee;

        $title = 'Giỏ hàng của bạn (' . $totalQuantity . ' món) | SPORTIX';
        $view  = 'client/cart';

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng.
     */
    public function updateCart()
    {
        $cartKey  = trim($_POST['cart_key'] ?? '');
        $quantity = (int) ($_POST['quantity'] ?? 1);

        if ($cartKey !== '' && isset($_SESSION['cart'][$cartKey])) {
            if ($quantity > 0) {
                $_SESSION['cart'][$cartKey]['quantity'] = min(99, $quantity);
                $_SESSION['flash_success'] = 'Đã cập nhật số lượng giỏ hàng!';
            } else {
                unset($_SESSION['cart'][$cartKey]);
                $_SESSION['flash_success'] = 'Đã xóa sản phẩm khỏi giỏ hàng!';
            }
        }

        header('Location: ' . BASE_URL . '?action=cart');
        exit;
    }

    /**
     * Tăng hoặc giảm nhanh số lượng sản phẩm (+1 / -1) trong giỏ hàng.
     */
    public function changeCartQty()
    {
        $cartKey = trim($_GET['key'] ?? $_POST['cart_key'] ?? '');
        $delta   = (int) ($_GET['delta'] ?? $_POST['delta'] ?? 1);

        if ($cartKey !== '' && isset($_SESSION['cart'][$cartKey])) {
            $currentQty = (int) ($_SESSION['cart'][$cartKey]['quantity'] ?? 1);
            $newQty     = $currentQty + $delta;

            if ($newQty > 0) {
                $_SESSION['cart'][$cartKey]['quantity'] = min(99, $newQty);
                $_SESSION['flash_success'] = 'Đã cập nhật số lượng sản phẩm!';
            } else {
                // Nếu giảm về 0 thì tự động xóa món hàng đó khỏi giỏ
                unset($_SESSION['cart'][$cartKey]);
                $_SESSION['flash_success'] = 'Đã xóa sản phẩm khỏi giỏ hàng!';
            }
        }

        header('Location: ' . BASE_URL . '?action=cart');
        exit;
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng.
     */
    public function removeFromCart()
    {
        $cartKey = trim($_GET['key'] ?? '');
        if ($cartKey !== '' && isset($_SESSION['cart'][$cartKey])) {
            unset($_SESSION['cart'][$cartKey]);
            $_SESSION['flash_success'] = 'Đã xóa sản phẩm khỏi giỏ hàng!';
        }

        header('Location: ' . BASE_URL . '?action=cart');
        exit;
    }

    /**
     * Xóa toàn bộ sản phẩm khỏi giỏ hàng.
     */
    public function clearCart()
    {
        if (!empty($_SESSION['cart'])) {
            unset($_SESSION['cart']);
            $_SESSION['flash_success'] = 'Đã xóa toàn bộ sản phẩm khỏi giỏ hàng!';
        }

        header('Location: ' . BASE_URL . '?action=cart');
        exit;
    }

    /**
     * Giao diện thanh toán & đặt hàng (Checkout).
     * Kiểm tra giỏ hàng không được để trống khi đặt hàng.
     */
    public function checkout()
    {
        $this->requireAuthenticatedUser('checkout');

        $cart = $_SESSION['cart'] ?? [];

        // Kiểm tra validation giỏ hàng: không được để trống khi đặt hàng
        if (empty($cart)) {
            $_SESSION['flash_error'] = 'Giỏ hàng của bạn đang trống! Vui lòng chọn sản phẩm trước khi tiến hành đặt hàng.';
            header('Location: ' . BASE_URL . '?action=cart');
            exit;
        }

        $csrfToken = $this->csrfToken();

        $title = 'Thanh toán & Đặt hàng | SPORTIX';
        $view  = 'client/checkout';

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Xử lý xác nhận đặt hàng.
     * Kiểm tra giỏ hàng không được để trống và lưu đơn hàng vào CSDL.
     */
    public function placeOrder()
    {
        $authenticatedUser = $this->requireAuthenticatedUser('checkout');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?action=cart');
            exit;
        }

        if (!$this->csrfValid()) {
            $_SESSION['flash_error'] = 'Phiên làm việc đã hết hạn, vui lòng quay lại trang thanh toán và gửi lại đơn hàng.';
            header('Location: ' . BASE_URL . '?action=checkout');
            exit;
        }

        $cart = $_SESSION['cart'] ?? [];

        // Kiểm tra giỏ hàng không được để trống khi đặt hàng
        if (empty($cart)) {
            $_SESSION['flash_error'] = 'Giỏ hàng của bạn đang trống! Không thể tiến hành đặt hàng.';
            header('Location: ' . BASE_URL . '?action=cart');
            exit;
        }

        $phoneInput      = $_POST['phone'] ?? '';
        $receiverName    = trim($_POST['receiver_name'] ?? '');
        $phone           = is_string($phoneInput) ? $phoneInput : '';
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        $paymentMethod   = trim($_POST['payment_method'] ?? 'cod');
        $note            = trim($_POST['note'] ?? '');

        // Validation thông tin người nhận
        $errors = [];
        if ($receiverName === '') {
            $errors[] = 'Vui lòng nhập họ và tên người nhận hàng.';
        }

        if (!is_valid_phone($phoneInput)) {
            $errors[] = 'Số điện thoại phải có đúng 10 chữ số, không chứa chữ cái, khoảng trắng hoặc ký tự đặc biệt.';
        }

        if ($shippingAddress === '' || mb_strlen($shippingAddress) < 6) {
            $errors[] = 'Địa chỉ nhận hàng quá ngắn. Vui lòng nhập chi tiết số nhà, tên đường, phường/xã, quận/huyện.';
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            header('Location: ' . BASE_URL . '?action=checkout');
            exit;
        }

        // Tính toán tổng tiền
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += ((float) ($item['price'] ?? 0)) * ((int) ($item['quantity'] ?? 1));
        }

        $shippingFee = ($subtotal > 0 && $subtotal < 500000) ? 30000 : 0;
        $totalAmount = $subtotal + $shippingFee;

        // Chuẩn bị địa chỉ kèm ghi chú nếu có
        $fullAddress = $shippingAddress;
        if ($note !== '') {
            $fullAddress .= ' [Ghi chú: ' . $note . ']';
        }

        $userId = (int) $authenticatedUser['id'];

        $orderData = [
            'user_id'          => $userId,
            'receiver_name'    => $receiverName,
            'phone'            => $phone,
            'shipping_address' => $fullAddress,
            'total_amount'     => $totalAmount,
            'payment_method'   => $paymentMethod,
        ];

        $orderModel = new OrderModel();
        $orderId = $orderModel->createOrder($orderData, $cart);

        if ($orderId > 0) {
            // Tự động xóa sạch giỏ hàng trong session sau khi hệ thống tạo đơn thành công
            unset($_SESSION['cart']);

            header('Location: ' . BASE_URL . '?action=order-success&id=' . $orderId);
            exit;
        }

        $_SESSION['flash_error'] = 'Có lỗi xảy ra khi tự động tạo đơn hàng, vui lòng thử lại sau ít phút.';
        header('Location: ' . BASE_URL . '?action=checkout');
        exit;
    }

    /**
     * Trang thông báo xác nhận đặt hàng thành công (Order Success).
     * Hiển thị biên nhận chi tiết ngay sau khi hệ thống tự động tạo đơn.
     */
    public function orderSuccess()
    {
        $authenticatedUser = $this->requireAuthenticatedUser('', 'Vui lòng đăng nhập để xem thông tin đơn hàng.');

        $orderId = (int) ($_GET['id'] ?? 0);
        if ($orderId <= 0) {
            header('Location: ' . BASE_URL . '?action=order-history');
            exit;
        }

        $orderModel = new OrderModel();
        $order = $orderModel->getById($orderId, (int) $authenticatedUser['id']);

        if (!$order) {
            $_SESSION['flash_error'] = 'Không tìm thấy đơn hàng hợp lệ cho tài khoản của bạn.';
            header('Location: ' . BASE_URL . '?action=order-history');
            exit;
        }

        $items = $orderModel->getOrderItems($orderId);

        $title = 'Đặt hàng thành công #' . $orderId . ' | SPORTIX';
        $view  = 'client/order-success';

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Xem lịch sử đơn hàng của khách hàng.
     */
    public function orderHistory()
    {
        $currentUser = $this->requireAuthenticatedUser('', 'Vui lòng đăng nhập để xem lịch sử đơn hàng.');

        $orderModel = new OrderModel();
        $orders = $orderModel->getOrdersByUserId((int) $currentUser['id']);

        // Bổ sung danh sách sản phẩm tóm tắt cho từng đơn hàng
        foreach ($orders as &$ord) {
            $ordId = (int) ($ord['order_id'] ?? 0);
            $ord['items'] = $orderModel->getOrderItems($ordId);
        }
        unset($ord);

        $title = 'Lịch sử đơn hàng | SPORTIX';
        $view  = 'client/order-history';

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Xem chi tiết đơn hàng của khách hàng.
     */
    public function orderDetailCustomer()
    {
        $authenticatedUser = $this->requireAuthenticatedUser('', 'Vui lòng đăng nhập để xem chi tiết đơn hàng.');

        $orderId = (int) ($_GET['id'] ?? 0);
        if ($orderId <= 0) {
            header('Location: ' . BASE_URL . '?action=order-history');
            exit;
        }

        $orderModel = new OrderModel();
        $order = $orderModel->getById($orderId, (int) $authenticatedUser['id']);

        if (!$order) {
            $_SESSION['flash_error'] = 'Không tìm thấy đơn hàng #' . $orderId;
            header('Location: ' . BASE_URL . '?action=order-history');
            exit;
        }

        $items = $orderModel->getOrderItems($orderId);
        $csrfToken = $this->csrfToken();

        $title = 'Chi tiết đơn hàng #' . $orderId . ' | SPORTIX';
        $view  = 'client/order-detail-customer';

        require_once PATH_VIEW_MAIN;
    }

    /**
     * Khách hàng gửi yêu cầu hoàn hàng.
     */
    public function requestReturn()
    {
        $authenticatedUser = $this->requireAuthenticatedUser('', 'Vui lòng đăng nhập để thực hiện chức năng này.');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '?action=order-history');
            exit;
        }

        if (!$this->csrfValid()) {
            $_SESSION['flash_error'] = 'Phiên làm việc đã hết hạn. Vui lòng gửi lại yêu cầu.';
            header('Location: ' . BASE_URL . '?action=order-history');
            exit;
        }

        $orderId = (int) ($_POST['order_id'] ?? 0);
        $presetReason = trim($_POST['preset_reason'] ?? '');
        $detailReason = trim($_POST['detail_reason'] ?? '');

        if ($orderId <= 0) {
            $_SESSION['flash_error'] = 'Mã đơn hàng không hợp lệ.';
            header('Location: ' . BASE_URL . '?action=order-history');
            exit;
        }

        $fullReason = $presetReason;
        if ($detailReason !== '') {
            $fullReason .= ($fullReason !== '' ? ' - ' : '') . $detailReason;
        }

        if (trim($fullReason) === '') {
            $_SESSION['flash_error'] = 'Vui lòng chọn hoặc điền lý do hoàn hàng.';
            header('Location: ' . BASE_URL . '?action=order-detail&id=' . $orderId);
            exit;
        }

        $orderModel = new OrderModel();
        $success = $orderModel->requestReturn($orderId, (int) $authenticatedUser['id'], $fullReason);

        if ($success) {
            $_SESSION['flash_success'] = 'Đã gửi yêu cầu hoàn hàng cho đơn #' . $orderId . '! Bộ phận CSKH SPORTIX sẽ liên hệ hỗ trợ bạn sớm nhất.';
        } else {
            $_SESSION['flash_error'] = 'Không thể gửi yêu cầu hoàn hàng. Đơn hàng không ở trạng thái giao thành công hoặc đã được yêu cầu trước đó.';
        }

        header('Location: ' . BASE_URL . '?action=order-detail&id=' . $orderId);
        exit;
    }

    public function notFound()
    {
        header("Location: " . BASE_URL);
        exit;
    }
}
