<?php

/**
 * AdminController - Khu vực quản trị của SPORTIX.
 *
 * Mọi phương thức đều gọi requireAdmin() trước khi hiển thị nội dung,
 * nhờ đó các route Admin được bảo vệ ở phía server (không phụ thuộc vào View).
 */
class AdminController extends BaseController
{
    private function parseVariantStockInput()
    {
        $colors = $_POST['variant_color'] ?? [];
        $sizes  = $_POST['variant_size'] ?? [];
        $stocks = $_POST['variant_stock'] ?? [];

        if (!is_array($colors) || !is_array($sizes) || !is_array($stocks)) {
            return [[], 'Dữ liệu ma trận tồn kho không hợp lệ.'];
        }

        $rows  = [];
        $count = min(count($colors), count($sizes), count($stocks));
        for ($i = 0; $i < $count; $i++) {
            $color = normalize_variant_value($colors[$i] ?? '');
            $size  = normalize_variant_value($sizes[$i] ?? '');
            $stock = trim((string) ($stocks[$i] ?? ''));

            if ($color === '' || $size === '') {
                return [[], 'Màu và size của biến thể không được để trống.'];
            }
            if ($stock === '') {
                $stock = '0';
            }
            if (preg_match('/^\d+$/', $stock) !== 1) {
                return [[], 'Tồn kho của biến thể ' . $color . ' - ' . $size . ' phải là số nguyên không âm.'];
            }

            $key = $color . '|' . $size;
            if (isset($rows[$key])) {
                return [[], 'Tổ hợp ' . $color . ' - ' . $size . ' bị lặp. Vui lòng kiểm tra lại ma trận tồn kho.'];
            }

            $rows[$key] = [
                'color' => $color,
                'size'  => $size,
                'stock' => (int) $stock,
            ];
        }

        return [array_values($rows), null];
    }

    private function buildVariantStockMap(array $rows)
    {
        $map = [];
        foreach ($rows as $row) {
            $map[normalize_variant_value($row['color'] ?? '') . '|' . normalize_variant_value($row['size'] ?? '')] = (int) ($row['stock'] ?? 0);
        }

        return $map;
    }

    private function buildVariantStockInputMap()
    {
        $colors = $_POST['variant_color'] ?? [];
        $sizes  = $_POST['variant_size'] ?? [];
        $stocks = $_POST['variant_stock'] ?? [];

        if (!is_array($colors) || !is_array($sizes) || !is_array($stocks)) {
            return [];
        }

        $map   = [];
        $count = min(count($colors), count($sizes), count($stocks));
        for ($i = 0; $i < $count; $i++) {
            $color = normalize_variant_value($colors[$i] ?? '');
            $size  = normalize_variant_value($sizes[$i] ?? '');
            if ($color === '' || $size === '') {
                continue;
            }
            $map[$color . '|' . $size] = trim((string) ($stocks[$i] ?? ''));
        }

        return $map;
    }

    private function getSessionVariantKeys($productId)
    {
        $keys  = [];
        $groups = [
            $_SESSION['cart'] ?? [],
            $_SESSION['checkout_items'] ?? [],
        ];

        foreach ($groups as $items) {
            if (!is_array($items)) {
                continue;
            }
            foreach ($items as $item) {
                if (!is_array($item)) {
                    if (!is_scalar($item) || !isset($_SESSION['cart'][$item])) {
                        continue;
                    }
                    $item = $_SESSION['cart'][$item];
                }
                if (!is_array($item) || (int) ($item['product_id'] ?? 0) !== (int) $productId) {
                    continue;
                }
                $key = normalize_variant_value($item['color'] ?? '') . '|' . normalize_variant_value($item['size'] ?? '');
                if ($key !== '|') {
                    $keys[$key] = $key;
                }
            }
        }

        return array_values($keys);
    }

    /**
     * Trang Dashboard quản trị.
     */
    public function dashboard()
    {
        $categoryModel = new CategoryModel();
        $productModel  = new ProductModel();
        $userModel     = new UserModel();
        $orderModel    = new OrderModel();

        $totalCategories = count($categoryModel->getAll());
        $totalProducts   = $productModel->countAll();
        $totalUser       = $userModel->countAll();
        $totalOrders     = $orderModel->countAll();

        $this->renderAdminPage(
            'dashboard',
            'Dashboard | Quản trị SPORTIX',
            'Dashboard',
            'Tổng quan hoạt động của cửa hàng SPORTIX.',
            'dashboard',
            [
                'totalCategories' => $totalCategories,
                'totalProducts'   => $totalProducts,
                'totalUser'       => $totalUser,
                'totalOrders'     => $totalOrders,
            ]
        );
    }

    /* =========================================================
       QUẢN LÝ DANH MỤC SẢN PHẨM (CRUD)
       ========================================================= */

    /**
     * Danh sách danh mục sản phẩm.
     */
    public function categories()
    {
        $categoryModel = new CategoryModel();
        $keyword = trim($_GET['keyword'] ?? '');

        $categories   = $categoryModel->getAllWithCount($keyword);
        $flashSuccess = $this->getFlash('success');
        $flashError   = $this->getFlash('error');

        $this->renderAdminPage(
            'categories/index',
            'Quản lý danh mục | Quản trị SPORTIX',
            'Quản lý danh mục',
            'Danh sách danh mục sản phẩm đang kinh doanh tại SPORTIX.',
            'categories',
            [
                'categories'   => $categories,
                'keyword'      => $keyword,
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
            ]
        );
    }

    /**
     * Thêm mới danh mục sản phẩm (GET: hiển thị form, POST: xử lý lưu).
     */
    public function categoryCreate()
    {
        $categoryModel = new CategoryModel();
        $errors = [];
        $old = [
            'categories_name' => '',
            'description'     => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['categories_name'] = trim($_POST['categories_name'] ?? '');
            $old['description']     = trim($_POST['description'] ?? '');

            // 1. Kiểm tra CSRF
            if (!$this->csrfValid()) {
                $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
            }

            // 2. Validate tên danh mục
            if ($old['categories_name'] === '') {
                $errors['categories_name'] = 'Vui lòng nhập tên danh mục.';
            } elseif (mb_strlen($old['categories_name']) > 100) {
                $errors['categories_name'] = 'Tên danh mục không được vượt quá 100 ký tự.';
            } elseif ($categoryModel->findByName($old['categories_name'])) {
                $errors['categories_name'] = 'Tên danh mục này đã tồn tại, vui lòng chọn tên khác.';
            }

            // Nếu không có lỗi, tiến hành lưu
            if (empty($errors)) {
                $newId = $categoryModel->create([
                    'categories_name' => $old['categories_name'],
                    'description'     => $old['description'],
                ]);

                if ($newId !== false) {
                    $this->setFlash('success', 'Thêm danh mục "' . htmlspecialchars($old['categories_name']) . '" thành công!');
                    $this->redirect('?action=admin-categories');
                } else {
                    $errors['general'] = 'Không thể lưu danh mục vào cơ sở dữ liệu. Vui lòng thử lại sau.';
                }
            }
        }

        $csrfToken  = $this->csrfToken();
        $flashError = $this->getFlash('error');

        $this->renderAdminPage(
            'categories/form',
            'Thêm danh mục mới | Quản trị SPORTIX',
            'Thêm danh mục mới',
            'Tạo mới phân loại danh mục sản phẩm cho cửa hàng.',
            'categories',
            [
                'isEdit'     => false,
                'category'   => null,
                'old'        => $old,
                'errors'     => $errors,
                'csrfToken'  => $csrfToken,
                'flashError' => $flashError,
            ]
        );
    }

    /**
     * Sửa thông tin danh mục sản phẩm (GET: hiển thị form, POST: xử lý cập nhật).
     */
    public function categoryEdit()
    {
        $categoryModel = new CategoryModel();
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Mã danh mục không hợp lệ.');
            $this->redirect('?action=admin-categories');
        }

        $category = $categoryModel->getById($id);
        if (!$category) {
            $this->setFlash('error', 'Không tìm thấy danh mục yêu cầu.');
            $this->redirect('?action=admin-categories');
        }

        $errors = [];
        $old = [
            'categories_name' => $category['categories_name'] ?? '',
            'description'     => $category['description'] ?? '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['categories_name'] = trim($_POST['categories_name'] ?? '');
            $old['description']     = trim($_POST['description'] ?? '');

            // 1. Kiểm tra CSRF
            if (!$this->csrfValid()) {
                $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng thử lại.';
            }

            // 2. Validate tên danh mục
            if ($old['categories_name'] === '') {
                $errors['categories_name'] = 'Vui lòng nhập tên danh mục.';
            } elseif (mb_strlen($old['categories_name']) > 100) {
                $errors['categories_name'] = 'Tên danh mục không được vượt quá 100 ký tự.';
            } else {
                // Kiểm tra trùng tên loại trừ chính danh mục đang sửa
                $existing = $categoryModel->findByName($old['categories_name'], $id);
                if ($existing) {
                    $errors['categories_name'] = 'Tên danh mục này đã tồn tại, vui lòng chọn tên khác.';
                }
            }

            // Nếu không có lỗi, tiến hành cập nhật
            if (empty($errors)) {
                $updated = $categoryModel->update($id, [
                    'categories_name' => $old['categories_name'],
                    'description'     => $old['description'],
                ]);

                if ($updated !== false) {
                    $this->setFlash('success', 'Cập nhật danh mục #' . $id . ' thành công!');
                    $this->redirect('?action=admin-categories');
                } else {
                    $errors['general'] = 'Không thể cập nhật danh mục. Vui lòng thử lại.';
                }
            }
        }

        $csrfToken  = $this->csrfToken();
        $flashError = $this->getFlash('error');

        $this->renderAdminPage(
            'categories/form',
            'Chỉnh sửa danh mục #' . $id . ' | Quản trị SPORTIX',
            'Chỉnh sửa danh mục',
            'Cập nhật thông tin phân loại: ' . htmlspecialchars($category['categories_name'] ?? ''),
            'categories',
            [
                'isEdit'     => true,
                'category'   => $category,
                'old'        => $old,
                'errors'     => $errors,
                'csrfToken'  => $csrfToken,
                'flashError' => $flashError,
            ]
        );
    }

    /**
     * Xóa danh mục sản phẩm.
     */
    public function categoryDelete()
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Mã danh mục không hợp lệ.');
            $this->redirect('?action=admin-categories');
        }

        $categoryModel = new CategoryModel();
        $category = $categoryModel->getById($id);

        if (!$category) {
            $this->setFlash('error', 'Danh mục không tồn tại hoặc đã bị xóa.');
            $this->redirect('?action=admin-categories');
        }

        // Kiểm tra ràng buộc khóa ngoại: nếu đang có sản phẩm thuộc danh mục thì chặn xóa
        $productCount = $categoryModel->countProducts($id);
        if ($productCount > 0) {
            $this->setFlash('error', 'Không thể xóa danh mục "' . htmlspecialchars($category['categories_name']) . '" vì đang có ' . $productCount . ' sản phẩm liên kết!');
            $this->redirect('?action=admin-categories');
        }

        // Thực hiện xóa
        $deleted = $categoryModel->delete($id);
        if ($deleted) {
            $this->setFlash('success', 'Đã xóa danh mục "' . htmlspecialchars($category['categories_name']) . '" thành công!');
        } else {
            $this->setFlash('error', 'Không thể xóa danh mục do lỗi hệ thống hoặc ràng buộc dữ liệu.');
        }

        $this->redirect('?action=admin-categories');
    }

    /* =========================================================
       QUẢN LÝ SẢN PHẨM (CRUD & QUẢN LÝ ẢNH)
       ========================================================= */

    /**
     * Danh sách sản phẩm (hỗ trợ tìm kiếm từ khóa và lọc theo danh mục).
     */
    public function products()
    {
        $productModel  = new ProductModel();
        $categoryModel = new CategoryModel();
        $variantModel  = new ProductVariantModel();

        $keyword    = trim($_GET['keyword'] ?? '');
        $categoryId = (int) ($_GET['category_id'] ?? 0);
        $products   = [];
        $categories = [];
        $loadError  = null;

        try {
            $products   = $productModel->getAllAdmin($keyword, $categoryId);
            $categories = $categoryModel->getAll();
        } catch (Throwable $e) {
            $loadError = 'Không thể tải danh sách sản phẩm từ cơ sở dữ liệu. Vui lòng thử lại sau.';
        }

        $variantStockTotals = $variantModel->getStockTotals();

        $flashSuccess = $this->getFlash('success');
        $flashError   = $this->getFlash('error');

        $this->renderAdminPage(
            'products/index',
            'Quản lý sản phẩm | Quản trị SPORTIX',
            'Quản lý sản phẩm',
            'Danh sách sản phẩm thể thao đang kinh doanh tại SPORTIX.',
            'products',
            [
                'products'           => $products,
                'categories'         => $categories,
                'variantStockTotals' => $variantStockTotals,
                'keyword'            => $keyword,
                'categoryId'         => $categoryId,
                'loadError'          => $loadError,
                'flashSuccess'       => $flashSuccess,
                'flashError'         => $flashError,
            ]
        );
    }

    public function productDetail()
    {
        $productModel = new ProductModel();
        $variantModel = new ProductVariantModel();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->setFlash('error', 'Mã sản phẩm không hợp lệ.');
            $this->redirect('?action=admin-products');
        }

        $product = $productModel->getById($id);
        if (!$product) {
            $this->setFlash('error', 'Không tìm thấy sản phẩm yêu cầu hoặc đã bị xóa.');
            $this->redirect('?action=admin-products');
        }

        $variants   = $variantModel->getByProduct($id);
        $totalStock = 0;
        foreach ($variants as $variant) {
            $totalStock += (int) ($variant['stock_quantity'] ?? 0);
        }

        $this->renderAdminPage(
            'products/detail',
            'Chi tiết sản phẩm #' . $id . ' | Quản trị SPORTIX',
            'Chi tiết sản phẩm #' . $id,
            'Xem thông tin sản phẩm và tồn kho theo từng biến thể.',
            'products',
            [
                'product'      => $product,
                'variants'     => $variants,
                'totalStock'   => $totalStock,
                'flashSuccess' => $this->getFlash('success'),
                'flashError'   => $this->getFlash('error'),
            ]
        );
    }

    /**
     * Thêm mới sản phẩm (GET: hiển thị form, POST: xử lý lưu kèm upload ảnh).
     */
    public function productCreate()
    {
        $productModel  = new ProductModel();
        $categoryModel = new CategoryModel();
        $categories    = $categoryModel->getAll();

        $errors = [];
        $old = [
            'product_name' => '',
            'category_id'  => 0,
            'price'        => '',
            'size'         => '',
            'size_type'    => '',
            'size_values'  => [],
            'color'        => '',
            'description'  => '',
            'image_url'    => '',
        ];
        $variantRows   = [];
        $variantStocks = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['product_name'] = trim($_POST['product_name'] ?? '');
            $old['category_id']  = (int) ($_POST['category_id'] ?? 0);
            $old['price']        = trim($_POST['price'] ?? '');
            $old['size']         = trim($_POST['size'] ?? '');
            $old['size_type']    = trim($_POST['size_type'] ?? '');
            $sizeValues          = $_POST['size_values'] ?? [];
            $old['size_values']  = is_array($sizeValues) ? $sizeValues : [];
            $colors              = parse_product_color_values($_POST['color'] ?? '');
            $old['color']        = implode(',', $colors);
            $old['description']  = trim($_POST['description'] ?? '');
            $old['image_url']    = trim($_POST['image_url'] ?? '');

            [$variantRows, $variantError] = $this->parseVariantStockInput();
            $variantStocks = $this->buildVariantStockInputMap();

            if (!$this->csrfValid()) {
                $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            }

            if ($old['product_name'] === '') {
                $errors['product_name'] = 'Vui lòng nhập tên sản phẩm.';
            } elseif (mb_strlen($old['product_name']) > 200) {
                $errors['product_name'] = 'Tên sản phẩm không được vượt quá 200 ký tự.';
            }

            if ($old['category_id'] <= 0) {
                $errors['category_id'] = 'Vui lòng chọn danh mục cho sản phẩm.';
            }

            if ($old['price'] === '') {
                $errors['price'] = 'Vui lòng nhập đơn giá sản phẩm.';
            } elseif (!is_numeric($old['price']) || (float) $old['price'] < 0) {
                $errors['price'] = 'Giá bán phải là số hợp lệ và lớn hơn hoặc bằng 0.';
            }

            if (empty($colors)) {
                $errors['color'] = 'Vui lòng nhập ít nhất một màu cho sản phẩm.';
            }

            if ($variantError !== null) {
                $errors['variant'] = $variantError;
            } elseif (empty($variantRows)) {
                $errors['variant'] = 'Vui lòng nhập ít nhất một tổ hợp màu - size và tồn kho tương ứng.';
            }

            $finalSize = null;
            if ($old['size_type'] === '' || $old['size_type'] === 'legacy') {
                $errors['size'] = 'Vui lòng chọn loại kích thước.';
            } else {
                $sizeResult = normalize_product_size_selection($old['size_type'], $old['size_values']);
                if ($sizeResult['error'] !== null) {
                    $errors['size'] = $sizeResult['error'];
                } else {
                    $finalSize = $sizeResult['value'];
                }
            }

            $finalImage = null;
            $imgError   = null;
            $uploadItem = $_FILES['image_file'] ?? $_FILES['image'] ?? null;

            if ($uploadItem && isset($uploadItem['error']) && $uploadItem['error'] !== UPLOAD_ERR_NO_FILE) {
                if (!is_valid_image_upload($uploadItem, $imgError)) {
                    $errors['image'] = $imgError;
                } else {
                    try {
                        $finalImage = upload_file('products', $uploadItem);
                    } catch (Exception $e) {
                        $errors['image'] = $e->getMessage();
                    }
                }
            } elseif ($old['image_url'] !== '') {
                $finalImage = $old['image_url'];
            }

            if (empty($errors)) {
                $pdo = $productModel->getConnection();

                try {
                    $pdo->beginTransaction();

                    $newId = $productModel->create([
                        'product_name' => $old['product_name'],
                        'category_id'  => $old['category_id'],
                        'price'        => (float) $old['price'],
                        'size'         => $finalSize,
                        'color'        => $old['color'] !== '' ? $old['color'] : null,
                        'description'  => $old['description'] !== '' ? $old['description'] : null,
                        'image'        => $finalImage,
                    ]);
                    if ($newId === false) {
                        throw new RuntimeException('Không thể lưu sản phẩm vào cơ sở dữ liệu.');
                    }

                    $variantModel = new ProductVariantModel($pdo);
                    $variantModel->syncForProduct($newId, $variantRows, []);

                    $pdo->commit();

                    $this->setFlash('success', 'Thêm sản phẩm "' . htmlspecialchars($old['product_name']) . '" thành công!');
                    $this->redirect('?action=admin-products');
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    if (!empty($finalImage)) {
                        delete_upload_file($finalImage);
                    }
                    $errors['general'] = $e->getMessage() !== '' ? $e->getMessage() : 'Không thể lưu sản phẩm vào cơ sở dữ liệu. Vui lòng thử lại sau.';
                }
            }
        }

        $csrfToken  = $this->csrfToken();
        $flashError = $this->getFlash('error');

        $this->renderAdminPage(
            'products/form',
            'Thêm sản phẩm mới | Quản trị SPORTIX',
            'Thêm sản phẩm mới',
            'Nhập thông tin sản phẩm và tải ảnh lên hệ thống.',
            'products',
            [
                'isEdit'        => false,
                'product'       => null,
                'categories'    => $categories,
                'old'           => $old,
                'variantStocks' => $variantStocks,
                'errors'        => $errors,
                'csrfToken'     => $csrfToken,
                'flashError'    => $flashError,
            ]
        );
    }

    /**
     * Chỉnh sửa và cập nhật sản phẩm (hỗ trợ quản lý, thay đổi và xóa ảnh).
     */
    public function productEdit()
    {
        $productModel  = new ProductModel();
        $categoryModel = new CategoryModel();
        $variantModel  = new ProductVariantModel();
        $categories    = $categoryModel->getAll();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->setFlash('error', 'Mã sản phẩm không hợp lệ.');
            $this->redirect('?action=admin-products');
        }

        $product = $productModel->getById($id);
        if (!$product) {
            $this->setFlash('error', 'Không tìm thấy sản phẩm yêu cầu hoặc đã bị xóa.');
            $this->redirect('?action=admin-products');
        }

        $existingVariants = $variantModel->getByProduct($id);
        $variantRows      = [];
        foreach ($existingVariants as $variant) {
            $variantRows[] = [
                'color' => $variant['color'],
                'size'  => $variant['size'],
                'stock' => (int) $variant['stock_quantity'],
            ];
        }
        $variantStocks = $this->buildVariantStockMap($variantRows);

        $existingSize     = (string) ($product['size'] ?? '');
        $existingSizeType = get_product_size_type($existingSize);

        $errors = [];
        $old = [
            'product_name' => $product['product_name'] ?? '',
            'category_id'  => (int) ($product['category_id'] ?? 0),
            'price'        => $product['price'] ?? '',
            'size'         => $existingSize,
            'size_type'    => $existingSizeType !== '' ? $existingSizeType : 'legacy',
            'size_values'  => parse_product_size_values($existingSize),
            'color'        => $product['color'] ?? '',
            'description'  => $product['description'] ?? '',
            'image_url'    => (isset($product['image']) && (str_starts_with($product['image'], 'http://') || str_starts_with($product['image'], 'https://'))) ? $product['image'] : '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['product_name'] = trim($_POST['product_name'] ?? '');
            $old['category_id']  = (int) ($_POST['category_id'] ?? 0);
            $old['price']        = trim($_POST['price'] ?? '');
            $old['size']         = trim($_POST['size'] ?? '');
            $old['size_type']    = trim($_POST['size_type'] ?? '');
            $sizeValues          = $_POST['size_values'] ?? [];
            $old['size_values']  = is_array($sizeValues) ? $sizeValues : [];
            $colors              = parse_product_color_values($_POST['color'] ?? '');
            $old['color']        = implode(',', $colors);
            $old['description']  = trim($_POST['description'] ?? '');
            $old['image_url']    = trim($_POST['image_url'] ?? '');
            $deleteImage         = !empty($_POST['delete_image']);

            [$variantRows, $variantError] = $this->parseVariantStockInput();
            $variantStocks = array_replace($variantStocks, $this->buildVariantStockInputMap());

            if (!$this->csrfValid()) {
                $errors['general'] = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.';
            }

            if ($old['product_name'] === '') {
                $errors['product_name'] = 'Vui lòng nhập tên sản phẩm.';
            } elseif (mb_strlen($old['product_name']) > 200) {
                $errors['product_name'] = 'Tên sản phẩm không được vượt quá 200 ký tự.';
            }

            if ($old['category_id'] <= 0) {
                $errors['category_id'] = 'Vui lòng chọn danh mục hợp lệ.';
            }

            if ($old['price'] === '') {
                $errors['price'] = 'Vui lòng nhập đơn giá sản phẩm.';
            } elseif (!is_numeric($old['price']) || (float) $old['price'] < 0) {
                $errors['price'] = 'Giá bán phải là số hợp lệ và lớn hơn hoặc bằng 0.';
            }

            if (empty($colors)) {
                $errors['color'] = 'Vui lòng nhập ít nhất một màu cho sản phẩm.';
            }

            if ($variantError !== null) {
                $errors['variant'] = $variantError;
            } elseif (empty($variantRows)) {
                $errors['variant'] = 'Vui lòng nhập ít nhất một tổ hợp màu - size và tồn kho tương ứng.';
            }

            $finalSize = null;
            if ($old['size_type'] === '') {
                $errors['size'] = 'Vui lòng chọn loại kích thước.';
            } else {
                $sizeResult = normalize_product_size_selection($old['size_type'], $old['size_values'], $existingSize);
                if ($sizeResult['error'] !== null) {
                    $errors['size'] = $sizeResult['error'];
                } else {
                    $finalSize = $sizeResult['value'];
                }
            }

            $finalImage = $product['image'] ?? null;
            $newUploadedFile = null;
            $shouldDeleteOldFile = false;
            $uploadItem = $_FILES['image_file'] ?? $_FILES['image'] ?? null;

            if ($deleteImage) {
                $finalImage = null;
                $shouldDeleteOldFile = true;
            } elseif ($uploadItem && isset($uploadItem['error']) && $uploadItem['error'] !== UPLOAD_ERR_NO_FILE) {
                $imgError = null;
                if (!is_valid_image_upload($uploadItem, $imgError)) {
                    $errors['image'] = $imgError;
                } else {
                    try {
                        $newUploadedFile = upload_file('products', $uploadItem);
                        $finalImage = $newUploadedFile;
                        $shouldDeleteOldFile = true;
                    } catch (Exception $e) {
                        $errors['image'] = $e->getMessage();
                    }
                }
            } elseif ($old['image_url'] !== '' && $old['image_url'] !== ($product['image'] ?? '')) {
                $finalImage = $old['image_url'];
                $shouldDeleteOldFile = true;
            }

            if (empty($errors)) {
                $pdo = $productModel->getConnection();

                try {
                    $pdo->beginTransaction();

                    $updated = $productModel->update($id, [
                        'product_name' => $old['product_name'],
                        'category_id'  => $old['category_id'],
                        'price'        => (float) $old['price'],
                        'size'         => $finalSize,
                        'color'        => $old['color'] !== '' ? $old['color'] : null,
                        'description'  => $old['description'] !== '' ? $old['description'] : null,
                        'image'        => $finalImage,
                    ]);
                    if ($updated === false) {
                        throw new RuntimeException('Không thể cập nhật sản phẩm vào cơ sở dữ liệu.');
                    }

                    $variantModel = new ProductVariantModel($pdo);
                    $variantModel->syncForProduct($id, $variantRows, $this->getSessionVariantKeys($id));

                    $pdo->commit();

                    if ($shouldDeleteOldFile && !empty($product['image'])) {
                        delete_upload_file($product['image']);
                    }

                    $this->setFlash('success', 'Cập nhật sản phẩm #' . $id . ' thành công!');
                    $this->redirect('?action=admin-products');
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    if ($newUploadedFile) {
                        delete_upload_file($newUploadedFile);
                    }
                    $errors['general'] = $e->getMessage() !== '' ? $e->getMessage() : 'Không thể cập nhật sản phẩm vào cơ sở dữ liệu. Vui lòng thử lại.';
                }
            }
        }

        $csrfToken  = $this->csrfToken();
        $flashError = $this->getFlash('error');

        $this->renderAdminPage(
            'products/form',
            'Chỉnh sửa sản phẩm #' . $id . ' | Quản trị SPORTIX',
            'Chỉnh sửa sản phẩm',
            'Cập nhật thông tin, giá bán và hình ảnh: ' . htmlspecialchars($product['product_name'] ?? ''),
            'products',
            [
                'isEdit'        => true,
                'product'       => $product,
                'categories'    => $categories,
                'old'           => $old,
                'variantStocks' => $variantStocks,
                'errors'        => $errors,
                'csrfToken'     => $csrfToken,
                'flashError'    => $flashError,
            ]
        );
    }

    /**
     * Xóa sản phẩm (kiểm tra đơn hàng liên kết và tự động xóa ảnh trên đĩa).
     */
    public function productDelete()
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Mã sản phẩm không hợp lệ.');
            $this->redirect('?action=admin-products');
        }

        $productModel = new ProductModel();
        $product = $productModel->getById($id);

        if (!$product) {
            $this->setFlash('error', 'Sản phẩm không tồn tại hoặc đã bị xóa trước đó.');
            $this->redirect('?action=admin-products');
        }

        // Kiểm tra ràng buộc khóa ngoại với đơn hàng
        $orderCount = $productModel->countInOrders($id);
        if ($orderCount > 0) {
            $this->setFlash('error', 'Không thể xóa sản phẩm "' . htmlspecialchars($product['product_name']) . '" vì đang nằm trong ' . $orderCount . ' đơn hàng của khách hàng!');
            $this->redirect('?action=admin-products');
        }

        // Thực hiện xóa sản phẩm
        $deleted = $productModel->delete($id);
        if ($deleted) {
            // Tự động xóa file ảnh vật lý trên máy chủ nếu là ảnh upload nội bộ
            if (!empty($product['image'])) {
                delete_upload_file($product['image']);
            }
            $this->setFlash('success', 'Đã xóa sản phẩm "' . htmlspecialchars($product['product_name']) . '" (ID: #' . $id . ') thành công!');
        } else {
            $this->setFlash('error', 'Không thể xóa sản phẩm do ràng buộc dữ liệu hoặc lỗi hệ thống.');
        }

        $this->redirect('?action=admin-products');
    }

    /**
     * Kiểm tra và lưu file hình ảnh sản phẩm vào assets/uploads/products.
     *
     * @param array $file Một phần tử trong $_FILES
     * @return string Đường dẫn tương đối lưu trong cột products.image
     * @throws Exception Khi file không hợp lệ hoặc lưu thất bại
     */
    private function storeProductImage(array $file)
    {
        return upload_file('products', $file);
    }

    /* =========================================================
       CÁC TRANG QUẢN TRỊ KHÁC
       ========================================================= */

    /**
     * Trang quản lý tài khoản người dùng.
     */
    public function users()
    {
        $userModel = new UserModel();
        $users = $userModel->getAllForAdmin();
        $flashSuccess = $this->getFlash('success');
        $flashError   = $this->getFlash('error');

        $this->renderAdminPage(
            'users/index',
            'Quản lý tài khoản | Quản trị SPORTIX',
            'Quản lý tài khoản',
            'Danh sách tài khoản đã đăng ký trên hệ thống.',
            'users',
            [
                'users'        => $users,
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
                'csrfToken'    => $this->csrfToken(),
            ]
        );
    }

    /**
     * Xác nhận và xóa tài khoản khách hàng (GET: hiển thị xác nhận, POST: thực hiện xóa).
     */
    public function userDelete()
    {
        $userModel = new UserModel();
        $id = (int) ($_REQUEST['id'] ?? 0);

        if ($id <= 0) {
            $this->setFlash('error', 'ID tài khoản không hợp lệ.');
            $this->redirect('?action=admin-users');
        }

        $targetUser = $userModel->findById($id);

        if (!$targetUser) {
            $this->setFlash('error', 'Tài khoản không tồn tại hoặc đã bị xóa.');
            $this->redirect('?action=admin-users');
        }

        // Chỉ cho phép thao tác với tài khoản khách hàng; tuyệt đối không xóa admin.
        if (($targetUser['role'] ?? '') === 'admin') {
            $this->setFlash('error', 'Không thể xóa tài khoản quản trị viên (Admin).');
            $this->redirect('?action=admin-users');
        }

        if (($targetUser['role'] ?? '') !== 'client') {
            $this->setFlash('error', 'Chỉ được phép xóa tài khoản khách hàng.');
            $this->redirect('?action=admin-users');
        }

        // Không cho phép xóa bằng GET.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfValid()) {
                $this->setFlash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
                $this->redirect('?action=admin-users');
            }

            // Kiểm tra lại dữ liệu thực tế từ database trước khi xóa.
            $currentUser = $userModel->findById($id);
            if (!$currentUser) {
                $this->setFlash('error', 'Tài khoản không tồn tại hoặc đã bị xóa.');
                $this->redirect('?action=admin-users');
            }

            if (($currentUser['role'] ?? '') === 'admin') {
                $this->setFlash('error', 'Không thể xóa tài khoản quản trị viên (Admin).');
                $this->redirect('?action=admin-users');
            }

            if (($currentUser['role'] ?? '') !== 'client') {
                $this->setFlash('error', 'Chỉ được phép xóa tài khoản khách hàng.');
                $this->redirect('?action=admin-users');
            }

            if ($userModel->hasOrders($id)) {
                $this->setFlash('error', 'Không thể xóa tài khoản này vì khách hàng đã có đơn hàng trên hệ thống.');
                $this->redirect('?action=admin-users');
            }

            $deleted = $userModel->deleteCustomerById($id);
            if ($deleted) {
                $this->setFlash('success', 'Xóa tài khoản khách hàng thành công.');
            } else {
                $this->setFlash('error', 'Không thể xóa tài khoản. Vui lòng kiểm tra lại điều kiện xóa.');
            }

            $this->redirect('?action=admin-users');
        }

        $csrfToken  = $this->csrfToken();
        $flashError = $this->getFlash('error');

        $this->renderAdminPage(
            'users/delete',
            'Xác nhận xóa tài khoản | Quản trị SPORTIX',
            'Xác nhận xóa tài khoản',
            'Kiểm tra thông tin trước khi xóa tài khoản khách hàng.',
            'users',
            [
                'targetUser' => $targetUser,
                'csrfToken'  => $csrfToken,
                'flashError' => $flashError,
            ]
        );
    }

    /**
     * Danh sách đơn hàng trong khu vực quản trị.
     */
    public function orders()
    {
        $orderModel = new OrderModel();

        $keywordInput = $_GET['keyword'] ?? '';
        $keyword = is_string($keywordInput) ? $keywordInput : '';
        $status  = trim($_GET['status'] ?? '');

        $flashSuccess = $this->getFlash('success');
        $flashError   = $this->getFlash('error');

        try {
            $orders = $orderModel->getAllAdmin($keyword, $status);
        } catch (Throwable $e) {
            $orders = [];
            $flashError = 'Không thể tải danh sách đơn hàng: ' . $e->getMessage();
        }

        $this->renderAdminPage(
            'orders/index',
            'Quản lý đơn hàng | Quản trị SPORTIX',
            'Quản lý đơn hàng',
            'Theo dõi, tra cứu và xử lý đơn hàng của khách hàng.',
            'orders',
            [
                'orders'       => $orders,
                'keyword'      => $keyword,
                'status'       => $status,
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
            ]
        );
    }

    /**
     * Xem chi tiết đơn hàng (các sản phẩm trong đơn, địa chỉ giao hàng và cập nhật trạng thái).
     */
    public function orderDetail()
    {
        $orderModel = new OrderModel();
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Mã đơn hàng không hợp lệ.');
            $this->redirect('?action=admin-orders');
        }

        $order = $orderModel->getById($id);
        if (!$order) {
            $this->setFlash('error', 'Không tìm thấy đơn hàng yêu cầu.');
            $this->redirect('?action=admin-orders');
        }

        $orderItems   = $orderModel->getOrderItems($id);
        $csrfToken    = $this->csrfToken();
        $flashSuccess = $this->getFlash('success');
        $flashError   = $this->getFlash('error');
        $nextStatus   = $orderModel->getNextDeliveryStatus((string) ($order['order_status'] ?? ''));

        $this->renderAdminPage(
            'orders/detail',
            'Chi tiết đơn hàng #' . $id . ' | Quản trị SPORTIX',
            'Chi tiết đơn hàng #' . $id,
            'Thông tin khách hàng, sản phẩm và trạng thái đơn hàng.',
            'orders',
            [
                'order'        => $order,
                'orderItems'   => $orderItems,
                'csrfToken'    => $csrfToken,
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
                'nextStatus'   => $nextStatus,
            ]
        );
    }

    /**
     * Cập nhật trạng thái đơn hàng.
     */
    public function orderUpdateStatus()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=admin-orders');
        }

        if (!$this->csrfValid()) {
            $this->setFlash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('?action=admin-orders');
        }

        $id     = (int) ($_POST['order_id'] ?? 0);
        $status = trim($_POST['order_status'] ?? '');
        $allowedStatuses = ['pending', 'processing', 'shipping', 'completed'];

        if ($id <= 0) {
            $this->setFlash('error', 'Mã đơn hàng không hợp lệ.');
            $this->redirect('?action=admin-orders');
        }

        if (!in_array($status, $allowedStatuses, true)) {
            $this->setFlash('error', 'Trạng thái cập nhật không hợp lệ cho quy trình đơn hàng thông thường.');
            $this->redirect('?action=admin-order-detail&id=' . $id);
        }

        $orderModel = new OrderModel();
        $order = $orderModel->getById($id);
        if (!$order) {
            $this->setFlash('error', 'Không tìm thấy đơn hàng #' . $id);
            $this->redirect('?action=admin-orders');
        }

        $currentStatus = (string) ($order['order_status'] ?? 'pending');
        $nextStatus    = $orderModel->getNextDeliveryStatus($currentStatus);

        if ($nextStatus === null) {
            if (in_array($currentStatus, $allowedStatuses, true)) {
                $this->setFlash('error', 'Đơn hàng #' . $id . ' đã hoàn tất giao hàng, không thể cập nhật tiếp.');
            } else {
                $this->setFlash('error', 'Đơn hàng đang ở trạng thái đặc biệt. Vui lòng sử dụng khu vực Quản lý hoàn hàng hoặc liên hệ quản trị viên.');
            }
            $this->redirect('?action=admin-order-detail&id=' . $id);
        }

        if ($status !== $nextStatus) {
            $this->setFlash('error', 'Chuyển trạng thái không hợp lệ. Chỉ được chuyển sang đúng trạng thái kế tiếp; không được nhảy cóc, lùi lại hoặc chọn lại trạng thái hiện tại.');
            $this->redirect('?action=admin-order-detail&id=' . $id);
        }

        $payMethod = (string) ($order['payment_method'] ?? 'cod');
        $payStatus = (string) ($order['payment_status'] ?? 'unpaid');

        // Quy tắc nghiệp vụ quan trọng:
        // Đơn hàng thanh toán Online (banking, momo,...): chỉ có thanh toán thành công thì mới được giao hàng.
        // Thanh toán khi nhận hàng (COD) là chuyện khác: COD thanh toán tiền mặt lúc nhận nên được giao bình thường.
        if ($payMethod !== 'cod' && $status === 'shipping' && $payStatus !== 'paid') {
            $this->setFlash('error', 'Đơn hàng thanh toán Online chưa được thanh toán thành công! Chỉ có thanh toán thành công thì mới được giao hàng. Vui lòng bấm nút "Xác nhận đã thanh toán & Bắt đầu giao hàng" bên dưới khi tiền đã về tài khoản.');
            $this->redirect('?action=admin-order-detail&id=' . $id);
        }

        $updated = $orderModel->updateStatus($id, $status);

        if ($updated) {
            // Nếu đơn hàng COD được chuyển sang hoàn thành, tiền mặt đã thu xong -> cập nhật trạng thái thanh toán thành paid
            if ($payMethod === 'cod' && $status === 'completed' && $payStatus !== 'paid') {
                $orderModel->updatePaymentStatus($id, 'paid');
            }

            $this->setFlash('success', 'Cập nhật trạng thái đơn hàng #' . $id . ' thành công!');
        } else {
            $this->setFlash('error', 'Không thể cập nhật trạng thái đơn hàng.');
        }

        $this->redirect('?action=admin-order-detail&id=' . $id);
    }

    /**
     * Admin xác nhận đã thanh toán thành công cho đơn hàng Online và chuyển sang trạng thái đang giao hàng.
     */
    public function orderConfirmPayment()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=admin-orders');
        }

        if (!$this->csrfValid()) {
            $this->setFlash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('?action=admin-orders');
        }

        $id = (int) ($_POST['order_id'] ?? 0);
        if ($id <= 0) {
            $this->setFlash('error', 'Mã đơn hàng không hợp lệ.');
            $this->redirect('?action=admin-orders');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->getById($id);
        if (!$order) {
            $this->setFlash('error', 'Không tìm thấy đơn hàng #' . $id);
            $this->redirect('?action=admin-orders');
        }

        $currentStatus = (string) ($order['order_status'] ?? '');
        $payMethod = (string) ($order['payment_method'] ?? 'cod');
        $payStatus = (string) ($order['payment_status'] ?? 'unpaid');

        if ($payMethod === 'cod' || !in_array($payStatus, ['unpaid', 'pending_confirm'], true)) {
            $this->setFlash('error', 'Đơn hàng không đủ điều kiện xác nhận thanh toán Online.');
            $this->redirect('?action=admin-order-detail&id=' . $id);
        }

        if ($currentStatus === 'pending') {
            $success = $orderModel->confirmPaymentOnly($id);
            if ($success) {
                $this->setFlash('success', 'Admin đã xác nhận nhận đủ tiền cho đơn #' . $id . ' và chuyển đơn sang Đang xử lý!');
            } else {
                $this->setFlash('error', 'Không thể xác nhận thanh toán cho đơn hàng #' . $id . '. Vui lòng thử lại.');
            }
        } elseif ($currentStatus === 'processing') {
            $success = $orderModel->confirmPaymentAndShip($id);
            if ($success) {
                $this->setFlash('success', 'Admin đã xác nhận thanh toán thành công cho đơn hàng #' . $id . ' và chuyển sang trạng thái Đang giao hàng!');
            } else {
                $this->setFlash('error', 'Không thể xác nhận thanh toán cho đơn hàng #' . $id . '. Vui lòng thử lại.');
            }
        } else {
            $this->setFlash('error', 'Chỉ có thể xác nhận thanh toán khi đơn đang ở trạng thái Chờ xác nhận hoặc Đang xử lý.');
        }

        $this->redirect('?action=admin-order-detail&id=' . $id);
    }

    public function returns()
    {
        $orderModel = new OrderModel();

        $keywordInput = $_GET['keyword'] ?? '';
        $keyword = is_string($keywordInput) ? trim($keywordInput) : '';
        $statusInput = $_GET['status'] ?? '';
        $status = is_string($statusInput) ? trim($statusInput) : '';

        if (!in_array($status, ['', 'return_request', 'return_accepted', 'return_rejected', 'returned'], true)) {
            $status = '';
        }

        $requests = [];
        $stats = ['total' => 0, 'pending' => 0, 'accepted' => 0, 'rejected' => 0];
        $flashSuccess = $this->getFlash('success');
        $flashError = $this->getFlash('error');

        try {
            $requests = $orderModel->getReturnRequests($keyword, $status);
            $stats = $orderModel->getReturnStats();
        } catch (Throwable $e) {
            $flashError = 'Không thể tải danh sách yêu cầu hoàn hàng: ' . $e->getMessage();
        }

        $this->renderAdminPage(
            'returns/index',
            'Quản lý hoàn hàng | Quản trị SPORTIX',
            'Quản lý hoàn hàng',
            'Theo dõi và xử lý các yêu cầu hoàn hàng của khách hàng.',
            'returns',
            [
                'requests'     => $requests,
                'stats'        => $stats,
                'keyword'      => $keyword,
                'status'       => $status,
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
            ]
        );
    }

    public function returnDetail()
    {
        $orderModel = new OrderModel();
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->setFlash('error', 'Mã yêu cầu hoàn hàng không hợp lệ.');
            $this->redirect('?action=admin-returns');
        }

        $order = $orderModel->getReturnRequestById($id);
        if (!$order) {
            $this->setFlash('error', 'Không tìm thấy yêu cầu hoàn hàng #' . $id);
            $this->redirect('?action=admin-returns');
        }

        $this->renderAdminPage(
            'returns/detail',
            'Chi tiết yêu cầu hoàn hàng #' . $id . ' | Quản trị SPORTIX',
            'Chi tiết yêu cầu hoàn hàng #' . $id,
            'Thông tin đơn hàng, khách hàng và yêu cầu hoàn hàng.',
            'returns',
            [
                'order'        => $order,
                'orderItems'   => $orderModel->getOrderItems($id),
                'csrfToken'    => $this->csrfToken(),
                'flashSuccess' => $this->getFlash('success'),
                'flashError'   => $this->getFlash('error'),
            ]
        );
    }

    public function returnAccept()
    {
        $this->processReturnDecision('accept');
    }

    public function returnReject()
    {
        $this->processReturnDecision('reject');
    }

    private function processReturnDecision($decision)
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=admin-returns');
        }

        if (!$this->csrfValid()) {
            $this->setFlash('error', 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.');
            $this->redirect('?action=admin-returns');
        }

        $id = (int) ($_POST['order_id'] ?? 0);
        if ($id <= 0) {
            $this->setFlash('error', 'Mã yêu cầu hoàn hàng không hợp lệ.');
            $this->redirect('?action=admin-returns');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->getReturnRequestById($id);

        if (!$order) {
            $this->setFlash('error', 'Không tìm thấy yêu cầu hoàn hàng #' . $id);
            $this->redirect('?action=admin-returns');
        }

        if (($order['order_status'] ?? '') !== 'return_request') {
            $this->setFlash('error', 'Yêu cầu hoàn hàng #' . $id . ' đã được xử lý.');
            $this->redirect('?action=admin-return-detail&id=' . $id);
        }

        $rejectReason = null;
        if ($decision === 'reject') {
            $rejectReason = trim((string) ($_POST['reject_reason'] ?? ''));
            if ($rejectReason === '') {
                $this->setFlash('error', 'Vui lòng nhập lý do từ chối yêu cầu hoàn hàng cho đơn #' . $id . '.');
                $this->redirect('?action=admin-return-detail&id=' . $id);
            }
        }

        $success = $orderModel->updateReturnRequestDecision($id, $decision, $rejectReason);

        if ($success) {
            if ($decision === 'accept') {
                $this->setFlash('success', 'Đã chấp nhận yêu cầu hoàn hàng cho đơn #' . $id . '. Vui lòng tiến hành thu hồi hàng và xác nhận hoàn tiền ở bước tiếp theo.');
            } else {
                $this->setFlash('success', 'Đã từ chối yêu cầu hoàn hàng cho đơn #' . $id . ' với lý do: ' . $rejectReason);
            }
        } else {
            $this->setFlash('error', 'Không thể cập nhật yêu cầu hoàn hàng #' . $id . '. Vui lòng thử lại.');
        }

        $this->redirect('?action=admin-return-detail&id=' . $id);
    }

    public function returnComplete()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?action=admin-returns');
        }

        if (!$this->csrfValid()) {
            $this->setFlash('error', 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.');
            $this->redirect('?action=admin-returns');
        }

        $id = (int) ($_POST['order_id'] ?? 0);
        if ($id <= 0) {
            $this->setFlash('error', 'Mã yêu cầu hoàn hàng không hợp lệ.');
            $this->redirect('?action=admin-returns');
        }

        $orderModel = new OrderModel();
        $order = $orderModel->getReturnRequestById($id);

        if (!$order) {
            $this->setFlash('error', 'Không tìm thấy yêu cầu hoàn hàng #' . $id);
            $this->redirect('?action=admin-returns');
        }

        if (($order['order_status'] ?? '') !== 'return_accepted') {
            $this->setFlash('error', 'Yêu cầu hoàn hàng #' . $id . ' không ở trạng thái đã chấp nhận.');
            $this->redirect('?action=admin-return-detail&id=' . $id);
        }

        $confirmReceived = !empty($_POST['confirm_received']);
        $confirmRefunded = !empty($_POST['confirm_refunded']);
        if (!$confirmReceived || !$confirmRefunded) {
            $this->setFlash('error', 'Vui lòng tích xác nhận đầy đủ 2 bước: (1) Đã thu hồi hàng nguyên tem mác và (2) Đã hoàn tiền cho khách trước khi hoàn tất.');
            $this->redirect('?action=admin-return-detail&id=' . $id);
        }

        $payMethod    = (string) ($order['payment_method'] ?? 'cod');
        $refundMethod = trim((string) ($_POST['refund_method'] ?? ''));
        $refundNote   = trim((string) ($_POST['refund_note'] ?? ''));

        if ($refundMethod === '') {
            $refundMethod = ($payMethod === 'cod')
                ? 'Hoàn tiền mặt khi thu hồi hàng COD'
                : 'Chuyển khoản hoàn tiền về tài khoản thanh toán Online ban đầu';
        }

        $fullRefundNote = $refundMethod;
        if ($refundNote !== '') {
            $fullRefundNote .= ' — Ghi chú/Mã GD: ' . $refundNote;
        }

        $success = $orderModel->completeReturn($id, $fullRefundNote);

        if ($success) {
            $this->setFlash('success', 'Đã xác nhận thu hồi hàng và hoàn tiền thành công cho đơn #' . $id . ' (' . $refundMethod . ').');
        } else {
            $this->setFlash('error', 'Không thể cập nhật hoàn tất hoàn hàng #' . $id . '. Vui lòng thử lại.');
        }

        $this->redirect('?action=admin-return-detail&id=' . $id);
    }

    /**
     * Kiểm tra quyền Admin rồi hiển thị một trang trong Admin Layout.
     *
     * @param string $view         Đường dẫn View tính từ thư mục views/admin/.
     * @param string $title        Tiêu đề <title> của trang.
     * @param string $pageTitle    Tiêu đề hiển thị trên Header Admin.
     * @param string $pageSubtitle Mô tả ngắn dưới tiêu đề.
     * @param string $activeMenu   Khóa menu đang chọn trên Sidebar.
     * @param array  $data         Mảng dữ liệu truyền sang View.
     */
    private function renderAdminPage($view, $title, $pageTitle, $pageSubtitle, $activeMenu, array $data = [])
    {
        // Bắt buộc kiểm tra quyền trước khi làm bất kỳ việc gì khác.
        $adminUser = $this->requireAdmin();

        // Trích xuất các biến để View sử dụng trực tiếp
        extract($data);

        $view = 'admin/' . $view;

        require_once PATH_VIEW_ADMIN_MAIN;
    }
}
