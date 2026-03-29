<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đặt Lại Mật Khẩu — Wonder Pickleball</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
.center-wrap { min-height: 80vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px 20px; }
.reset-box { width: 100%; max-width: 400px; }
.reset-logo { width: 60px; height: 60px; background: var(--green); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 28px; }
.reset-title { font-size: 22px; font-weight: 600; text-align: center; margin-bottom: 6px; }
.reset-sub { font-size: 14px; color: var(--text2); text-align: center; margin-bottom: 28px; }
.pw-wrap { position: relative; }
.pw-wrap input { padding-right: 44px; }
.pw-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text2); font-size: 16px; padding: 4px; }
.strength-bar { height: 4px; border-radius: 99px; margin-top: 6px; background: var(--bg3); overflow: hidden; }
.strength-fill { height: 100%; border-radius: 99px; transition: width .3s, background .3s; }
.strength-label { font-size: 11px; margin-top: 4px; }
</style>
</head>
<body>
<div class="layout">
  <?php include __DIR__ . '/includes/nav.php'; ?>
  <main class="main-content">
    <div class="center-wrap">
      <div class="reset-box">
        <div class="reset-logo">🔑</div>
        <div class="reset-title">Đặt lại mật khẩu</div>
        <div class="reset-sub">Nhập mật khẩu mới cho tài khoản của bạn</div>

        <div id="checking-view" class="form-card" style="text-align:center;padding:32px">
          <div class="spinner" style="width:24px;height:24px;border-color:rgba(0,0,0,.15);border-top-color:var(--green);margin:0 auto 12px"></div>
          <div style="font-size:14px;color:var(--text2)">Đang kiểm tra link...</div>
        </div>

        <div id="invalid-view" class="form-card hidden" style="text-align:center;padding:32px">
          <div style="font-size:40px;margin-bottom:12px">⚠</div>
          <div style="font-size:16px;font-weight:500;margin-bottom:8px">Link không hợp lệ</div>
          <div style="font-size:13px;color:var(--text2);margin-bottom:20px">Link đặt lại mật khẩu đã hết hạn hoặc đã được sử dụng.</div>
          <a href="member.php" class="btn btn-primary btn-full">Quay lại đăng nhập</a>
        </div>

        <div id="reset-form-view" class="form-card hidden">
          <div id="reset-alert" class="alert hidden"></div>
          <div class="form-group">
            <label>Mật khẩu mới <span class="required">*</span></label>
            <div class="pw-wrap">
              <input type="password" id="new-pw" class="form-input" placeholder="Tối thiểu 6 ký tự" oninput="checkStrength(this.value)">
              <button type="button" class="pw-toggle" onclick="togglePw('new-pw',this)">👁</button>
            </div>
            <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
            <div class="strength-label" id="strength-label" style="color:var(--text3)"></div>
          </div>
          <div class="form-group">
            <label>Xác nhận mật khẩu <span class="required">*</span></label>
            <div class="pw-wrap">
              <input type="password" id="confirm-pw" class="form-input" placeholder="Nhập lại mật khẩu mới">
              <button type="button" class="pw-toggle" onclick="togglePw('confirm-pw',this)">👁</button>
            </div>
          </div>
          <button class="btn btn-primary btn-full" onclick="doReset()">Lưu mật khẩu mới</button>
        </div>

        <div id="success-view" class="form-card hidden" style="text-align:center;padding:32px">
          <div style="width:60px;height:60px;background:var(--green);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:28px">✓</div>
          <div style="font-size:17px;font-weight:600;margin-bottom:8px">Mật khẩu đã cập nhật!</div>
          <div style="font-size:13px;color:var(--text2);margin-bottom:20px">Bạn có thể đăng nhập với mật khẩu mới ngay bây giờ.</div>
          <a href="member.php" class="btn btn-primary btn-full">Đăng nhập →</a>
        </div>

      </div>
    </div>
  </main>
</div>

<script src="assets/js/app.js"></script>
<script>
const token = new URLSearchParams(location.search).get('token') || '';

async function checkToken() {
  if (!token) { showView('invalid'); return; }
  try {
    const res = await fetch(`api/auth.php?action=check_token&token=${token}`);
    const json = await res.json();
    showView(json.valid ? 'form' : 'invalid');
  } catch(e) { showView('invalid'); }
}

function showView(v) {
  document.getElementById('checking-view').classList.add('hidden');
  document.getElementById('invalid-view').classList.add('hidden');
  document.getElementById('reset-form-view').classList.add('hidden');
  document.getElementById('success-view').classList.add('hidden');
  if (v==='invalid') document.getElementById('invalid-view').classList.remove('hidden');
  else if (v==='form') document.getElementById('reset-form-view').classList.remove('hidden');
  else if (v==='success') document.getElementById('success-view').classList.remove('hidden');
}

function togglePw(id, btn) {
  const el = document.getElementById(id);
  if (el.type==='password') { el.type='text'; btn.textContent='🙈'; }
  else { el.type='password'; btn.textContent='👁'; }
}

function checkStrength(pw) {
  let score = 0;
  if (pw.length >= 6) score++;
  if (pw.length >= 10) score++;
  if (/[A-Z]/.test(pw)) score++;
  if (/[0-9]/.test(pw)) score++;
  if (/[^A-Za-z0-9]/.test(pw)) score++;
  const fill = document.getElementById('strength-fill');
  const lbl  = document.getElementById('strength-label');
  const levels = [
    {w:'0%', c:'', t:''},
    {w:'25%', c:'#E24B4A', t:'Quá yếu'},
    {w:'50%', c:'#BA7517', t:'Yếu'},
    {w:'75%', c:'#1D9E75', t:'Tốt'},
    {w:'100%', c:'#085041', t:'Mạnh'},
  ];
  const l = levels[Math.min(score, 4)];
  fill.style.width = l.w; fill.style.background = l.c;
  lbl.textContent = l.t; lbl.style.color = l.c;
}

async function doReset() {
  const pw  = document.getElementById('new-pw').value;
  const cpw = document.getElementById('confirm-pw').value;
  if (pw.length < 6) { showAlert('reset-alert','Mật khẩu tối thiểu 6 ký tự','warn'); return; }
  if (pw !== cpw)    { showAlert('reset-alert','Mật khẩu xác nhận không khớp','warn'); return; }

  try {
    const res = await fetch('api/auth.php?action=reset', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ token, password: pw })
    });
    const json = await res.json();
    if (json.error) { showAlert('reset-alert', json.error, 'error'); return; }
    showView('success');
  } catch(e) { showAlert('reset-alert','Lỗi kết nối server','error'); }
}

checkToken();
</script>
</body>
</html>
