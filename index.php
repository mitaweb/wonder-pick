<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wonder Pickleball — Lễ Tân</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="layout">
  <?php include __DIR__ . '/includes/nav.php'; ?>

  <main class="main-content">
    <div class="page-header">
      <h1>Check-in</h1>
      <p class="subtitle">Nhập số điện thoại hoặc quét QR để check-in khách</p>
    </div>

    <div class="search-card">
      <div class="input-row">
        <div class="phone-input-wrap">
          <span class="phone-prefix">📱</span>
          <input type="tel" id="phone-input" class="phone-input" placeholder="Nhập số điện thoại..." maxlength="12" autocomplete="off" autofocus>
        </div>
        <button class="btn btn-primary" onclick="searchCustomer()">Tìm kiếm</button>
      </div>
      <div class="qr-row">
        <span class="qr-hint">hoặc</span>
        <button class="btn btn-ghost" onclick="document.getElementById('qr-input').focus()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM17 17h3v3h-3zM14 20h3"/></svg>
          Nhập mã QR
        </button>
        <input type="text" id="qr-input" class="qr-hidden-input" placeholder="WP-0901234567" oninput="handleQRInput(this)">
      </div>
      <div id="search-alert" class="alert hidden"></div>
    </div>

    <div id="customer-panel" class="hidden">
      <div class="customer-hero">
        <div class="customer-avatar" id="cust-avatar">NA</div>
        <div class="customer-info">
          <h2 id="cust-name">Nguyễn Văn A</h2>
          <p id="cust-phone">0901 234 567</p>
          <p id="cust-joined" class="meta">Tham gia: —</p>
        </div>
        <div class="session-counter">
          <div class="session-num" id="cust-sessions">0</div>
          <div class="session-lbl">buổi còn lại</div>
        </div>
      </div>

      <div class="session-bar-wrap">
        <div class="session-bar-track">
          <div class="session-bar-fill" id="session-bar"></div>
        </div>
        <span class="session-bar-label" id="session-bar-label"></span>
      </div>

      <div class="action-row">
        <button class="btn btn-checkin" id="checkin-btn" onclick="doCheckin()">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Check In — trừ 1 buổi
        </button>
        <button class="btn btn-ghost" onclick="showQRModal()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM17 17h3v3h-3zM14 20h3"/></svg>
          Xem QR
        </button>
      </div>

      <div id="checkin-alert" class="alert hidden"></div>

      <div class="history-section">
        <h3>Lịch sử check-in gần đây</h3>
        <div id="history-list" class="history-list"></div>
      </div>
    </div>
  </main>
</div>

<!-- QR Modal -->
<div id="qr-modal" class="modal-overlay hidden">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('qr-modal')">✕</button>
    <div class="modal-title">Thẻ QR khách hàng</div>
    <div class="qr-display" id="modal-qr"></div>
    <div id="modal-cust-info" style="text-align:center;margin-top:12px"></div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="downloadQR()">↓ Tải QR</button>
      <button class="btn btn-primary" onclick="closeModal('qr-modal')">Đóng</button>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="assets/js/app.js"></script>
<script>
const API_BASE = 'api/customers.php';
let currentCustomer = null;

document.getElementById('phone-input').addEventListener('keydown', e => {
  if (e.key === 'Enter') searchCustomer();
});

function formatPhoneDisplay(p) {
  p = p.replace(/\D/g,'');
  if (p.length === 10) return p.slice(0,4)+' '+p.slice(4,7)+' '+p.slice(7);
  return p;
}

function getInitials(name) {
  return name.trim().split(' ').map(w=>w[0]).slice(-2).join('').toUpperCase();
}

async function searchCustomer() {
  const raw = document.getElementById('phone-input').value.replace(/\D/g,'');
  if (raw.length < 9) { showAlert('search-alert', 'Vui lòng nhập đủ số điện thoại', 'warn'); return; }
  hideAlert('search-alert');

  try {
    const res = await fetch(`${API_BASE}?action=get&phone=${raw}`);
    const json = await res.json();
    if (!json.data) {
      showAlert('search-alert', 'Không tìm thấy khách hàng. Vui lòng đăng ký tại trang Đăng Ký.', 'error');
      document.getElementById('customer-panel').classList.add('hidden');
      return;
    }
    currentCustomer = json.data;
    renderCustomer(json.data, json.checkins || []);
  } catch(e) {
    showAlert('search-alert', 'Lỗi kết nối. Kiểm tra lại server.', 'error');
  }
}

function renderCustomer(c, checkins) {
  document.getElementById('customer-panel').classList.remove('hidden');
  document.getElementById('cust-avatar').textContent = getInitials(c.name);
  document.getElementById('cust-name').textContent = c.name;
  document.getElementById('cust-phone').textContent = formatPhoneDisplay(c.phone);
  document.getElementById('cust-joined').textContent = 'Tham gia: ' + formatDate(c.created_at);
  updateSessionDisplay(c);
  renderHistory(checkins);
  hideAlert('checkin-alert');
  document.getElementById('customer-panel').scrollIntoView({behavior:'smooth'});
}

function updateSessionDisplay(c) {
  const n = parseInt(c.sessions);
  const max = parseInt(c.max_sessions) || 13;
  document.getElementById('cust-sessions').textContent = n;
  document.getElementById('cust-sessions').className = 'session-num' + (n===0?' zero':n<=3?' low':'');
  const pct = Math.max(0, Math.min(100, Math.round(n/max*100)));
  const bar = document.getElementById('session-bar');
  bar.style.width = pct + '%';
  bar.className = 'session-bar-fill' + (n===0?' zero':n<=3?' low':n<=6?' mid':'');
  document.getElementById('session-bar-label').textContent = n + '/' + max + ' buổi';
  const btn = document.getElementById('checkin-btn');
  btn.disabled = n <= 0;
  btn.style.opacity = n <= 0 ? '.45' : '1';
}

async function doCheckin() {
  if (!currentCustomer || parseInt(currentCustomer.sessions) <= 0) return;
  const btn = document.getElementById('checkin-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> Đang xử lý...';

  try {
    const res = await fetch(`${API_BASE}?action=checkin`, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({phone: currentCustomer.phone})
    });
    const json = await res.json();
    if (json.error) { showAlert('checkin-alert', json.error, 'error'); }
    else {
      currentCustomer = json.data;
      updateSessionDisplay(json.data);
      showAlert('checkin-alert', `✓ Check-in thành công! Còn ${json.sessions_after} buổi.`, 'success');
      // Refresh history
      const res2 = await fetch(`${API_BASE}?action=get&phone=${currentCustomer.phone}`);
      const json2 = await res2.json();
      if (json2.checkins) renderHistory(json2.checkins);
    }
  } catch(e) {
    showAlert('checkin-alert', 'Lỗi kết nối server', 'error');
  }
  btn.disabled = parseInt(currentCustomer.sessions) <= 0;
  btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Check In — trừ 1 buổi';
}

function renderHistory(checkins) {
  const el = document.getElementById('history-list');
  if (!checkins.length) { el.innerHTML = '<div class="no-history">Chưa có lịch sử</div>'; return; }
  el.innerHTML = checkins.slice(0,8).map(ci => `
    <div class="history-item">
      <div class="history-dot"></div>
      <div class="history-info">
        <span class="history-date">${formatDateTime(ci.checked_in_at)}</span>
        <span class="history-detail">${ci.sessions_before} → ${ci.sessions_after} buổi</span>
      </div>
    </div>`).join('');
}

function handleQRInput(el) {
  const v = el.value.trim();
  if (v.startsWith('WP-') && v.length > 5) {
    const phone = v.replace('WP-','');
    document.getElementById('phone-input').value = formatPhoneDisplay(phone);
    el.value = '';
    searchCustomer();
  }
}

let qrInstance = null;
function showQRModal() {
  if (!currentCustomer) return;
  const el = document.getElementById('modal-qr');
  el.innerHTML = '';
  qrInstance = new QRCode(el, {
    text: 'WP-' + currentCustomer.phone,
    width: 200, height: 200,
    colorDark: '#085041', colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M
  });
  document.getElementById('modal-cust-info').innerHTML =
    `<strong>${currentCustomer.name}</strong><br><span style="color:#666">${formatPhoneDisplay(currentCustomer.phone)}</span><br><span style="color:#1D9E75">${currentCustomer.sessions} buổi còn lại</span>`;
  document.getElementById('qr-modal').classList.remove('hidden');
}

function downloadQR() {
  const canvas = document.querySelector('#modal-qr canvas');
  if (!canvas) return;
  const a = document.createElement('a');
  a.download = 'wonder-qr-' + (currentCustomer?.phone||'') + '.png';
  a.href = canvas.toDataURL('image/png');
  a.click();
}

function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>
</body>
</html>
