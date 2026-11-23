<?php

if (!function_exists('is_valid_phone')) {
    function is_valid_phone($phone)
    {
        return is_string($phone) && preg_match('/^[0-9]{10}$/', $phone) === 1;
    }
}

if (!function_exists('get_standard_shipping_fee')) {
    function get_standard_shipping_fee()
    {
        return 30000;
    }
}

if (!function_exists('get_free_shipping_threshold')) {
    function get_free_shipping_threshold()
    {
        return 500000;
    }
}

if (!function_exists('calculate_shipping_fee')) {
    function calculate_shipping_fee($subtotal)
    {
        $subtotal = (float) $subtotal;
        if ($subtotal <= 0 || $subtotal >= get_free_shipping_threshold()) {
            return 0;
        }

        return get_standard_shipping_fee();
    }
}

if (!function_exists('debug')) {
    function debug($data)
    {
        echo '<pre>';
        print_r($data);
        die;
    }
}

if (!function_exists('upload_file')) {
    /**
     * Tải tệp lên thư mục uploads của hệ thống.
     * Tự động tạo thư mục con nếu chưa tồn tại và tạo tên file ngẫu nhiên an toàn.
     *
     * @param string $folder Thư mục con trong assets/uploads/ (ví dụ: 'products')
     * @param array  $file   Phần tử từ $_FILES (ví dụ: $_FILES['image'])
     * @return string Đường dẫn tương đối lưu vào CSDL (ví dụ: 'products/171234_ab12.jpg')
     * @throws Exception
     */
    function upload_file($folder, $file)
    {
        if (empty($file) || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Tệp tải lên không hợp lệ hoặc đã xảy ra lỗi trong quá trình tải.');
        }

        $folderClean = trim(str_replace(['..', '\\'], '', $folder), '/');
        $targetDir   = PATH_ASSETS_UPLOADS . ($folderClean !== '' ? $folderClean . '/' : '');

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                throw new Exception('Không thể tạo thư mục lưu trữ ảnh tải lên.');
            }
        }

        $origName = $file['name'] ?? 'image.jpg';
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        // Tên file an toàn: timestamp + chuỗi ngẫu nhiên + extension
        $safeName = time() . '_' . bin2hex(random_bytes(4)) . ($ext !== '' ? '.' . $ext : '');
        $targetPath = $targetDir . $safeName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ($folderClean !== '' ? $folderClean . '/' : '') . $safeName;
        }

        throw new Exception('Lưu tệp tải lên vào máy chủ thất bại.');
    }
}

if (!function_exists('delete_upload_file')) {
    /**
     * Xóa an toàn tệp tải lên khỏi thư mục uploads (tránh xóa nhầm file hệ thống).
     * Bỏ qua nếu là URL bên ngoài (http/https) hoặc chuỗi rỗng.
     *
     * @param string|null $relativePath Đường dẫn tương đối lưu trong CSDL
     * @return bool
     */
    function delete_upload_file($relativePath)
    {
        if (empty($relativePath)) {
            return false;
        }

        // Bỏ qua nếu là đường dẫn URL bên ngoài
        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return false;
        }

        // Ngăn chặn tấn công Path Traversal
        $cleaned = str_replace(['..', '\\'], '', $relativePath);
        $fullPath = PATH_ASSETS_UPLOADS . ltrim($cleaned, '/');

        if (is_file($fullPath) && file_exists($fullPath)) {
            return @unlink($fullPath);
        }

        return false;
    }
}

if (!function_exists('is_valid_image_upload')) {
    /**
     * Kiểm tra tính hợp lệ của tệp hình ảnh tải lên.
     *
     * @param array $file Phần tử trong $_FILES
     * @param string|null &$errorMessage Biến nhận thông báo lỗi nếu không hợp lệ
     * @param int $maxSizeMax Kích thước tối đa theo byte (mặc định 5MB)
     * @return bool
     */
    function is_valid_image_upload($file, &$errorMessage = null, $maxSizeMax = 5242880)
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return true; // Không chọn file là hợp lệ (form không bắt buộc upload)
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = 'Quá trình tải ảnh lên gặp sự cố (mã lỗi: ' . $file['error'] . ').';
            return false;
        }

        if ($file['size'] > $maxSizeMax) {
            $errorMessage = 'Dung lượng ảnh vượt quá giới hạn cho phép (tối đa 5MB).';
            return false;
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExts, true)) {
            $errorMessage = 'Định dạng tệp không được hỗ trợ. Vui lòng chọn ảnh JPG, PNG, WEBP hoặc GIF.';
            return false;
        }

        // Kiểm tra loại MIME thực tế của tệp
        if (!empty($file['tmp_name']) && is_file($file['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($mime, $allowedMimes, true)) {
                    $errorMessage = 'Nội dung tệp không phải là hình ảnh hợp lệ.';
                    return false;
                }
            }
        }

        return true;
    }
}

if (!function_exists('get_product_image_url')) {
    /**
     * Chuẩn hóa đường dẫn hiển thị ảnh sản phẩm.
     * Hỗ trợ cả URL ngoài (Unsplash, CDN) và tệp upload nội bộ.
     *
     * @param string|null $image
     * @return string
     */
    function get_product_image_url($image)
    {
        if (empty($image)) {
            return '';
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        return BASE_ASSETS_UPLOADS . ltrim($image, '/');
    }
}

if (!function_exists('get_vietqr_url')) {
    /**
     * Tạo đường dẫn ảnh mã VietQR tự động theo chuẩn Napas 24/7.
     * Sử dụng dịch vụ VietQR API mở giúp khách hàng quét mã chuyển tiền chính xác.
     *
     * @param int|float   $amount  Số tiền cần thanh toán
     * @param string      $memo    Nội dung chuyển khoản (VD: SPORTIX DH12)
     * @param string|null $bank    Mã ngân hàng (VD: MB, VCB)
     * @param string|null $account Số tài khoản thụ hưởng
     * @param string|null $name    Tên chủ tài khoản
     * @param string      $template Giao diện QR (compact2, compact, qr_only, print)
     * @return string
     */
    function get_vietqr_url($amount, $memo = '', $bank = null, $account = null, $name = null, $template = 'compact2')
    {
        $bankId   = $bank ?? (defined('BANK_ID') ? BANK_ID : 'MB');
        $accNo    = $account ?? (defined('BANK_ACCOUNT_NO') ? BANK_ACCOUNT_NO : '0987654321');
        $accName  = $name ?? (defined('BANK_ACCOUNT_NAME') ? BANK_ACCOUNT_NAME : 'SPORTIX STORE');

        $cleanAmount = (int) round($amount);
        $cleanMemo   = urlencode(trim((string) $memo));
        $cleanName   = urlencode(trim((string) $accName));

        return "https://img.vietqr.io/image/{$bankId}-{$accNo}-{$template}.png?amount={$cleanAmount}&addInfo={$cleanMemo}&accountName={$cleanName}";
    }
}
