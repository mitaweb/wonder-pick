<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mua Gói Tập — Wonder Pickleball</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
.pricing-hero{background:linear-gradient(135deg,#085041 0%,#1D9E75 100%);border-radius:var(--radius-xl);padding:28px 24px;margin-bottom:24px;color:white}
.pricing-hero h2{font-size:22px;font-weight:600;margin-bottom:4px}
.pricing-hero p{font-size:14px;opacity:.8}
.slot-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:20px}
.slot-card{background:var(--bg);border:0.5px solid var(--border2);border-radius:var(--radius-lg);padding:14px 10px;text-align:center}
.slot-card.active{border-color:var(--green);border-width:2px;background:var(--green-light)}
.slot-time{font-size:12px;font-weight:500;color:var(--text2);margin-bottom:4px}
.slot-price{font-size:19px;font-weight:600;color:var(--green)}
.slot-label{font-size:11px;color:var(--text3);margin-top:2px}
.current-badge{display:inline-block;background:var(--green);color:white;font-size:10px;font-weight:600;padding:2px 7px;border-radius:99px;margin-bottom:4px}
.pkg-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
.kids-row{display:flex;align-items:center;gap:12px;background:var(--bg2);border:0.5px solid var(--border);border-radius:var(--radius-lg);padding:14px;margin-bottom:16px}
.kids-counter{display:flex;align-items:center;gap:10px;margin-left:auto}
.kids-count-num{font-size:20px;font-weight:600;min-width:28px;text-align:center}
.counter-btn{width:32px;height:32px;border-radius:50%;border:0.5px solid var(--border2);background:var(--bg);cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;transition:all .15s}
.counter-btn:hover{background:var(--green);color:white;border-color:var(--green)}
.total-bar{background:var(--bg2);border-radius:var(--radius-lg);padding:16px 20px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center}
.total-label{font-size:14px;color:var(--text2)}
.total-amount{font-size:28px;font-weight:600;color:var(--green-dark)}
/* Payment modal */
.pay-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;display:flex;align-items:center;justify-content:center;padding:16px}
.pay-box{background:var(--bg);border-radius:var(--radius-xl);padding:24px;max-width:440px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25)}
.qr-img-wrap{background:white;border:1px solid var(--border);border-radius:var(--radius-lg);padding:10px;text-align:center;margin-bottom:14px}
.qr-img-wrap img{max-width:240px;width:100%;border-radius:8px}
.ck-table td{padding:5px 0;font-size:13px;vertical-align:top}
.ck-table td:first-child{color:var(--text2);width:110px;white-space:nowrap}
.ck-table td:last-child{font-weight:500}
.copy-btn{background:none;border:none;cursor:pointer;color:var(--green);font-size:12px;margin-left:4px;font-family:inherit;padding:0}
.warn-note{background:var(--amber-light);border:0.5px solid #FAC775;border-radius:var(--radius);padding:10px 14px;font-size:12px;color:#633806;margin:10px 0;line-height:1.6}
.pending-badge{display:inline-flex;align-items:center;gap:6px;background:var(--amber-light);color:#633806;border:0.5px solid #FAC775;border-radius:99px;padding:6px 14px;font-size:13px;font-weight:500;margin:12px 0}
.success-ring{width:64px;height:64px;background:var(--green);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;animation:pop .4s cubic-bezier(.175,.885,.32,1.275)}
.success-ring svg{width:32px;height:32px;stroke:white;stroke-width:3;fill:none}
@keyframes pop{from{transform:scale(0)}to{transform:scale(1)}}
</style>
</head>
<body>
<div class="layout">
  <?php include __DIR__ . '/includes/nav.php'; ?>
  <main class="main-content">

    <div class="pricing-hero">
      <h2>Mua gói tập</h2>
      <p>Chọn gói, chuyển khoản QR — nhân viên duyệt trong vài phút</p>
    </div>

    <!-- Bảng giá Social -->
    <div class="form-card">
      <div class="section-title">Giá lẻ theo khung giờ</div>
      <div class="slot-grid">
        <div class="slot-card" id="slot-0"><div class="slot-time">8h – 11h</div><div class="slot-price"><?= number_format(getPrice('price_social_morning')) ?>đ</div><div class="slot-label">Sáng</div></div>
        <div class="slot-card" id="slot-1"><div class="slot-time">11h – 16h</div><div class="slot-price"><?= number_format(getPrice('price_social_noon')) ?>đ</div><div class="slot-label">Trưa</div></div>
        <div class="slot-card" id="slot-2"><div class="slot-time">16h – 22h</div><div class="slot-price"><?= number_format(getPrice('price_social_evening')) ?>đ</div><div class="slot-label">Chiều/Tối</div></div>
      </div>
      <div id="current-slot-info" style="font-size:13px;color:var(--text2);text-align:center;margin-top:-8px"></div>
    </div>

    <!-- Chưa đăng nhập -->
    <div id="buy-login-required" class="form-card hidden" style="text-align:center;padding:32px">
      <div style="font-size:40px;margin-bottom:12px">🔒</div>
      <div style="font-size:17px;font-weight:600;margin-bottom:8px">Vui lòng đăng nhập</div>
      <div style="font-size:14px;color:var(--text2);margin-bottom:20px">Bạn cần đăng nhập để mua gói tập</div>
      <a href="member.php" class="btn btn-primary btn-full" style="display:flex">Đăng nhập →</a>
      <div style="margin-top:12px;font-size:13px;color:var(--text3)">Chưa có tài khoản? <a href="register.php" style="color:var(--green)">Đăng ký ngay</a></div>
    </div>

    <div id="buy-form-card" class="form-card hidden">
      <div class="section-title">Chọn gói tập</div>
      <div id="reg-alert" class="alert hidden"></div>

      <!-- Thông tin đã đăng nhập -->
      <div id="buy-member-info" style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--green-light);border-radius:var(--radius-lg);margin-bottom:16px">
        <div style="width:38px;height:38px;background:var(--green);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:600;font-size:15px" id="buy-avatar">?</div>
        <div style="flex:1">
          <div style="font-weight:500;font-size:14px" id="buy-member-name">—</div>
          <div style="font-size:12px;color:var(--green-dark);font-family:'DM Mono',monospace" id="buy-member-phone">—</div>
        </div>
        <a href="member.php" style="font-size:12px;color:var(--green)">Đổi →</a>
      </div>
      <input type="hidden" id="buy-phone">
      <input type="hidden" id="buy-name">

      <div class="pkg-row">
        <div class="pkg-card selected" onclick="selectPkg(this,'pkg_10')" id="opt-pkg10">
          <div class="pkg-badge">Phổ biến</div>
          <div class="pkg-sessions">13</div>
          <div class="pkg-name">Gói 10 tặng 3</div>
          <div class="pkg-desc">HSD: 1 tháng</div>
          <div style="margin-top:8px;font-size:16px;font-weight:600;color:var(--green-dark)"><?= number_format(getPrice('price_pkg_10')) ?>đ</div>
        </div>
        <div class="pkg-card" onclick="selectPkg(this,'pkg_30')" id="opt-pkg30">
          <div class="pkg-sessions">40</div>
          <div class="pkg-name">Gói 30 tặng 10</div>
          <div class="pkg-desc">HSD: 3 tháng</div>
          <div style="margin-top:8px;font-size:16px;font-weight:600;color:var(--green-dark)"><?= number_format(getPrice('price_pkg_30')) ?>đ</div>
        </div>
      </div>
      <div class="pkg-row" style="grid-template-columns:1fr">
        <div class="pkg-card" onclick="selectPkg(this,'single')" id="opt-single">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div><div class="pkg-name">Lẻ 1 buổi</div><div class="pkg-desc">Giờ hiện tại: <?= getCurrentSinglePriceDynamic()['slot'] ?></div></div>
            <div style="font-size:22px;font-weight:600;color:var(--green)"><?= number_format(getCurrentSinglePriceDynamic()['price']) ?>đ</div>
          </div>
        </div>
      </div>

      <!-- Kids only -->
      <div class="pkg-row" style="grid-template-columns:1fr">
        <div class="pkg-card" onclick="selectPkg(this,'kids')" id="opt-kids">
          <div style="display:flex;justify-content:space-between;align-items:center">
            <div><div class="pkg-name">🎠 Khu vui chơi trẻ em</div><div class="pkg-desc">Không giới hạn giờ · Chọn số trẻ bên dưới</div></div>
            <div style="font-size:22px;font-weight:600;color:var(--green)"><?= number_format(getPrice('price_kids')) ?>đ/trẻ</div>
          </div>
        </div>
      </div>

      <!-- Trẻ em counter — chỉ hiện khi chọn gói kids hoặc muốn thêm trẻ em -->
      <div class="kids-row" id="kids-row" style="display:none">
        <div>
          <div style="font-weight:500;font-size:14px">Số trẻ em</div>
          <div style="font-size:12px;color:var(--text2);margin-top:2px"><?= number_format(getPrice('price_kids')) ?>đ / trẻ</div>
        </div>
        <div class="kids-counter">
          <button class="counter-btn" onclick="changeKids(-1)">−</button>
          <div class="kids-count-num" id="kids-count">1</div>
          <button class="counter-btn" onclick="changeKids(1)">+</button>
        </div>
      </div>
      <!-- Nút thêm trẻ em cho gói có sẵn -->
      <div id="kids-addon-row" style="display:none;margin-bottom:12px">
        <button class="btn btn-ghost btn-full" style="font-size:13px;justify-content:center" onclick="toggleKidsAddon()">
          🎠 <span id="kids-addon-label">+ Thêm trẻ em vào khu vui chơi</span>
        </button>
      </div>

      <div class="total-bar">
        <div>
          <div class="total-label">Tổng thanh toán</div>
          <div style="font-size:12px;color:var(--text3)" id="total-breakdown"></div>
        </div>
        <div class="total-amount" id="total-display">600.000đ</div>
      </div>
      <button class="btn btn-primary btn-full" onclick="proceedToPayment()" style="font-size:16px;padding:14px">Tạo đơn & lấy QR →</button>
    </div><!-- /buy-form-card -->

  </main>
</div>

<!-- Payment Modal -->
<div id="pay-modal" class="pay-overlay hidden">
  <div class="pay-box">

    <!-- QR View -->
    <div id="pay-qr-view">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
        <div style="font-size:17px;font-weight:600">Thanh toán chuyển khoản</div>
        <button style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--text2)" onclick="closePayModal()">✕</button>
      </div>
      <div style="font-size:13px;color:var(--text2);margin-bottom:14px" id="pay-pkg-label"></div>

      <div class="qr-img-wrap">
        <img id="pay-qr-img" src="" alt="QR VietQR" style="display:none" onload="this.style.display='block';document.getElementById('qr-fallback').style.display='none'">
        <div id="qr-fallback" style="padding:20px;font-size:13px;color:var(--text2)">QR đang tải...</div>
        <div style="font-size:11px;color:var(--text3);margin-top:6px">Mở app ngân hàng · Quét mã QR · Chuyển đúng số tiền</div>
      </div>

      <table class="ck-table" style="width:100%;margin-bottom:8px">
        <tr><td>Ngân hàng</td><td id="pay-bank-name"></td></tr>
        <tr><td>Số tài khoản</td><td><span id="pay-bank-account"></span> <button class="copy-btn" onclick="copy(document.getElementById('pay-bank-account').textContent)">📋</button></td></tr>
        <tr><td>Chủ TK</td><td id="pay-bank-owner"></td></tr>
        <tr><td>Số tiền</td><td id="pay-amount-lbl" style="color:var(--green);font-size:15px"></td></tr>
        <tr><td>Nội dung CK</td><td><strong id="pay-code" style="color:var(--green-dark)"></strong> <button class="copy-btn" onclick="copy(document.getElementById('pay-code').textContent)">📋</button></td></tr>
      </table>

      <div class="warn-note">
        ⚠ <strong>Ghi đúng nội dung chuyển khoản</strong> để nhân viên duyệt nhanh hơn.<br>
        Sau khi chuyển khoản, nhân viên sẽ kiểm tra và duyệt trong vài phút.
      </div>

      <button class="btn btn-primary btn-full" onclick="markAsSent()" style="margin-bottom:8px">Tôi đã chuyển khoản xong ✓</button>
      <button class="btn btn-ghost btn-full" style="font-size:13px" onclick="closePayModal()">Huỷ đơn hàng</button>
    </div>

    <!-- Pending View (sau khi bấm đã chuyển) -->
    <div id="pay-pending-view" class="hidden" style="text-align:center;padding:16px 0">
      <div style="font-size:48px;margin-bottom:16px">⏳</div>
      <div style="font-size:18px;font-weight:600;margin-bottom:8px">Đang chờ xác nhận</div>
      <div style="font-size:13px;color:var(--text2);margin-bottom:16px;line-height:1.6">
        Nhân viên sẽ kiểm tra biến động số dư và duyệt đơn trong vài phút.<br>
        Bạn sẽ nhận được thông báo khi buổi tập được cộng vào tài khoản.
      </div>
      <div style="background:var(--bg2);border-radius:var(--radius-lg);padding:14px;font-size:13px;margin-bottom:20px;text-align:left">
        <div style="color:var(--text2);margin-bottom:4px">Mã đơn hàng</div>
        <div style="font-size:18px;font-weight:600;font-family:'DM Mono',monospace;color:var(--green-dark)" id="pending-code"></div>
      </div>
      <button class="btn btn-primary btn-full" onclick="closePayModal()">Đóng</button>
    </div>

  </div>
</div>

<script src="assets/js/app.js"></script>
<script>
const PRICES = { pkg_10:<?= getPrice('price_pkg_10') ?>, pkg_30:<?= getPrice('price_pkg_30') ?>, kids:<?= getPrice('price_kids') ?> };
const SINGLE_PRICE = <?= getCurrentSinglePriceDynamic()['price'] ?>;
const SINGLE_SLOT  = '<?= getCurrentSinglePriceDynamic()['slot'] ?>';
let selectedPkg = 'pkg_10';
let kidsCount = 0;
let currentOrderCode = '';

// Highlight khung giờ hiện tại
(function() {
  const h = new Date().getHours();
  let i = -1;
  if (h>=8&&h<11) i=0; else if (h>=11&&h<16) i=1; else if (h>=16&&h<22) i=2;
  if (i>=0) {
    const c = document.getElementById('slot-'+i);
    c.classList.add('active');
    const badge = document.createElement('div'); badge.className='current-badge'; badge.textContent='Hiện tại';
    c.insertBefore(badge, c.firstChild);
    document.getElementById('current-slot-info').textContent = 'Khung giờ hiện tại: '+SINGLE_SLOT+' — '+SINGLE_PRICE.toLocaleString('vi-VN')+'đ/buổi';
  }
})();

function fmtPhone(el){let d=el.value.replace(/\D/g,'').slice(0,10);if(d.length>7)el.value=d.slice(0,4)+' '+d.slice(4,7)+' '+d.slice(7);else if(d.length>4)el.value=d.slice(0,4)+' '+d.slice(4);else el.value=d;}

async function lookupName() {
  const p = document.getElementById('buy-phone').value.replace(/\D/g,'');
  if (p.length<9) return;
  try {
    const r = await fetch('api/customers.php?action=get&phone='+p);
    const j = await r.json();
    if (j.data?.name) { document.getElementById('buy-name').value=j.data.name; document.getElementById('buy-name').style.borderColor='var(--green)'; }
  } catch(e){}
}

function changeKids(d){
  kidsCount = Math.max(selectedPkg==='kids'?1:0, kidsCount+d);
  document.getElementById('kids-count').textContent=kidsCount;
  updateTotal();
}

let kidsAddonOpen = false;
function toggleKidsAddon() {
  kidsAddonOpen = !kidsAddonOpen;
  if (kidsAddonOpen) {
    if (kidsCount<1){kidsCount=1;document.getElementById('kids-count').textContent=1;}
    document.getElementById('kids-row').style.display='flex';
    document.getElementById('kids-addon-label').textContent='✓ Đã thêm — bấm để bỏ';
  } else {
    kidsCount=0; document.getElementById('kids-count').textContent=0;
    document.getElementById('kids-row').style.display='none';
    document.getElementById('kids-addon-label').textContent='+ Thêm trẻ em vào khu vui chơi';
  }
  updateTotal();
}

function selectPkg(el,type){
  document.querySelectorAll('.pkg-card').forEach(x=>x.classList.remove('selected'));
  el.classList.add('selected'); selectedPkg=type;
  const kidsRow = document.getElementById('kids-row');
  const addonRow = document.getElementById('kids-addon-row');
  if(type==='kids'){
    kidsRow.style.display='flex'; addonRow.style.display='none';
    kidsAddonOpen=false;
    if(kidsCount<1){kidsCount=1;document.getElementById('kids-count').textContent=1;}
  } else {
    addonRow.style.display='block';
    if(!kidsAddonOpen){kidsRow.style.display='none'; kidsCount=0; document.getElementById('kids-count').textContent=0;}
  }
  updateTotal();
}
function updateTotal(){
  let base=0,bd='';
  if(selectedPkg==='pkg_10'){base=PRICES.pkg_10;bd='Gói 10+3 = '+PRICES.pkg_10.toLocaleString('vi-VN')+'đ';}
  else if(selectedPkg==='pkg_30'){base=PRICES.pkg_30;bd='Gói 30+10 = '+PRICES.pkg_30.toLocaleString('vi-VN')+'đ';}
  else if(selectedPkg==='single'){base=SINGLE_PRICE;bd='Lẻ 1 buổi ('+SINGLE_SLOT+') = '+SINGLE_PRICE.toLocaleString('vi-VN')+'đ';}
  else if(selectedPkg==='kids'){base=0;bd='';}
  const ka=kidsCount*PRICES.kids;
  if(kidsCount>0) bd+=(bd?' + ':'')+kidsCount+' trẻ = '+ka.toLocaleString('vi-VN')+'đ';
  document.getElementById('total-display').textContent=(base+ka).toLocaleString('vi-VN')+'đ';
  document.getElementById('total-breakdown').textContent=bd;
}
updateTotal();

(async function initBuyPage() {
  const saved = localStorage.getItem('wp_member');
  if (!saved) {
    document.getElementById('buy-login-required').classList.remove('hidden');
    return;
  }
  try {
    const {phone, password} = JSON.parse(saved);
    const res = await fetch('api/auth.php?action=login', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({phone, password})
    });
    const json = await res.json();
    if (json.error) {
      localStorage.removeItem('wp_member');
      document.getElementById('buy-login-required').classList.remove('hidden');
      return;
    }
    const d = json.data;
    document.getElementById('buy-phone').value = d.phone;
    document.getElementById('buy-name').value = d.name;
    document.getElementById('buy-avatar').textContent = d.name.trim().split(' ').map(w=>w[0]).filter(Boolean).slice(-2).join('').toUpperCase();
    document.getElementById('buy-member-name').textContent = d.name;
    const ph = d.phone.replace(/\D/g,'');
    document.getElementById('buy-member-phone').textContent = ph.slice(0,4)+' '+ph.slice(4,7)+' '+ph.slice(7);
    document.getElementById('buy-form-card').classList.remove('hidden');
  } catch(e) {
    document.getElementById('buy-login-required').classList.remove('hidden');
  }
})();

async function proceedToPayment() {
  const phone = document.getElementById('buy-phone').value.replace(/\D/g,'');
  const name  = document.getElementById('buy-name').value.trim();
  if (phone.length<9) { showAlert('reg-alert','Vui lòng nhập số điện thoại','warn'); return; }
  if (selectedPkg==='kids' && kidsCount<=0) { showAlert('reg-alert','Vui lòng chọn số trẻ em','warn'); return; }
  hideAlert('reg-alert');
  try {
    const res = await fetch('api/orders.php?action=create', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({phone, name, pkg_type:selectedPkg, kids_count:kidsCount})
    });
    const json = await res.json();
    if (json.error) { showAlert('reg-alert',json.error,'error'); return; }
    showPayModal(json);
  } catch(e) { showAlert('reg-alert','Lỗi kết nối server','error'); }
}

function showPayModal(data) {
  currentOrderCode = data.order_code;
  document.getElementById('pay-qr-img').style.display = 'none';
  document.getElementById('qr-fallback').style.display = 'block';
  document.getElementById('pay-qr-img').src = data.qr_url;
  document.getElementById('pay-code').textContent = data.order_code;
  document.getElementById('pay-amount-lbl').textContent = parseInt(data.amount).toLocaleString('vi-VN')+'đ';
  document.getElementById('pay-pkg-label').textContent = data.pkg_label;
  document.getElementById('pay-bank-name').textContent = data.bank_name || '';
  document.getElementById('pay-bank-account').textContent = data.bank_account || '';
  document.getElementById('pay-bank-owner').textContent = data.bank_owner || '';
  document.getElementById('pay-qr-view').classList.remove('hidden');
  document.getElementById('pay-pending-view').classList.add('hidden');
  document.getElementById('pay-modal').classList.remove('hidden');
}

function markAsSent() {
  document.getElementById('pending-code').textContent = currentOrderCode;
  document.getElementById('pay-qr-view').classList.add('hidden');
  document.getElementById('pay-pending-view').classList.remove('hidden');
}

function closePayModal() { document.getElementById('pay-modal').classList.add('hidden'); }
function copy(t) { navigator.clipboard.writeText(t).then(()=>{}).catch(()=>{}); }
</script>
</body>
</html>
