<?php $current = basename($_SERVER['PHP_SELF']); ?>
<nav class="navbar">
  <div class="nav-brand">
    <div class="brand-icon">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="white"><circle cx="12" cy="12" r="10"/><path fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="1.5" d="M6 12 Q12 5 18 12 Q12 19 6 12"/><circle cx="12" cy="12" r="2.5" fill="white"/></svg>
    </div>
    <div>
      <div class="brand-name">Wonder Pickleball</div>
      <div class="brand-sub">Quản lý thẻ tập</div>
    </div>
  </div>
  <div class="nav-links">
    <a href="index.php" class="nav-link <?= $current==='index.php'?'active':'' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      Lễ Tân
    </a>
    <a href="member.php" class="nav-link <?= $current==='member.php'?'active':'' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Thành Viên
    </a>
    <a href="buy.php" class="nav-link <?= $current==='buy.php'?'active':'' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
      Mua Gói
    </a>
    <a href="register.php" class="nav-link <?= $current==='register.php'?'active':'' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
      Đăng Ký
    </a>
    <a href="admin.php" class="nav-link <?= $current==='admin.php'?'active':'' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      Admin
    </a>
  </div>
</nav>
