<?php
/**
 * views/home.php
 * Trang chủ SPORTIX: Banner slider, Danh mục nổi bật, Sản phẩm mới, Sản phẩm bán chạy.
 */
?>

<!-- ==================== 1. HERO BANNER SLIDER ==================== -->
<section class="hero-slider-section" aria-label="Banner quảng cáo chính">
    <div class="sx-slider-container" id="sxHomeSlider">
        <div class="sx-slider-track">
            <?php foreach ($banners as $idx => $banner): ?>
                <div class="sx-slide <?= $idx === 0 ? 'is-active' : '' ?>"
                     data-slide-index="<?= $idx ?>"
                     style="background-image: linear-gradient(135deg, rgba(13, 27, 42, 0.88) 0%, rgba(13, 27, 42, 0.6) 50%, rgba(255, 77, 46, 0.4) 100%), url('<?= htmlspecialchars($banner['image_bg'], ENT_QUOTES, 'UTF-8') ?>');">
                    <div class="sx-slide-content">
                        <?php if (!empty($banner['badge'])): ?>
                            <span class="sx-slide-badge">
                                <i class="bi bi-lightning-charge-fill"></i>
                                <?= htmlspecialchars($banner['badge'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        <?php endif; ?>

                        <p class="sx-slide-eyebrow"><?= htmlspecialchars($banner['eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>

                        <h2 class="sx-slide-title">
                            <?= $banner['title'] ?>
                        </h2>

                        <p class="sx-slide-sub">
                            <?= htmlspecialchars($banner['subtitle'], ENT_QUOTES, 'UTF-8') ?>
                        </p>

                        <div class="sx-slide-actions">
                            <a class="btn-hero btn-hero-primary" href="<?= $banner['link'] ?>">
                                <span><?= htmlspecialchars($banner['btn_text'], ENT_QUOTES, 'UTF-8') ?></span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                            <a class="btn-hero btn-hero-outline" href="<?= BASE_URL ?>?action=products&amp;filter=sale">
                                <span>Xem khuyến mãi</span>
                                <i class="bi bi-tag-fill"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Điều khiển chuyển slide: Mũi tên Trước / Sau -->
        <button type="button" class="sx-slider-nav sx-slider-prev" aria-label="Slide trước" id="sliderPrevBtn">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button type="button" class="sx-slider-nav sx-slider-next" aria-label="Slide tiếp theo" id="sliderNextBtn">
            <i class="bi bi-chevron-right"></i>
        </button>

        <!-- Chỉ số Slide (Dots) -->
        <div class="sx-slider-dots" id="sliderDots">
            <?php foreach ($banners as $idx => $b): ?>
                <button type="button" class="sx-dot <?= $idx === 0 ? 'is-active' : '' ?>"
                        data-target-slide="<?= $idx ?>"
                        aria-label="Chuyển tới slide <?= $idx + 1 ?>">
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ==================== 2. THANH TIỆN ÍCH DỊCH VỤ ==================== -->
<section class="trust-perks-section" aria-label="Lợi ích mua hàng tại SPORTIX">
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="perk-card">
                <div class="perk-icon-wrap text-accent">
                    <i class="bi bi-truck"></i>
                </div>
                <div class="perk-info">
                    <h3 class="perk-title">Miễn phí giao hàng</h3>
                    <p class="perk-desc">Đơn hàng từ 500.000&#8363;</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="perk-card">
                <div class="perk-icon-wrap text-accent">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div class="perk-info">
                    <h3 class="perk-title">Đổi trả 30 ngày</h3>
                    <p class="perk-desc">Thủ tục nhanh chóng</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="perk-card">
                <div class="perk-icon-wrap text-accent">
                    <i class="bi bi-patch-check"></i>
                </div>
                <div class="perk-info">
                    <h3 class="perk-title">100% Chính hãng</h3>
                    <p class="perk-desc">Cam kết chất lượng chuẩn</p>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="perk-card">
                <div class="perk-icon-wrap text-accent">
                    <i class="bi bi-headset"></i>
                </div>
                <div class="perk-info">
                    <h3 class="perk-title">Hỗ trợ 24/7</h3>
                    <p class="perk-desc">Hotline 1900 0000</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== 3. DANH MỤC NỔI BẬT ==================== -->
<section class="home-section section-categories" aria-label="Danh mục sản phẩm nổi bật">
    <div class="section-header">
        <div class="section-header-left">
            <span class="section-badge">
                <i class="bi bi-grid-fill"></i> BỘ SƯU TẬP
            </span>
            <h2 class="section-title">Danh Mục Nổi Bật</h2>
            <p class="section-subtitle">Lựa chọn trang phục thể thao tối ưu theo nhu cầu tập luyện của bạn</p>
        </div>
        <div class="section-header-right">
            <a class="btn-section-more" href="<?= BASE_URL ?>?action=products">
                <span>Xem tất cả danh mục</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="row g-3 g-lg-4">
        <?php foreach ($featuredCategories as $cat): ?>
            <?php
                $catId    = (int) ($cat['categories_id'] ?? 0);
                $catName  = (string) ($cat['categories_name'] ?? 'Danh mục');
                $catDesc  = (string) ($cat['description'] ?? 'Khám phá sản phẩm thể thao chất lượng cao');
                $catCount = (int) ($cat['product_count'] ?? 0);
                $catBadge = (string) ($cat['badge'] ?? '');
                $catIcon  = (string) ($cat['icon'] ?? 'bi-tags-fill');
            ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a class="category-card" href="<?= BASE_URL ?>?action=products&amp;category=<?= $catId ?>">
                    <?php if (!empty($catBadge)): ?>
                        <span class="cat-pill"><?= htmlspecialchars($catBadge, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>

                    <div class="cat-icon-box">
                        <i class="bi <?= htmlspecialchars($catIcon, ENT_QUOTES, 'UTF-8') ?>"></i>
                    </div>

                    <h3 class="cat-name"><?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?></h3>

                    <p class="cat-count">
                        <?= $catCount > 0 ? $catCount . ' sản phẩm' : 'Xem sản phẩm' ?>
                    </p>

                    <span class="cat-arrow" aria-hidden="true">
                        <i class="bi bi-arrow-right-short"></i>
                    </span>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ==================== 4. SẢN PHẨM MỚI ==================== -->
<section class="home-section section-new-arrivals" aria-label="Sản phẩm mới">
    <div class="section-header">
        <div class="section-header-left">
            <span class="section-badge badge-green">
                <i class="bi bi-stars"></i> MỚI CẬP NHẬT
            </span>
            <h2 class="section-title">Sản Phẩm Mới Nhất</h2>
            <p class="section-subtitle">Đón đầu xu hướng thời trang thể thao phong cách và hiện đại nhất</p>
        </div>
        <div class="section-header-right">
            <a class="btn-section-more" href="<?= BASE_URL ?>?action=products&amp;filter=new">
                <span>Xem tất cả mới về</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="row g-3 g-lg-4">
        <?php if (!empty($newProducts)): ?>
            <?php foreach ($newProducts as $product): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <?php
                    $cardBadge = 'new';
                    require PATH_VIEW . 'partials/product-card.php';
                    ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="empty-state">Đang cập nhật sản phẩm mới...</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ==================== 5. BANNER QUẢNG BÁ KHUYẾN MÃI ==================== -->
<section class="promo-strip-banner" aria-label="Quảng bá mùa giải mới">
    <div class="promo-strip-card">
        <div class="row align-items-center gy-3">
            <div class="col-12 col-lg-8">
                <span class="promo-tag">
                    <i class="bi bi-fire"></i> KHUYẾN MÃI ĐỘC QUYỀN SPORTIX
                </span>
                <h2 class="promo-strip-title">
                    Thành Viên Mới? Nhận Ngay <span class="accent">Voucher Giảm 10%</span>
                </h2>
                <p class="promo-strip-sub">
                    Đăng ký tài khoản SPORTIX ngay hôm nay để nhận mã giảm giá đặc quyền cho đơn hàng đầu tiên cùng nhiều ưu đãi tích điểm hấp dẫn.
                </p>
            </div>
            <div class="col-12 col-lg-4 text-lg-end">
                <div class="promo-strip-actions">
                    <a class="btn-hero btn-hero-primary" href="<?= BASE_URL ?>?action=register">
                        <span>Đăng ký thành viên</span>
                        <i class="bi bi-person-plus-fill"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== 6. SẢN PHẨM BÁN CHẠY ==================== -->
<section class="home-section section-best-sellers" aria-label="Sản phẩm bán chạy">
    <div class="section-header">
        <div class="section-header-left">
            <span class="section-badge badge-orange">
                <i class="bi bi-fire"></i> XU HƯỚNG MUA SẮM
            </span>
            <h2 class="section-title">Top Sản Phẩm Bán Chạy</h2>
            <p class="section-subtitle">Những mẫu trang phục được hàng nghìn tín đồ thể thao tin dùng và đánh giá cao</p>
        </div>
        <div class="section-header-right">
            <a class="btn-section-more" href="<?= BASE_URL ?>?action=products&amp;filter=bestseller">
                <span>Xem tất cả bán chạy</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <div class="row g-3 g-lg-4">
        <?php if (!empty($bestSellingProducts)): ?>
            <?php foreach ($bestSellingProducts as $product): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <?php
                    $cardBadge = 'hot';
                    require PATH_VIEW . 'partials/product-card.php';
                    ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="empty-state">Đang cập nhật sản phẩm bán chạy...</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ==================== SCRIPT ĐIỀU KHIỂN SLIDER ==================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const slider = document.getElementById('sxHomeSlider');
    if (!slider) return;

    const slides = slider.querySelectorAll('.sx-slide');
    const dots = slider.querySelectorAll('.sx-dot');
    const prevBtn = document.getElementById('sliderPrevBtn');
    const nextBtn = document.getElementById('sliderNextBtn');

    if (slides.length <= 1) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        return;
    }

    let currentIndex = 0;
    let autoSlideTimer = null;

    function goToSlide(index) {
        if (index < 0) {
            index = slides.length - 1;
        } else if (index >= slides.length) {
            index = 0;
        }

        slides.forEach(function (slide, i) {
            slide.classList.toggle('is-active', i === index);
        });

        dots.forEach(function (dot, i) {
            dot.classList.toggle('is-active', i === index);
        });

        currentIndex = index;
    }

    function startAutoSlide() {
        stopAutoSlide();
        autoSlideTimer = setInterval(function () {
            goToSlide(currentIndex + 1);
        }, 5500);
    }

    function stopAutoSlide() {
        if (autoSlideTimer) {
            clearInterval(autoSlideTimer);
            autoSlideTimer = null;
        }
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            goToSlide(currentIndex - 1);
            startAutoSlide();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            goToSlide(currentIndex + 1);
            startAutoSlide();
        });
    }

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            const target = parseInt(dot.getAttribute('data-target-slide'), 10);
            if (!isNaN(target)) {
                goToSlide(target);
                startAutoSlide();
            }
        });
    });

    slider.addEventListener('mouseenter', stopAutoSlide);
    slider.addEventListener('mouseleave', startAutoSlide);

    // Bắt đầu tự động chuyển slide
    startAutoSlide();
});
</script>
