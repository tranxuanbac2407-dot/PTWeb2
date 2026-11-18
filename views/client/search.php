<?php $escKeyword = htmlspecialchars($keyword ?? '', ENT_QUOTES, 'UTF-8'); ?>

<section class="page-section">

    <?php if ($escKeyword !== ''): ?>
        <h1 class="page-title">Kết quả tìm kiếm cho "<?= $escKeyword ?>"</h1>
    <?php else: ?>
        <h1 class="page-title">Kết quả tìm kiếm</h1>
    <?php endif; ?>

    <?php if ($escKeyword === ''): ?>

        <p class="empty-state">
            Vui lòng nhập từ khóa vào thanh tìm kiếm phía trên trang để tìm sản phẩm.
        </p>

    <?php elseif (empty($products)): ?>

        <p class="empty-state">
            Không tìm thấy sản phẩm nào khớp với từ khóa
            <strong>"<?= $escKeyword ?>"</strong>.
            Bạn có thể thử với từ khóa khác.
        </p>

    <?php else: ?>

        <p class="result-count">Tìm thấy <?= count($products) ?> sản phẩm:</p>

        <div class="row g-3">
            <?php foreach ($products as $product): ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <a class="product-thumb"
                           href="<?= BASE_URL ?>?action=product-detail&amp;id=<?= (int) $product['product_id'] ?>">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= BASE_ASSETS_UPLOADS . htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?>">
                            <?php else: ?>
                                <span class="product-no-image">Chưa có hình ảnh</span>
                            <?php endif; ?>
                        </a>
                        <div class="product-info">
                            <a class="product-name"
                               href="<?= BASE_URL ?>?action=product-detail&amp;id=<?= (int) $product['product_id'] ?>">
                                <?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8') ?>
                            </a>
                            <span class="product-price">
                                <?= number_format((float) $product['price'], 0, ',', '.') ?>&#8363;
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</section>
