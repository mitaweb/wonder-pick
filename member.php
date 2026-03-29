<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thành Viên — Wonder Pickleball</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* ---- Login screen ---- */
.login-wrap {
  min-height: 60vh; display: flex; flex-direction: column;
  align-items: center; justify-content: center; text-align: center; padding: 32px 0;
}
.login-logo {
  width: 72px; height: 72px; background: var(--green); border-radius: 20px;
  display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;
}
.login-logo svg { width: 40px; height: 40px; fill: white; }
.login-title { font-size: 24px; font-weight: 600; margin-bottom: 6px; }
.login-sub { font-size: 14px; color: var(--text2); margin-bottom: 32px; }
.login-box { width: 100%; max-width: 360px; }
.big-phone-input {
  width: 100%; padding: 14px 18px; font-size: 22px; font-family: 'DM Mono', monospace;
  font-weight: 500; text-align: center; border: 1.5px solid var(--border2);
  border-radius: var(--radius-lg); background: var(--bg2); color: var(--text);
  outline: none; transition: all .2s; letter-spacing: 2px;
}
.big-phone-input:focus { border-color: var(--green); background: var(--bg); box-shadow: 0 0 0 4px rgba(29,158,117,.1); }
.login-btn { margin-top: 14px; width: 100%; padding: 14px; font-size: 16px; }

/* ---- Member profile ---- */
.member-hero {
  background: linear-gradient(135deg, #085041 0%, #1D9E75 100%);
  border-radius: var(--radius-xl); padding: 28px 24px; margin-bottom: 20px; color: white; position: relative;
}
.member-avatar {
  width: 56px; height: 56px; background: rgba(255,255,255,.2);
  border-radius: 50%; display: flex; align-items: center; justify-content: center;
  font-size: 22px; font-weight: 600; color: white; margin-bottom: 12px;
}
.member-name { font-size: 22px; font-weight: 600; }
.member-phone { font-size: 14px; opacity: .75; font-family: 'DM Mono', monospace; margin-top: 2px; }
.member-logout {
  position: absolute; top: 20px; right: 20px;
  background: rgba(255,255,255,.15); border: none; border-radius: var(--radius);
  color: white; font-size: 12px; padding: 6px 12px; cursor: pointer;
}
.member-logout:hover { background: rgba(255,255,255,.25); }

/* ---- Sessions big display ---- */
.sessions-showcase {
  display: flex; align-items: center; gap: 20px; margin-top: 20px;
  padding-top: 20px; border-top: 1px solid rgba(255,255,255,.15);
}
.sessions-big-num { font-size: 64px; font-weight: 600; line-height: 1; }
.sessions-big-num.low { color: #FFD580; }
.sessions-big-num.zero { color: #FF9F9F; }
.sessions-big-info { flex: 1; }
.sessions-big-label { font-size: 16px; font-weight: 500; opacity: .9; }
.sessions-big-sub { font-size: 13px; opacity: .65; margin-top: 4px; }
.sessions-big-expiry { font-size: 12px; opacity: .7; margin-top: 8px; }

/* ---- Progress bar on hero ---- */
.hero-bar-track { height: 6px; background: rgba(255,255,255,.2); border-radius: 99px; margin-top: 14px; overflow: hidden; }
.hero-bar-fill { height: 100%; background: white; border-radius: 99px; transition: width .5s ease; }
.hero-bar-fill.low { background: #FFD580; }
.hero-bar-fill.zero { background: #FF9F9F; width: 100% !important; }

/* ---- QR card ---- */
.qr-card {
  display: flex; align-items: center; gap: 16px;
  background: var(--bg); border: 0.5px solid var(--border2);
  border-radius: var(--radius-lg); padding: 16px; margin-bottom: 20px;
}
.qr-card-info { flex: 1; }
.qr-card-title { font-size: 14px; font-weight: 500; margin-bottom: 4px; }
.qr-card-sub { font-size: 12px; color: var(--text2); }

/* ---- Buy more section ---- */
.buy-more-title {
  font-size: 18px; font-weight: 600; margin-bottom: 16px;
  display: flex; align-items: center; gap: 8px;
}
.mini-pkg { border: 1px solid var(--border2); border-radius: var(--radius-lg); padding: 14px 16px; margin-bottom: 10px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; transition: all .15s; }
.mini-pkg:hover { border-color: var(--green); background: var(--green-light); }
.mini-pkg.selected { border-color: var(--green); border-width: 2px; background: var(--green-light); }
.mini-pkg-left { flex: 1; }
.mini-pkg-name { font-size: 14px; font-weight: 500; }
.mini-pkg-desc { font-size: 12px; color: var(--text2); margin-top: 2px; }
.mini-pkg-price { font-size: 18px; font-weight: 600; color: var(--green); text-align: right; }
.mini-pkg-sessions { font-size: 11px; color: var(--text2); text-align: right; margin-top: 2px; }

/* Kids inline */
.kids-inline { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; background: var(--bg2); border: 0.5px solid var(--border); border-radius: var(--radius-lg); margin-bottom: 16px; }
.kids-inline-left .title { font-size: 14px; font-weight: 500; }
.kids-inline-left .sub { font-size: 12px; color: var(--text2); margin-top: 2px; }
.kids-counter { display: flex; align-items: center; gap: 10px; }
.kids-count-num { font-size: 20px; font-weight: 600; min-width: 28px; text-align: center; }
.counter-btn { width: 32px; height: 32px; border-radius: 50%; border: 0.5px solid var(--border2); background: var(--bg); cursor: pointer; font-size: 18px; display: flex; align-items: center; justify-content: center; transition: all .15s; }
.counter-btn:hover { background: var(--green); color: white; border-color: var(--green); }

.buy-total { display: flex; justify-content: space-between; align-items: center; background: var(--bg2); border-radius: var(--radius-lg); padding: 14px 18px; margin-bottom: 16px; }
.buy-total-lbl { font-size: 14px; color: var(--text2); }
.buy-total-amt { font-size: 24px; font-weight: 600; color: var(--green-dark); }

/* Payment overlay */
.pay-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 200; display: flex; align-items: center; justify-content: center; padding: 20px; }
.pay-box { background: var(--bg); border-radius: var(--radius-xl); padding: 28px; max-width: 420px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,.25); }
.qr-img-wrap { background: white; border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 14px; text-align: center; margin-bottom: 16px; }
.qr-img-wrap img { max-width: 200px; width: 100%; }
.ck-table td { padding: 5px 0; font-size: 13px; }
.ck-table td:first-child { color: var(--text2); width: 110px; }
.ck-table td:last-child { font-weight: 500; }
.copy-btn { background: none; border: none; cursor: pointer; color: var(--green); font-size: 12px; margin-left: 6px; font-family: inherit; }
.warn-note { background: var(--amber-light); border: 0.5px solid #FAC775; border-radius: var(--radius); padding: 10px 14px; font-size: 12px; color: #633806; margin: 12px 0; }
.waiting-row { display: flex; align-items: center; gap: 8px; justify-content: center; font-size: 13px; color: var(--text2); margin-top: 12px; }
.dot-pulse { display: flex; gap: 4px; }
.dot-pulse span { width: 6px; height: 6px; border-radius: 50%; background: var(--green); animation: dp 1.2s infinite; }
.dot-pulse span:nth-child(2) { animation-delay:.2s; }
.dot-pulse span:nth-child(3) { animation-delay:.4s; }
@keyframes dp { 0%,80%,100%{transform:scale(.8);opacity:.5} 40%{transform:scale(1.2);opacity:1} }

.success-pop { text-align: center; padding: 10px 0; }
.success-ring { width: 72px; height: 72px; background: var(--green); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; animation: pop .4s cubic-bezier(.175,.885,.32,1.275); }
.success-ring svg { width: 36px; height: 36px; stroke: white; stroke-width: 3; fill: none; }
@keyframes pop { from{transform:scale(0)} to{transform:scale(1)} }

/* Checkin history */
.history-card { background: var(--bg); border: 0.5px solid var(--border2); border-radius: var(--radius-lg); padding: 20px; margin-bottom: 20px; }
</style>
</head>
<body>
<div class="layout">
  <?php include __DIR__ . '/includes/nav.php'; ?>
  <main class="main-content">

    <!-- LOGIN -->
    <div id="login-screen">
      <div class="login-wrap">
        <div class="login-logo">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="1.5" d="M6 12 Q12 5 18 12 Q12 19 6 12"/><circle cx="12" cy="12" r="2.5" fill="white"/></svg>
        </div>
        <div class="login-title">Trang thành viên</div>
        <div class="login-sub">Đăng nhập để xem thẻ tập của bạn</div>
        <div class="login-box">
          <div id="login-alert" class="alert hidden"></div>

          <!-- Phone + password login -->
          <div id="login-form-view">
            <div style="margin-bottom:10px">
              <input type="tel" id="login-phone" class="form-input" placeholder="Số điện thoại"
                style="font-size:16px;padding:12px 16px;margin-bottom:10px"
                maxlength="12" oninput="fmtBigPhone(this)" onkeydown="if(event.key==='Enter')document.getElementById('login-pw').focus()">
              <div style="position:relative">
                <input type="password" id="login-pw" class="form-input" placeholder="Mật khẩu"
                  style="font-size:16px;padding:12px 44px 12px 16px"
                  onkeydown="if(event.key==='Enter')doLogin()">
                <button type="button" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text2);font-size:14px" onclick="toggleLoginPw(this)">👁</button>
              </div>
            </div>
            <button class="btn btn-primary login-btn" onclick="doLogin()">Đăng nhập →</button>
            <div style="display:flex;justify-content:space-between;margin-top:14px;font-size:13px">
              <span style="color:var(--text3)">Chưa có tài khoản? <a href="register.php" style="color:var(--green)">Đăng ký</a></span>
              <a href="#" style="color:var(--text2)" onclick="showForgot();return false">Quên mật khẩu?</a>
            </div>
          </div>

          <!-- Forgot password form -->
          <div id="forgot-form-view" class="hidden">
            <div style="font-size:14px;font-weight:500;margin-bottom:6px">Quên mật khẩu</div>
            <div style="font-size:13px;color:var(--text2);margin-bottom:12px">Nhập email đã đăng ký — chúng tôi sẽ gửi link đặt lại mật khẩu</div>
            <input type="email" id="forgot-email" class="form-input" placeholder="email@gmail.com"
              style="margin-bottom:10px" onkeydown="if(event.key==='Enter')doForgot()">
            <button class="btn btn-primary" style="width:100%;padding:12px" onclick="doForgot()" id="forgot-btn">Gửi link đặt lại →</button>
            <div style="text-align:center;margin-top:12px">
              <a href="#" style="font-size:13px;color:var(--text2)" onclick="showLogin();return false">← Quay lại đăng nhập</a>
            </div>
          </div>

          <!-- Forgot success -->
          <div id="forgot-sent-view" class="hidden" style="text-align:center;padding:16px 0">
            <div style="font-size:32px;margin-bottom:12px">📧</div>
            <div style="font-size:15px;font-weight:500;margin-bottom:6px">Email đã được gửi!</div>
            <div style="font-size:13px;color:var(--text2);margin-bottom:16px" id="forgot-sent-msg"></div>
            <a href="#" style="font-size:13px;color:var(--green)" onclick="showLogin();return false">← Quay lại đăng nhập</a>
          </div>
        </div>
      </div>
    </div>

    <!-- MEMBER PROFILE -->
    <div id="member-screen" class="hidden">

      <!-- Hero card -->
      <div class="member-hero" id="hero-card">
        <button class="member-logout" onclick="doLogout()">← Đổi SĐT</button>
        <div class="member-avatar" id="m-avatar">NA</div>
        <div class="member-name" id="m-name">Nguyễn Văn A</div>
        <div class="member-phone" id="m-phone">0901 234 567</div>
        <div class="sessions-showcase">
          <div class="sessions-big-num" id="m-sessions">0</div>
          <div class="sessions-big-info">
            <div class="sessions-big-label">buổi tập còn lại</div>
            <div class="sessions-big-sub" id="m-pkg-label">—</div>
            <div class="sessions-big-expiry" id="m-expiry"></div>
          </div>
        </div>
        <div class="hero-bar-track">
          <div class="hero-bar-fill" id="m-bar"></div>
        </div>
      </div>

      <!-- QR card -->
      <div class="qr-card">
        <div id="m-qr-mini" style="width:70px;height:70px;flex-shrink:0"></div>
        <div class="qr-card-info">
          <div class="qr-card-title">Mã QR cá nhân</div>
          <div class="qr-card-sub">Xuất trình khi check-in tại quầy</div>
        </div>
        <button class="btn btn-ghost" style="font-size:12px;padding:7px 12px" onclick="showQRBig()">Phóng to</button>
      </div>

      <!-- Buy more -->
      <div class="buy-more-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        Mua thêm buổi tập
      </div>

      <div id="buy-alert" class="alert hidden"></div>

      <div class="mini-pkg selected" onclick="selectBuyPkg(this,'pkg_10')" id="buy-pkg10">
        <div class="mini-pkg-left">
          <div class="mini-pkg-name">Gói 10 tặng 3</div>
          <div class="mini-pkg-desc">+13 buổi · Hiệu lực 1 tháng</div>
        </div>
        <div>
          <div class="mini-pkg-price"><?= number_format(PRICE_PKG_10) ?>đ</div>
          <div class="mini-pkg-sessions">13 buổi</div>
        </div>
      </div>

      <div class="mini-pkg" onclick="selectBuyPkg(this,'pkg_30')" id="buy-pkg30">
        <div class="mini-pkg-left">
          <div class="mini-pkg-name">Gói 30 tặng 10</div>
          <div class="mini-pkg-desc">+40 buổi · Hiệu lực 3 tháng</div>
        </div>
        <div>
          <div class="mini-pkg-price"><?= number_format(PRICE_PKG_30) ?>đ</div>
          <div class="mini-pkg-sessions">40 buổi</div>
        </div>
      </div>

      <div class="mini-pkg" onclick="selectBuyPkg(this,'single')" id="buy-single">
        <div class="mini-pkg-left">
          <div class="mini-pkg-name">Lẻ 1 buổi</div>
          <div class="mini-pkg-desc">Giờ hiện tại: <?= getCurrentSinglePrice()['slot'] ?></div>
        </div>
        <div>
          <div class="mini-pkg-price"><?= number_format(getCurrentSinglePrice()['price']) ?>đ</div>
          <div class="mini-pkg-sessions">1 buổi</div>
        </div>
      </div>

      <!-- Kids -->
      <div class="kids-inline">
        <div class="kids-inline-left">
          <div class="title">🎠 Khu vui chơi trẻ em 20K</div>
          <div class="sub"><?= number_format(PRICE_KIDS) ?>đ / trẻ · Không giới hạn giờ</div>
        </div>
        <div class="kids-counter">
          <button class="counter-btn" onclick="changeKids(-1)">−</button>
          <div class="kids-count-num" id="kids-num">0</div>
          <button class="counter-btn" onclick="changeKids(1)">+</button>
        </div>
      </div>

      <!-- Total + Pay button -->
      <div class="buy-total">
        <div>
          <div class="buy-total-lbl">Tổng thanh toán</div>
          <div style="font-size:12px;color:var(--text3)" id="buy-breakdown"></div>
        </div>
        <div class="buy-total-amt" id="buy-total-display">600.000đ</div>
      </div>
      <button class="btn btn-primary btn-full" onclick="proceedBuy()" style="font-size:15px;padding:13px;margin-bottom:28px">
        Thanh toán →
      </button>

      <!-- Check-in history -->
      <div class="history-card">
        <div class="section-title">Lịch sử check-in</div>
        <div id="m-history"></div>
      </div>

    </div><!-- /member-screen -->
  </main>
</div>

<!-- QR Big Modal -->
<div id="qr-big-modal" class="modal-overlay hidden" onclick="if(event.target===this)closeQRBig()">
  <div class="modal">
    <button class="modal-close" onclick="closeQRBig()">✕</button>
    <div class="modal-title">Thẻ QR của bạn</div>
    <div style="display:flex;justify-content:center;margin-bottom:14px">
      <div id="qr-big-display"></div>
    </div>
    <div style="text-align:center;font-size:13px;color:var(--text2)" id="qr-big-info"></div>
    <div class="modal-actions">
      <button class="btn btn-ghost" style="flex:1" onclick="downloadQR()">↓ Tải về</button>
      <button class="btn btn-primary" style="flex:1" onclick="closeQRBig()">Đóng</button>
    </div>
  </div>
</div>

<!-- Payment Modal -->
<div id="pay-modal" class="pay-overlay hidden">
  <div class="pay-box">
    <!-- QR View -->
    <div id="pay-checkout-view">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
        <div style="font-size:17px;font-weight:600">Thanh toán chuyển khoản</div>
        <button style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--text2)" onclick="cancelPay()">✕</button>
      </div>
      <div style="font-size:13px;color:var(--text2);margin-bottom:14px" id="pay-pkg-label"></div>
      <div class="qr-img-wrap">
        <img id="pay-qr" src="" alt="QR VietQR" onerror="this.style.display='none'">
        <div style="font-size:11px;color:var(--text3);margin-top:6px">Mở app ngân hàng · Quét mã QR · Đúng số tiền</div>
      </div>
      <table class="ck-table" style="width:100%;margin-bottom:8px">
        <tr><td>Ngân hàng</td><td><?= BANK_NAME ?></td></tr>
        <tr><td>Số tài khoản</td><td><?= BANK_ACCOUNT ?> <button class="copy-btn" onclick="copy('<?= BANK_ACCOUNT ?>')">📋</button></td></tr>
        <tr><td>Chủ TK</td><td><?= BANK_OWNER ?></td></tr>
        <tr><td>Số tiền</td><td id="pay-amount-lbl" style="color:var(--green);font-size:15px"></td></tr>
        <tr><td>Nội dung CK</td><td><strong id="pay-code" style="color:var(--green-dark)"></strong> <button class="copy-btn" onclick="copy(document.getElementById('pay-code').textContent)">📋</button></td></tr>
      </table>
      <div class="warn-note">⚠ <strong>Ghi đúng nội dung chuyển khoản</strong> để nhân viên duyệt nhanh.<br>Sau khi CK, nhân viên sẽ kiểm tra và duyệt trong vài phút.</div>
      <button class="btn btn-primary btn-full" onclick="markSent()" style="margin-bottom:8px">Tôi đã chuyển khoản xong ✓</button>
      <button class="btn btn-ghost btn-full" style="font-size:13px" onclick="cancelPay()">Huỷ đơn hàng</button>
    </div>
    <!-- Pending View -->
    <div id="pay-success-view" class="hidden" style="text-align:center;padding:16px 0">
      <div style="font-size:48px;margin-bottom:16px">⏳</div>
      <div style="font-size:18px;font-weight:600;margin-bottom:8px">Đang chờ xác nhận</div>
      <div style="font-size:13px;color:var(--text2);margin-bottom:16px;line-height:1.6">Nhân viên sẽ kiểm tra và duyệt đơn trong vài phút.<br>Buổi tập sẽ được cộng vào tài khoản sau khi duyệt.</div>
      <div style="background:var(--bg2);border-radius:var(--radius-lg);padding:14px;font-size:13px;margin-bottom:20px">
        <div style="color:var(--text2);margin-bottom:4px">Mã đơn hàng</div>
        <div style="font-size:18px;font-weight:600;font-family:'DM Mono',monospace;color:var(--green-dark)" id="pay-success-details"></div>
      </div>
      <button class="btn btn-primary btn-full" onclick="cancelPay()">Đóng</button>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="assets/js/app.js"></script>
<script>
const API_C = 'api/customers.php';
const API_O = 'api/orders.php';
const PRICES = { pkg_10: <?= PRICE_PKG_10 ?>, pkg_30: <?= PRICE_PKG_30 ?>, kids: <?= PRICE_KIDS ?> };
const SINGLE_PRICE = <?= getCurrentSinglePrice()['price'] ?>;
const SINGLE_SLOT  = '<?= getCurrentSinglePrice()['slot'] ?>';

let currentCustomer = null;
let selectedBuyPkg  = 'pkg_10';
let kidsCount = 0;
let pollTimer = null;
let currentOrderId = null;
let currentOrderData = null;

// ---- LOGIN ----
function fmtBigPhone(el) {
  let d = el.value.replace(/\D/g,'').slice(0,10);
  if (d.length>7) el.value = d.slice(0,4)+' '+d.slice(4,7)+' '+d.slice(7);
  else if (d.length>4) el.value = d.slice(0,4)+' '+d.slice(4);
  else el.value = d;
}

// Pre-fill phone from URL ?phone=xxx (after register redirect)
(function prefillPhone() {
  const p = new URLSearchParams(location.search).get('phone');
  if (p) {
    const d = p.replace(/\D/g,'');
    document.getElementById('login-phone').value = d.slice(0,4)+' '+d.slice(4,7)+' '+d.slice(7);
    document.getElementById('login-pw').focus();
  }
})();

function toggleLoginPw(btn) {
  const el = document.getElementById('login-pw');
  if (el.type==='password'){el.type='text';btn.textContent='🙈';}
  else{el.type='password';btn.textContent='👁';}
}

function showForgot() {
  hideAlert('login-alert');
  document.getElementById('login-form-view').classList.add('hidden');
  document.getElementById('forgot-form-view').classList.remove('hidden');
  document.getElementById('forgot-sent-view').classList.add('hidden');
}
function showLogin() {
  hideAlert('login-alert');
  document.getElementById('login-form-view').classList.remove('hidden');
  document.getElementById('forgot-form-view').classList.add('hidden');
  document.getElementById('forgot-sent-view').classList.add('hidden');
}

async function doLogin() {
  const phone = document.getElementById('login-phone').value.replace(/\D/g,'');
  const pw    = document.getElementById('login-pw').value;
  if (phone.length < 9) { showAlert('login-alert','Vui lòng nhập số điện thoại','warn'); return; }
  if (!pw)               { showAlert('login-alert','Vui lòng nhập mật khẩu','warn'); return; }
  hideAlert('login-alert');
  try {
    const res = await fetch('api/auth.php?action=login', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({phone, password: pw})
    });
    const json = await res.json();
    if (json.error) { showAlert('login-alert', json.error, 'error'); return; }
    currentCustomer = json.data;
    renderMember(json.data, json.checkins||[], json.expired);
  } catch(e) {
    showAlert('login-alert','Lỗi kết nối server','error');
  }
}

async function doForgot() {
  const email = document.getElementById('forgot-email').value.trim();
  if (!email) { showAlert('login-alert','Vui lòng nhập email','warn'); return; }
  const btn = document.getElementById('forgot-btn');
  btn.disabled = true; btn.textContent = 'Đang gửi...';
  try {
    const res = await fetch('api/auth.php?action=forgot', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({email})
    });
    const json = await res.json();
    if (json.error) {
      showAlert('login-alert', json.error, 'error');
      btn.disabled=false; btn.textContent='Gửi link đặt lại →';
      return;
    }
    document.getElementById('forgot-form-view').classList.add('hidden');
    document.getElementById('forgot-sent-view').classList.remove('hidden');
    document.getElementById('forgot-sent-msg').textContent = 'Link đặt lại mật khẩu đã gửi về ' + email + '. Kiểm tra hộp thư (kể cả Spam).';
  } catch(e) {
    showAlert('login-alert','Lỗi kết nối server','error');
    btn.disabled=false; btn.textContent='Gửi link đặt lại →';
  }
}

function doLogout() {
  currentCustomer = null;
  document.getElementById('login-screen').classList.remove('hidden');
  document.getElementById('member-screen').classList.add('hidden');
  document.getElementById('login-phone').value = '';
  document.getElementById('login-pw').value = '';
  showLogin();
}

// ---- RENDER MEMBER ----
function renderMember(c, checkins, expired) {
  document.getElementById('login-screen').classList.add('hidden');
  document.getElementById('member-screen').classList.remove('hidden');

  const initials = c.name.trim().split(' ').map(w=>w[0]).slice(-2).join('').toUpperCase();
  document.getElementById('m-avatar').textContent = initials;
  document.getElementById('m-name').textContent = c.name;
  const ph = c.phone.replace(/\D/g,'');
  document.getElementById('m-phone').textContent = ph.slice(0,4)+' '+ph.slice(4,7)+' '+ph.slice(7);

  updateSessionsDisplay(c, expired);
  renderQRMini(c.phone);
  renderHistory(checkins);
  updateBuyTotal();
}

function updateSessionsDisplay(c, expired) {
  const n = parseInt(c.sessions);
  const max = parseInt(c.max_sessions)||13;
  const numEl = document.getElementById('m-sessions');
  numEl.textContent = n;
  numEl.className = 'sessions-big-num' + (n===0?' zero':n<=3?' low':'');

  document.getElementById('m-pkg-label').textContent =
    c.pkg==='pkg_30' ? 'Gói 30 tặng 10' : c.pkg==='pkg_10' ? 'Gói 10 tặng 3' : 'Thẻ tập';

  const bar = document.getElementById('m-bar');
  bar.style.width = Math.max(0, Math.min(100, Math.round(n/max*100))) + '%';
  bar.className = 'hero-bar-fill' + (n===0?' zero':n<=3?' low':'');

  const expiryEl = document.getElementById('m-expiry');
  if (c.expires_at) {
    const d = new Date(c.expires_at);
    const dateStr = d.toLocaleDateString('vi-VN');
    if (expired) expiryEl.textContent = '⚠ Thẻ đã hết hạn ' + dateStr;
    else {
      const days = Math.ceil((d-new Date())/(1000*60*60*24));
      expiryEl.textContent = 'Hết hạn: ' + dateStr + (days<=7?' (còn '+days+' ngày)':'');
    }
  }
}

function renderQRMini(phone) {
  const el = document.getElementById('m-qr-mini');
  el.innerHTML = '';
  new QRCode(el, { text:'WP-'+phone, width:70, height:70, colorDark:'#085041', colorLight:'#fff', correctLevel: QRCode.CorrectLevel.M });
}

function renderHistory(checkins) {
  const el = document.getElementById('m-history');
  if (!checkins.length) { el.innerHTML='<div class="no-history">Chưa có lịch sử check-in</div>'; return; }
  el.innerHTML = checkins.slice(0,8).map(ci => `
    <div class="history-item">
      <div class="history-dot"></div>
      <div class="history-info">
        <span class="history-date">${formatDateTime(ci.checked_in_at)}</span>
        <span class="history-detail">${ci.sessions_before} → ${ci.sessions_after} buổi</span>
      </div>
    </div>`).join('');
}

// ---- BUY MORE ----
function selectBuyPkg(el, pkg) {
  document.querySelectorAll('.mini-pkg').forEach(x=>x.classList.remove('selected'));
  el.classList.add('selected');
  selectedBuyPkg = pkg;
  updateBuyTotal();
}

function changeKids(d) {
  kidsCount = Math.max(0, kidsCount+d);
  document.getElementById('kids-num').textContent = kidsCount;
  updateBuyTotal();
}

function updateBuyTotal() {
  let base=0, breakdown='';
  if (selectedBuyPkg==='pkg_10') { base=PRICES.pkg_10; breakdown='Gói 10+3 = '+PRICES.pkg_10.toLocaleString('vi-VN')+'đ'; }
  else if (selectedBuyPkg==='pkg_30') { base=PRICES.pkg_30; breakdown='Gói 30+10 = '+PRICES.pkg_30.toLocaleString('vi-VN')+'đ'; }
  else if (selectedBuyPkg==='single') { base=SINGLE_PRICE; breakdown='Lẻ 1 buổi ('+SINGLE_SLOT+') = '+SINGLE_PRICE.toLocaleString('vi-VN')+'đ'; }
  const kidsAmt = kidsCount*PRICES.kids;
  if (kidsCount>0) breakdown += (breakdown?' + ':'')+kidsCount+' trẻ = '+kidsAmt.toLocaleString('vi-VN')+'đ';
  document.getElementById('buy-total-display').textContent = (base+kidsAmt).toLocaleString('vi-VN')+'đ';
  document.getElementById('buy-breakdown').textContent = breakdown;
}

updateBuyTotal();

async function proceedBuy() {
  if (!currentCustomer) return;
  hideAlert('buy-alert');
  try {
    const res = await fetch(`${API_O}?action=create`, {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ phone: currentCustomer.phone, name: currentCustomer.name, pkg_type: selectedBuyPkg, kids_count: kidsCount })
    });
    const json = await res.json();
    if (json.error) { showAlert('buy-alert', json.error, 'error'); return; }
    currentOrderId = json.order_id;
    currentOrderData = json;
    showPayModal(json);
  } catch(e) { showAlert('buy-alert','Lỗi kết nối server','error'); }
}

function showPayModal(data) {
  currentOrderData = data;
  document.getElementById('pay-qr').src = data.qr_url;
  document.getElementById('pay-code').textContent = data.order_code;
  document.getElementById('pay-amount-lbl').textContent = parseInt(data.amount).toLocaleString('vi-VN')+'đ';
  let lbl = data.pkg_label;
  if (data.kids_count>0) lbl += ' + '+data.kids_count+' trẻ em';
  document.getElementById('pay-pkg-label').textContent = lbl;
  document.getElementById('pay-checkout-view').classList.remove('hidden');
  document.getElementById('pay-success-view').classList.add('hidden');
  document.getElementById('pay-modal').classList.remove('hidden');
}

function markSent() {
  document.getElementById('pay-checkout-view').classList.add('hidden');
  document.getElementById('pay-success-view').classList.remove('hidden');
  document.getElementById('pay-success-details').textContent = currentOrderData?.order_code || '';
}

function cancelPay() { document.getElementById('pay-modal').classList.add('hidden'); }


// ---- QR BIG ----
function showQRBig() {
  if (!currentCustomer) return;
  const el = document.getElementById('qr-big-display');
  el.innerHTML = '';
  new QRCode(el, { text:'WP-'+currentCustomer.phone, width:220, height:220, colorDark:'#085041', colorLight:'#fff', correctLevel: QRCode.CorrectLevel.M });
  const ph = currentCustomer.phone.replace(/\D/g,'');
  document.getElementById('qr-big-info').textContent = currentCustomer.name + ' · ' + ph.slice(0,4)+' '+ph.slice(4,7)+' '+ph.slice(7);
  document.getElementById('qr-big-modal').classList.remove('hidden');
}
function closeQRBig() { document.getElementById('qr-big-modal').classList.add('hidden'); }
function downloadQR() {
  const canvas = document.querySelector('#qr-big-display canvas');
  if (!canvas) return;
  const a = document.createElement('a');
  a.download = 'wonder-qr-'+currentCustomer.phone+'.png';
  a.href = canvas.toDataURL('image/png');
  a.click();
}

function copy(t) { navigator.clipboard.writeText(t).catch(()=>{}); }
</script>
</body>
</html>
