<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Đăng Ký — Wonder Pickleball</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
.pw-wrap{position:relative}
.pw-wrap input{padding-right:44px}
.pw-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text2);font-size:15px;padding:4px}
</style>
</head>
<body>
<div class="layout">
  <?php include __DIR__ . '/includes/nav.php'; ?>
  <main class="main-content">
    <div class="page-header">
      <h1>Đăng Ký Thành Viên</h1>
      <p class="subtitle">Tạo tài khoản để quản lý thẻ tập Wonder Pickleball</p>
    </div>

    <div class="form-card">
      <div id="reg-alert" class="alert hidden"></div>

      <div class="form-group">
        <label>Họ và tên <span class="required">*</span></label>
        <input type="text" id="reg-name" class="form-input" placeholder="Nguyễn Văn A">
      </div>

      <div class="form-group">
        <label>Số điện thoại <span class="required">*</span></label>
        <input type="tel" id="reg-phone" class="form-input" placeholder="0901 234 567" maxlength="12" oninput="fmtPhone(this)">
      </div>

      <div class="form-group">
        <label>Email <span class="required">*</span></label>
        <input type="email" id="reg-email" class="form-input" placeholder="example@gmail.com">
        <div style="font-size:11px;color:var(--text3);margin-top:4px">Dùng để nhận link quên mật khẩu</div>
      </div>

      <div class="form-group">
        <label>Mật khẩu <span class="required">*</span></label>
        <div class="pw-wrap">
          <input type="password" id="reg-pw" class="form-input" placeholder="Tối thiểu 6 ký tự">
          <button type="button" class="pw-toggle" onclick="togglePw('reg-pw',this)">👁</button>
        </div>
      </div>

      <div class="form-group">
        <label>Xác nhận mật khẩu <span class="required">*</span></label>
        <div class="pw-wrap">
          <input type="password" id="reg-pw2" class="form-input" placeholder="Nhập lại mật khẩu">
          <button type="button" class="pw-toggle" onclick="togglePw('reg-pw2',this)">👁</button>
        </div>
      </div>

      <button class="btn btn-primary btn-full" id="reg-btn" onclick="doRegister()">Đăng ký tài khoản →</button>
      <div style="text-align:center;margin-top:14px;font-size:13px;color:var(--text3)">
        Đã có tài khoản? <a href="member.php" style="color:var(--green)">Đăng nhập</a>
      </div>
    </div>
  </main>
</div>
<script src="assets/js/app.js"></script>
<script>
function fmtPhone(el){let d=el.value.replace(/\D/g,'').slice(0,10);if(d.length>7)el.value=d.slice(0,4)+' '+d.slice(4,7)+' '+d.slice(7);else if(d.length>4)el.value=d.slice(0,4)+' '+d.slice(4);else el.value=d;}
function togglePw(id,btn){const el=document.getElementById(id);if(el.type==='password'){el.type='text';btn.textContent='🙈';}else{el.type='password';btn.textContent='👁';}}

async function doRegister() {
  const name  = document.getElementById('reg-name').value.trim();
  const phone = document.getElementById('reg-phone').value.replace(/\D/g,'');
  const email = document.getElementById('reg-email').value.trim();
  const pw    = document.getElementById('reg-pw').value;
  const pw2   = document.getElementById('reg-pw2').value;

  if (!name)           { showAlert('reg-alert','Vui lòng nhập họ tên','warn'); return; }
  if (phone.length<9)  { showAlert('reg-alert','Vui lòng nhập đủ số điện thoại','warn'); return; }
  if (!email)          { showAlert('reg-alert','Vui lòng nhập email','warn'); return; }
  if (pw.length<6)     { showAlert('reg-alert','Mật khẩu tối thiểu 6 ký tự','warn'); return; }
  if (pw !== pw2)      { showAlert('reg-alert','Mật khẩu xác nhận không khớp','warn'); return; }

  const btn = document.getElementById('reg-btn');
  btn.disabled = true; btn.textContent = 'Đang xử lý...';

  try {
    const res = await fetch('api/auth.php?action=register', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({name, phone, email, password: pw})
    });
    const json = await res.json();
    if (json.error) {
      let msg = json.error;
      if (json.error.includes('điện thoại')) msg += ' <a href="member.php?phone='+phone+'" style="color:var(--green)">Đăng nhập?</a>';
      showAlert('reg-alert', msg, 'error');
      btn.disabled=false; btn.textContent='Đăng ký tài khoản →';
      return;
    }
    window.location.href = 'member.php?phone=' + phone;
  } catch(e) {
    showAlert('reg-alert','Lỗi kết nối server','error');
    btn.disabled=false; btn.textContent='Đăng ký tài khoản →';
  }
}
</script>
</body>
</html>
