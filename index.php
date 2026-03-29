<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wonder Pickleball</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
.hero-banner {
  background: linear-gradient(135deg, #085041 0%, #1D9E75 60%, #2BC48A 100%);
  border-radius: var(--radius-xl); padding: 40px 28px; margin-bottom: 24px;
  color: white; text-align: center; position: relative; overflow: hidden;
}
.hero-banner::before {
  content: ''; position: absolute; top: -40px; right: -40px;
  width: 180px; height: 180px; background: rgba(255,255,255,.06);
  border-radius: 50%;
}
.hero-banner::after {
  content: ''; position: absolute; bottom: -30px; left: -20px;
  width: 120px; height: 120px; background: rgba(255,255,255,.04);
  border-radius: 50%;
}
.hero-logo { font-size: 48px; margin-bottom: 12px; }
.hero-title { font-size: 28px; font-weight: 600; margin-bottom: 6px; position: relative; }
.hero-sub { font-size: 15px; opacity: .85; position: relative; }

.action-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px;
}
.action-card {
  background: var(--bg); border: 0.5px solid var(--border2);
  border-radius: var(--radius-lg); padding: 24px 18px; text-align: center;
  text-decoration: none; color: var(--text); transition: all .2s;
  box-shadow: var(--shadow); display: flex; flex-direction: column;
  align-items: center; gap: 10px;
}
.action-card:hover { border-color: var(--green); transform: translateY(-2px); box-shadow: var(--shadow-lg); }
.action-icon {
  width: 52px; height: 52px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center; font-size: 24px;
}
.action-icon.green { background: var(--green-light); color: var(--green-dark); }
.action-icon.amber { background: var(--amber-light); color: var(--amber); }
.action-icon.blue { background: #E6F1FB; color: #378ADD; }
.action-icon.red { background: var(--red-light); color: var(--red); }
.action-name { font-size: 14px; font-weight: 600; }
.action-desc { font-size: 12px; color: var(--text2); line-height: 1.4; }

.banner-section { margin-bottom: 24px; }
.banner-title {
  font-size: 18px; font-weight: 600; margin-bottom: 14px;
  display: flex; align-items: center; gap: 8px;
}
.banner-card {
  background: var(--bg); border: 0.5px solid var(--border2);
  border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 12px;
  box-shadow: var(--shadow);
}
.banner-img {
  width: 100%; height: 180px; object-fit: cover;
  background: linear-gradient(135deg, #085041, #1D9E75);
  display: flex; align-items: center; justify-content: center;
}
.banner-placeholder {
  padding: 32px; text-align: center; color: white;
}
.banner-placeholder .bp-icon { font-size: 48px; margin-bottom: 10px; }
.banner-placeholder .bp-title { font-size: 20px; font-weight: 600; margin-bottom: 4px; }
.banner-placeholder .bp-sub { font-size: 13px; opacity: .8; }
.banner-body { padding: 16px; }
.banner-body h3 { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
.banner-body p { font-size: 13px; color: var(--text2); line-height: 1.5; }

.pricing-quick {
  background: var(--bg); border: 0.5px solid var(--border2);
  border-radius: var(--radius-lg); padding: 20px; margin-bottom: 24px;
  box-shadow: var(--shadow);
}
.pricing-quick h3 { font-size: 16px; font-weight: 600; margin-bottom: 14px; }
.price-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 10px 0; border-bottom: 0.5px solid var(--border); font-size: 14px;
}
.price-row:last-child { border-bottom: none; }
.price-value { font-weight: 600; color: var(--green-dark); }

.info-footer {
  text-align: center; padding: 20px 0; font-size: 12px; color: var(--text3);
}
</style>
</head>
<body>
<div class="layout">
  <?php include __DIR__ . '/includes/nav.php'; ?>
  <main class="main-content">

    <!-- Hero Banner -->
    <div class="hero-banner">
      <div class="hero-logo">🏓</div>
      <div class="hero-title">Wonder Pickleball</div>
      <div class="hero-sub">Chơi Pickleball — Sống khỏe mỗi ngày</div>
    </div>

    <!-- Action Buttons -->
    <div class="action-grid">
      <a href="member.php" class="action-card">
        <div class="action-icon green">👤</div>
        <div class="action-name">Đăng nhập</div>
        <div class="action-desc">Xem thẻ tập & check-in</div>
      </a>
      <a href="register.php" class="action-card">
        <div class="action-icon blue">✏</div>
        <div class="action-name">Đăng ký</div>
        <div class="action-desc">Tạo tài khoản mới</div>
      </a>
      <a href="buy.php" class="action-card">
        <div class="action-icon amber">🎫</div>
        <div class="action-name">Mua gói tập</div>
        <div class="action-desc">Gói 10, 30 buổi hoặc lẻ</div>
      </a>
      <a href="member.php" class="action-card">
        <div class="action-icon red">📱</div>
        <div class="action-name">Check-in</div>
        <div class="action-desc">Quét QR vào chơi</div>
      </a>
    </div>

    <!-- Tournament Banners -->
    <div class="banner-section">
      <div class="banner-title">🏆 Giải đấu & Sự kiện</div>

      <div class="banner-card">
        <div class="banner-img">
          <div class="banner-placeholder">
            <div class="bp-icon">🏆</div>
            <div class="bp-title">Wonder Open 2026</div>
            <div class="bp-sub">Giải Pickleball mở rộng</div>
          </div>
        </div>
        <div class="banner-body">
          <h3>Wonder Open 2026 — Mùa Xuân</h3>
          <p>Giải đấu Pickleball hàng tháng dành cho mọi trình độ. Đăng ký tham gia để có cơ hội nhận giải thưởng hấp dẫn và giao lưu cùng cộng đồng!</p>
        </div>
      </div>

      <div class="banner-card">
        <div class="banner-img" style="background:linear-gradient(135deg,#1a365d,#378ADD)">
          <div class="banner-placeholder">
            <div class="bp-icon">🎓</div>
            <div class="bp-title">Lớp học Pickleball</div>
            <div class="bp-sub">Cho người mới bắt đầu</div>
          </div>
        </div>
        <div class="banner-body">
          <h3>Lớp học Pickleball cơ bản</h3>
          <p>Dành cho người mới! Học kỹ thuật cơ bản từ huấn luyện viên chuyên nghiệp. Mỗi tuần 2 buổi, miễn phí cho hội viên gói 30 buổi.</p>
        </div>
      </div>
    </div>

    <!-- Quick Pricing -->
    <div class="pricing-quick">
      <h3>💰 Bảng giá nhanh</h3>
      <div class="price-row">
        <span>Gói 10 tặng 3 (13 buổi, 1 tháng)</span>
        <span class="price-value"><?= number_format(PRICE_PKG_10) ?>đ</span>
      </div>
      <div class="price-row">
        <span>Gói 30 tặng 10 (40 buổi, 3 tháng)</span>
        <span class="price-value"><?= number_format(PRICE_PKG_30) ?>đ</span>
      </div>
      <div class="price-row">
        <span>Lẻ sáng (8h-11h)</span>
        <span class="price-value"><?= number_format(PRICE_SOCIAL_MORNING) ?>đ</span>
      </div>
      <div class="price-row">
        <span>Lẻ trưa (11h-16h)</span>
        <span class="price-value"><?= number_format(PRICE_SOCIAL_NOON) ?>đ</span>
      </div>
      <div class="price-row">
        <span>Lẻ chiều/tối (16h-22h)</span>
        <span class="price-value"><?= number_format(PRICE_SOCIAL_EVENING) ?>đ</span>
      </div>
      <div class="price-row">
        <span>🎠 Khu vui chơi trẻ em</span>
        <span class="price-value"><?= number_format(PRICE_KIDS) ?>đ/trẻ</span>
      </div>
    </div>

    <div class="info-footer">
      Wonder Pickleball &copy; <?= date('Y') ?> · Liên hệ: <?= BANK_OWNER ?>
    </div>

  </main>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
