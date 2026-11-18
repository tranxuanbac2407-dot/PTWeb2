<div class="checkout-page">
    <h2>Thanh toán</h2>
    <form action="<?= BASE_URL ?>?action=process-checkout" method="post">
        <div class="mb-3">
            <label for="name" class="form-label">Họ và tên</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Số điện thoại</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Địa chỉ</label>
            <textarea class="form-control" id="address" name="address" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Xác nhận đặt hàng</button>
    </form>
</div>
