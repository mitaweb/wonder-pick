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
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
      <span>Trang chủ</span>
    </a>
    <a href="member.php" class="nav-link <?= $current==='member.php'?'active':'' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <span>Thành Viên</span>
    </a>
    <a href="buy.php" class="nav-link <?= $current==='buy.php'?'active':'' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
      <span>Mua Gói</span>
    </a>
    <a href="register.php" class="nav-link <?= $current==='register.php'?'active':'' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
      <span>Đăng Ký</span>
    </a>
    <a href="admin.php" class="nav-link <?= $current==='admin.php'?'active':'' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      <span>Admin</span>
    </a>
  </div>
</nav>

<style>
/* ── Nav fix: không tràn màn hình mobile ── */
.navbar {
  overflow: hidden; /* bọc ngoài không scroll */
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 2px;
  overflow-x: auto;          /* scroll ngang nếu không đủ chỗ */
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;     /* ẩn scrollbar Firefox */
  flex-shrink: 1;
  min-width: 0;
}
.nav-links::-webkit-scrollbar { display: none; } /* ẩn scrollbar Chrome */

.nav-link {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 3px;
  white-space: nowrap;
  flex-shrink: 0;
  padding: 6px 10px;
  font-size: 11px;
}

.nav-link svg {
  flex-shrink: 0;
}

/* Trên màn hình nhỏ: ẩn text, chỉ hiện icon */
@media (max-width: 480px) {
  .nav-brand .brand-sub { display: none; }
  .nav-brand .brand-name { font-size: 13px; }
  .brand-icon { width: 28px; height: 28px; }

  .nav-link span { display: none; }
  .nav-link { padding: 6px 8px; }
}

/* Màn hình rất nhỏ: thu brand lại thêm */
@media (max-width: 360px) {
  .nav-brand { gap: 6px; }
  .brand-icon { width: 24px; height: 24px; }
  .nav-link { padding: 6px 6px; }
}
</style>
