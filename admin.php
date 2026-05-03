<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin — Wonder Pickleball</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<style>
.admin-tabs{display:flex;gap:2px;background:var(--bg2);padding:4px;border-radius:var(--radius-lg);margin-bottom:20px}
.admin-tab{flex:1;padding:8px;text-align:center;font-size:13px;border-radius:var(--radius);border:none;background:transparent;cursor:pointer;color:var(--text2);font-family:inherit;transition:all .15s;position:relative}
.admin-tab.active{background:var(--bg);color:var(--text);box-shadow:0 0 0 0.5px var(--border2)}
.admin-tab .tab-badge{position:absolute;top:4px;right:8px;background:var(--red);color:white;border-radius:99px;font-size:10px;font-weight:600;padding:1px 5px;min-width:16px;text-align:center}
.tab-panel{display:none}
.tab-panel.active{display:block}

/* Order list */
.order-card{background:var(--bg);border:0.5px solid var(--border2);border-radius:var(--radius-lg);padding:16px;margin-bottom:10px;transition:border-color .15s}
.order-card.pending{border-color:#FAC775;border-left:3px solid var(--amber)}
.order-card.paid{border-left:3px solid var(--green);opacity:.75}
.order-card.cancelled{border-left:3px solid var(--gray);opacity:.5}
.order-header{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.order-code{font-family:'DM Mono',monospace;font-size:13px;font-weight:500;color:var(--green-dark)}
.order-status{display:inline-block;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600}
.status-Unpaid{background:var(--amber-light);color:#633806}
.status-Paid{background:var(--green-light);color:var(--green-dark)}
.status-Cancelled{background:var(--gray-light);color:#444}
.order-body{margin-top:10px;font-size:13px;display:grid;grid-template-columns:1fr 1fr;gap:4px 16px}
.order-body .lbl{color:var(--text2)}
.order-body .val{font-weight:500}
.order-actions{display:flex;gap:8px;margin-top:12px;padding-top:12px;border-top:0.5px solid var(--border)}
.order-qr-thumb{width:72px;height:72px;flex-shrink:0;border-radius:var(--radius);border:0.5px solid var(--border);overflow:hidden}
.order-qr-thumb img{width:100%;height:100%;object-fit:cover}

/* SMTP */
.smtp-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
.pw-wrap{position:relative}
.pw-wrap input{padding-right:44px}
.pw-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text2)}

/* Orders filter tabs */
.filter-tabs{display:flex;gap:6px;margin-bottom:14px;flex-wrap:wrap}
.filter-tab{padding:5px 14px;border-radius:99px;border:0.5px solid var(--border2);background:transparent;font-size:12px;cursor:pointer;color:var(--text2);font-family:inherit;transition:all .15s}
.filter-tab.active{background:var(--green);color:white;border-color:var(--green)}

/* Auto-refresh indicator */
.refresh-row{display:flex;align-items:center;gap:8px;font-size:12px;color:var(--text3);margin-bottom:12px}
.refresh-dot{width:6px;height:6px;border-radius:50%;background:var(--green);animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}

/* Report */
.report-section{background:var(--bg);border:0.5px solid var(--border2);border-radius:var(--radius-lg);padding:20px;margin-bottom:16px;box-shadow:var(--shadow)}
.report-section h3{font-size:15px;font-weight:600;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.report-list{display:flex;flex-direction:column;gap:8px}
.report-item{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--bg2);border-radius:var(--radius);font-size:13px}
.report-item .ri-name{font-weight:500}
.report-item .ri-phone{font-family:'DM Mono',monospace;font-size:12px;color:var(--text2)}
.report-item .ri-sessions{font-weight:600}
.report-item .ri-sessions.low{color:var(--amber)}
.report-item .ri-sessions.critical{color:var(--red)}
.report-item .ri-time{font-size:12px;color:var(--text3)}
.revenue-big{font-size:36px;font-weight:700;color:var(--green-dark);margin-bottom:4px}
.revenue-sub{font-size:13px;color:var(--text2)}
.rev-day{display:flex;justify-content:space-between;padding:8px 0;border-bottom:0.5px solid var(--border);font-size:13px}
.rev-day:last-child{border-bottom:none}
.rev-day .rd-date{color:var(--text2)}
.rev-day .rd-amount{font-weight:600;color:var(--green-dark)}

/* Pagination */
.pager{display:flex;align-items:center;justify-content:space-between;margin-top:12px;font-size:12px;color:var(--text2)}
.pager-btns{display:flex;gap:6px}
.pager-btn{padding:4px 12px;border:0.5px solid var(--border2);border-radius:99px;background:transparent;font-size:12px;cursor:pointer;font-family:inherit;color:var(--text2)}
.pager-btn:disabled{opacity:.35;cursor:default}
.pager-btn.active-pg{background:var(--green);color:white;border-color:var(--green)}

/* Banner management */
.banner-card{background:var(--bg);border:0.5px solid var(--border2);border-radius:var(--radius-lg);padding:14px;margin-bottom:10px;display:flex;align-items:center;gap:14px}
.banner-thumb{width:80px;height:50px;border-radius:var(--radius);object-fit:cover;border:0.5px solid var(--border);flex-shrink:0;background:var(--bg2)}
.banner-info{flex:1;min-width:0}
.banner-link{font-size:12px;color:var(--text2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.banner-actions{display:flex;gap:6px;flex-shrink:0}
.upload-zone{border:1.5px dashed var(--border2);border-radius:var(--radius-lg);padding:24px;text-align:center;cursor:pointer;transition:border-color .15s;margin-bottom:14px}
.upload-zone:hover,.upload-zone.drag{border-color:var(--green);background:var(--green-light)}
.upload-zone input{display:none}
</style>
</head>
<body>
<div class="layout">
  <?php include __DIR__ . '/includes/nav.php'; ?>
  <main class="main-content">

    <!-- Login -->
    <div id="login-panel">
      <div class="page-header"><h1>Quản Trị</h1><p class="subtitle">Đăng nhập để quản lý thành viên và đơn hàng</p></div>
      <div class="form-card" style="max-width:380px">
        <div id="login-alert" class="alert hidden"></div>
        <div class="form-group">
          <label>Mật khẩu admin</label>
          <input type="password" id="admin-pw" class="form-input" placeholder="••••••••" onkeydown="if(event.key==='Enter')adminLogin()">
        </div>
        <button class="btn btn-primary btn-full" onclick="adminLogin()">Đăng nhập</button>
      </div>
    </div>

    <!-- Dashboard -->
    <div id="admin-panel" class="hidden">
      <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">
        <div><h1>Wonder Pickleball</h1><p class="subtitle">Trang quản trị</p></div>
        <button class="btn btn-ghost" onclick="adminLogout()" style="font-size:13px">Đăng xuất</button>
      </div>

      <!-- Stats -->
      <div class="stats-grid" style="margin-bottom:20px">
        <div class="stat-card">
          <div class="stat-icon">👥</div>
          <div class="stat-num" id="stat-total">—</div>
          <div class="stat-label">Thành viên</div>
        </div>
        <div class="stat-card warn" id="stat-pending-card" style="cursor:pointer" onclick="switchTab('orders')">
          <div class="stat-icon">📋</div>
          <div class="stat-num" id="stat-pending">—</div>
          <div class="stat-label">Chờ duyệt</div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">✓</div>
          <div class="stat-num" id="stat-today">—</div>
          <div class="stat-label">Check-in hôm nay</div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="admin-tabs">
        <button class="admin-tab active" id="tab-orders" onclick="switchTab('orders')">
          📋 Đơn hàng
          <span class="tab-badge hidden" id="orders-badge">0</span>
        </button>
        <button class="admin-tab" id="tab-checkin" onclick="switchTab('checkin')">✓ Check-in</button>
        <button class="admin-tab" id="tab-report" onclick="switchTab('report')">📊 Báo cáo</button>
        <button class="admin-tab" id="tab-members" onclick="switchTab('members')">👥 Thành viên</button>
        <button class="admin-tab" id="tab-inventory" onclick="switchTab('inventory')">📦 Kho hàng</button>
        <button class="admin-tab" id="tab-sales" onclick="switchTab('sales')">🛒 Bán hàng</button>
        <button class="admin-tab" id="tab-salesreport" onclick="switchTab('salesreport')">📈 BC bán hàng</button>
        <button class="admin-tab" id="tab-banners" onclick="switchTab('banners')">🖼 Sự kiện</button>
        <button class="admin-tab" id="tab-smtp" onclick="switchTab('smtp')">⚙ Cài đặt</button>
      </div>

      <!-- TAB: ORDERS -->
      <div class="tab-panel active" id="panel-orders">
        <div class="refresh-row">
          <div class="refresh-dot"></div>
          Tự động cập nhật mỗi 15 giây
          <button class="btn btn-ghost" style="font-size:12px;padding:4px 10px;margin-left:auto" onclick="loadOrders()">↻ Làm mới</button>
        </div>
        <div class="filter-tabs">
          <button class="filter-tab active" onclick="filterOrders(this,'')">Tất cả</button>
          <button class="filter-tab" onclick="filterOrders(this,'Unpaid')">⏳ Chờ duyệt</button>
          <button class="filter-tab" onclick="filterOrders(this,'Paid')">✓ Đã duyệt</button>
          <button class="filter-tab" onclick="filterOrders(this,'Cancelled')">✕ Đã huỷ</button>
        </div>
        <div id="orders-list"></div>
        <div id="orders-empty" class="no-history hidden">Không có đơn hàng</div>
      </div>

      <!-- TAB: CHECK-IN -->
      <div class="tab-panel" id="panel-checkin">
        <div class="table-card" style="margin-bottom:16px">
          <h3 style="font-size:16px;font-weight:500;margin-bottom:12px">Check-in khách hàng</h3>
          <div style="display:flex;gap:8px;margin-bottom:12px">
            <div style="flex:1;position:relative">
              <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%)">🔍</span>
              <input type="text" id="ci-phone-input" class="form-input" style="padding-left:38px;font-size:15px" placeholder="Nhập tên, 4 số cuối hoặc SĐT đầy đủ..." onkeydown="if(event.key==='Enter')ciSearch()">
            </div>
            <button class="btn btn-primary" onclick="ciSearch()">Tìm</button>
            <button class="btn btn-outline" id="ci-scan-btn" onclick="ciStartQR()">📷 QR</button>
          </div>
          <div id="ci-qr-wrap" style="display:none;margin-bottom:12px">
            <div id="ci-qr-reader" style="width:100%;max-width:400px;border-radius:var(--radius-lg);overflow:hidden"></div>
            <button class="btn btn-ghost btn-full" style="margin-top:8px;font-size:13px" onclick="ciStopQR()">✕ Đóng camera</button>
          </div>
          <div id="ci-search-alert" class="alert hidden"></div>
          <div id="ci-matches-list" style="margin-top:12px"></div>
        </div>

        <div id="ci-customer-panel" class="hidden">
          <div class="table-card" style="margin-bottom:16px">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
              <div class="customer-avatar" id="ci-avatar" style="width:52px;height:52px;font-size:18px">NA</div>
              <div style="flex:1">
                <div style="font-size:16px;font-weight:600" id="ci-name">—</div>
                <div style="font-size:13px;color:var(--text2)" id="ci-phone-display">—</div>
              </div>
              <div style="text-align:center">
                <div style="font-size:28px;font-weight:700;color:var(--green-dark)" id="ci-sessions">0</div>
                <div style="font-size:11px;color:var(--text2)">buổi còn lại</div>
              </div>
            </div>

            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;padding:14px;background:var(--bg2);border-radius:var(--radius-lg)">
              <span style="font-size:14px;font-weight:500;white-space:nowrap">Số người:</span>
              <div style="display:flex;align-items:center;gap:8px">
                <button class="btn btn-outline" style="width:36px;height:36px;padding:0;font-size:20px;display:flex;align-items:center;justify-content:center" onclick="ciChangePeople(-1)">−</button>
                <span id="ci-people-count" style="font-size:24px;font-weight:700;min-width:40px;text-align:center;color:var(--green-dark)">1</span>
                <button class="btn btn-outline" style="width:36px;height:36px;padding:0;font-size:20px;display:flex;align-items:center;justify-content:center" onclick="ciChangePeople(1)">+</button>
              </div>
              <span id="ci-people-note" style="font-size:12px;color:var(--text2);margin-left:auto">Trừ 1 lượt</span>
            </div>

            <div id="ci-checkin-alert" class="alert hidden"></div>
            <button class="btn btn-primary btn-full" id="ci-checkin-btn" onclick="ciDoCheckin()" style="padding:14px;font-size:15px">
              ✓ Check In — trừ 1 buổi
            </button>
          </div>

          <div class="table-card">
            <h3 style="font-size:14px;font-weight:500;margin-bottom:10px">Lịch sử check-in gần đây</h3>
            <div id="ci-history" class="report-list"></div>
          </div>
        </div>
      </div>

      <!-- TAB: MEMBERS -->
      <div class="tab-panel" id="panel-members">
        <div class="table-card" style="margin-bottom:0">
          <div class="table-header" style="flex-wrap:wrap;gap:8px">
            <h3>Danh sách thành viên</h3>
            <div style="display:flex;gap:8px;align-items:center;margin-left:auto">
              <input type="text" class="form-input" style="width:180px;font-size:13px" placeholder="Tìm tên hoặc SĐT..." oninput="filterList(this.value)">
              <button class="btn btn-primary" style="font-size:12px;white-space:nowrap;padding:7px 12px" onclick="openBulkModal()">+ Tạo tài khoản</button>
              <button class="btn btn-outline" style="font-size:12px;white-space:nowrap;padding:7px 12px" onclick="exportMembers()">📥 Xuất Excel</button>
            </div>
          </div>
          <div style="overflow-x:auto">
            <table class="data-table" id="customer-table">
              <thead><tr>
                <th>Họ tên</th>
                <th>SĐT</th>
                <th class="col-hide-mobile">Email</th>
                <th>Buổi</th>
                <th class="col-hide-mobile">Hết hạn</th>
                <th></th>
              </tr></thead>
              <tbody id="customer-tbody"></tbody>
            </table>
          </div>
          <div id="no-data" class="no-history hidden">Không có dữ liệu</div>
          <!-- Phân trang -->
          <div id="member-pager" style="display:none;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;padding:12px 16px;border-top:0.5px solid var(--border)">
            <div style="font-size:12px;color:var(--text2)">
              Hiển thị <strong id="member-page-from">1</strong>–<strong id="member-page-to">20</strong> / <strong id="member-page-total">0</strong> thành viên
              &nbsp;·&nbsp;
              <select id="member-page-size" style="font-size:12px;padding:2px 6px;border:0.5px solid var(--border2);border-radius:6px;background:var(--bg)" onchange="memberPageSizeChange()">
                <option value="10">10/trang</option>
                <option value="20" selected>20/trang</option>
                <option value="50">50/trang</option>
                <option value="100">100/trang</option>
              </select>
            </div>
            <div style="display:flex;gap:4px;align-items:center">
              <button class="pager-btn" id="member-first" onclick="memberGoPage(1)">«</button>
              <button class="pager-btn" id="member-prev" onclick="memberGoPage(memberPage-1)">‹ Trước</button>
              <span id="member-page-info" style="font-size:12px;color:var(--text2);padding:0 8px"></span>
              <button class="pager-btn" id="member-next" onclick="memberGoPage(memberPage+1)">Sau ›</button>
              <button class="pager-btn" id="member-last" onclick="memberGoPage(memberTotalPages)">»</button>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB: REPORT -->
      <div class="tab-panel" id="panel-report">
        <div id="report-loading" style="text-align:center;padding:40px;color:var(--text2)">Đang tải báo cáo...</div>
        <div id="report-content" class="hidden">

          <!-- Date Picker -->
          <div class="report-section" style="padding:14px 20px">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
              <span style="font-size:13px;font-weight:500;white-space:nowrap">📅 Khoảng ngày:</span>
              <input type="date" id="rpt-date-from" class="form-input" style="font-size:13px;width:auto;padding:6px 10px">
              <span style="font-size:13px;color:var(--text3)">→</span>
              <input type="date" id="rpt-date-to" class="form-input" style="font-size:13px;width:auto;padding:6px 10px">
              <button class="btn btn-primary" style="padding:6px 14px;font-size:13px" onclick="loadReport()">Xem</button>
              <div style="display:flex;gap:4px;margin-left:auto">
                <button class="btn btn-ghost" style="font-size:11px;padding:4px 8px" onclick="setReportDate('today')">Hôm nay</button>
                <button class="btn btn-ghost" style="font-size:11px;padding:4px 8px" onclick="setReportDate('7d')">7 ngày</button>
                <button class="btn btn-ghost" style="font-size:11px;padding:4px 8px" onclick="setReportDate('month')">Tháng này</button>
              </div>
            </div>
          </div>

          <!-- Doanh thu -->
          <div class="report-section">
            <h3 style="justify-content:space-between">💰 Doanh thu <span id="rpt-rev-label" style="font-size:12px;font-weight:400;color:var(--text2)"></span> <button class="btn btn-ghost" style="font-size:11px;padding:4px 10px;margin-left:auto" onclick="exportReport()">📥 Xuất Excel</button></h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
              <div>
                <div class="revenue-big" id="rpt-revenue">0đ</div>
                <div class="revenue-sub" id="rpt-revenue-sub">0 đơn đã duyệt</div>
              </div>
              <div style="text-align:right">
                <div style="font-size:13px;color:var(--text2);margin-bottom:4px">Doanh thu tháng này</div>
                <div style="font-size:22px;font-weight:700;color:var(--green-dark)" id="rpt-rev-month">0đ</div>
                <div style="font-size:12px;color:var(--text3)" id="rpt-rev-month-sub"></div>
              </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
              <div style="background:var(--green-light);border-radius:var(--radius);padding:12px">
                <div style="font-size:11px;color:var(--green-dark);margin-bottom:4px">Bán gói combo</div>
                <div style="font-size:18px;font-weight:700;color:var(--green-dark)" id="rpt-rev-combo">0đ</div>
                <div style="font-size:11px;color:var(--text2)" id="rpt-rev-combo-sub">0 đơn</div>
              </div>
              <div style="background:var(--bg2);border-radius:var(--radius);padding:12px">
                <div style="font-size:11px;color:var(--text2);margin-bottom:4px">Khách vãng lai (lẻ)</div>
                <div style="font-size:18px;font-weight:700;color:var(--text)" id="rpt-rev-walkin">0đ</div>
                <div style="font-size:11px;color:var(--text2)" id="rpt-rev-walkin-sub">0 đơn</div>
              </div>
            </div>
          </div>

          <!-- Doanh thu theo ngày -->
          <div class="report-section">
            <h3>📈 Chi tiết doanh thu theo ngày</h3>
            <div id="rpt-rev-days" class="report-list"></div>
            <div id="rpt-rev-days-empty" class="hidden" style="font-size:13px;color:var(--text3);text-align:center;padding:16px">Chưa có doanh thu</div>
          </div>

          <!-- Lượt chơi -->
          <div class="report-section">
            <h3>🏃 Lượt chơi <span id="rpt-ci-label" style="font-size:12px;font-weight:400;color:var(--text2)"></span></h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:10px">
              <div style="background:var(--green-light);border-radius:var(--radius);padding:16px;text-align:center">
                <div style="font-size:28px;font-weight:700;color:var(--green-dark)" id="rpt-people">0</div>
                <div style="font-size:12px;color:var(--green-dark)">Người vào chơi</div>
              </div>
              <div style="background:var(--bg2);border-radius:var(--radius);padding:16px;text-align:center">
                <div style="font-size:28px;font-weight:700;color:var(--text)" id="rpt-checkins">0</div>
                <div style="font-size:12px;color:var(--text2)">Lượt check-in</div>
              </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px">
              <div style="background:var(--green-light);border-radius:var(--radius);padding:10px">
                <div style="font-size:11px;color:var(--green-dark);margin-bottom:2px">Sở hữu gói (combo)</div>
                <div style="font-size:15px;font-weight:700;color:var(--green-dark)"><span id="rpt-ci-combo-ppl">0</span> người · <span id="rpt-ci-combo-ci">0</span> lượt</div>
              </div>
              <div style="background:var(--bg2);border-radius:var(--radius);padding:10px">
                <div style="font-size:11px;color:var(--text2);margin-bottom:2px">Vãng lai (lẻ)</div>
                <div style="font-size:15px;font-weight:700;color:var(--text)"><span id="rpt-ci-walk-ppl">0</span> người · <span id="rpt-ci-walk-ci">0</span> lượt</div>
              </div>
            </div>
            <div id="rpt-checkin-list" class="report-list"></div>
            <div id="rpt-checkin-empty" class="hidden" style="font-size:13px;color:var(--text3);text-align:center;padding:16px">Chưa có check-in hôm nay</div>
            <div id="rpt-checkin-pager" class="pager hidden">
              <span id="rpt-ci-page-info"></span>
              <div class="pager-btns">
                <button class="pager-btn" id="rpt-ci-prev" onclick="ciPageChange(-1)">← Trước</button>
                <button class="pager-btn" id="rpt-ci-next" onclick="ciPageChange(1)">Sau →</button>
              </div>
            </div>
          </div>

          <!-- Hội viên cần chăm sóc -->
          <div class="report-section">
            <h3>⚠ Hội viên sở hữu gói còn dưới 5 buổi <span style="font-size:12px;font-weight:400;color:var(--text3)" id="rpt-low-count">(0)</span></h3>
            <div style="font-size:12px;color:var(--text2);margin-bottom:12px">Chỉ tính khách đã sở hữu gói combo — liên hệ chăm sóc và gợi ý gia hạn</div>
            <div id="rpt-low-list" class="report-list"></div>
            <div id="rpt-low-empty" class="hidden" style="font-size:13px;color:var(--text3);text-align:center;padding:16px">Không có hội viên nào</div>
            <div id="rpt-low-pager" class="pager hidden">
              <span id="rpt-low-page-info"></span>
              <div class="pager-btns">
                <button class="pager-btn" id="rpt-low-prev" onclick="lowPageChange(-1)">← Trước</button>
                <button class="pager-btn" id="rpt-low-next" onclick="lowPageChange(1)">Sau →</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB: KHO HÀNG -->
      <div class="tab-panel" id="panel-inventory">
        <div class="table-card" style="margin-bottom:16px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px">
            <h3 style="font-size:16px;font-weight:500;margin:0">📦 Danh sách sản phẩm</h3>
            <div style="display:flex;gap:8px">
              <input type="text" id="inv-search" class="form-input" style="font-size:13px;width:180px" placeholder="Tìm sản phẩm..." oninput="searchProducts()">
              <button class="btn btn-primary" onclick="openAddProductModal()">+ Thêm SP</button>
            </div>
          </div>
          <div id="inv-alert" class="alert hidden"></div>
          <div style="overflow-x:auto">
            <table class="data-table" style="min-width:600px">
              <colgroup><col style="min-width:180px"><col style="width:70px"><col style="width:110px"><col style="width:110px"><col style="width:80px"><col style="width:140px"></colgroup>
              <thead><tr><th>Tên sản phẩm</th><th>ĐVT</th><th>Giá nhập</th><th>Giá bán</th><th>Tồn</th><th>Thao tác</th></tr></thead>
              <tbody id="inv-tbody"><tr><td colspan="6" style="text-align:center;color:var(--text3);padding:20px">Đang tải...</td></tr></tbody>
            </table>
          </div>
        </div>
        <!-- Lịch sử nhập kho -->
        <div class="table-card">
          <h3 style="font-size:15px;font-weight:500;margin-bottom:12px">📋 Lịch sử nhập kho</h3>
          <div style="overflow-x:auto">
            <table class="data-table" style="min-width:500px">
              <thead><tr><th>Thời gian</th><th>Sản phẩm</th><th>SL</th><th>Ghi chú</th></tr></thead>
              <tbody id="inv-log-tbody"><tr><td colspan="4" style="text-align:center;color:var(--text3);padding:20px">Đang tải...</td></tr></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB: BÁN HÀNG -->
      <div class="tab-panel" id="panel-sales">
        <div style="display:grid;grid-template-columns:1fr 340px;gap:16px;align-items:start">
          <!-- Tìm & thêm sản phẩm -->
          <div>
            <div class="table-card" style="margin-bottom:16px">
              <h3 style="font-size:15px;font-weight:500;margin-bottom:12px">🔍 Thêm sản phẩm vào giỏ</h3>
              <div style="position:relative">
                <input type="text" id="sale-search" class="form-input" placeholder="Nhập tên sản phẩm..." oninput="saleProdSearch()" autocomplete="off">
                <div id="sale-suggestions" style="display:none;position:absolute;top:100%;left:0;right:0;background:var(--bg);border:0.5px solid var(--border2);border-radius:var(--radius-lg);z-index:10;max-height:220px;overflow-y:auto;box-shadow:0 4px 16px rgba(0,0,0,.12)"></div>
              </div>
            </div>
            <!-- Lịch sử bán -->
            <div class="table-card">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                <h3 style="font-size:15px;font-weight:500;margin:0">🧾 Đơn bán gần đây</h3>
                <input type="date" id="sale-filter-date" class="form-input" style="font-size:12px;width:140px" onchange="loadSales()">
              </div>
              <div style="overflow-x:auto">
                <table class="data-table" style="min-width:480px">
                  <thead><tr><th>Thời gian</th><th>Hàng hóa</th><th>Tổng tiền</th><th></th></tr></thead>
                  <tbody id="sales-tbody"><tr><td colspan="4" style="text-align:center;color:var(--text3);padding:20px">Đang tải...</td></tr></tbody>
                </table>
              </div>
            </div>
          </div>
          <!-- Giỏ hàng -->
          <div class="table-card" style="position:sticky;top:16px">
            <h3 style="font-size:15px;font-weight:500;margin-bottom:12px">🛒 Giỏ hàng</h3>
            <div id="sale-alert" class="alert hidden"></div>
            <div id="cart-list" style="min-height:80px"></div>
            <div style="border-top:0.5px solid var(--border2);margin:12px 0;padding-top:12px">
              <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:600">
                <span>Tổng cộng</span>
                <span id="cart-total" style="color:var(--green)">0đ</span>
              </div>
            </div>
            <textarea id="sale-note" class="form-input" rows="2" placeholder="Ghi chú đơn (không bắt buộc)" style="font-size:13px;margin-bottom:10px;resize:none"></textarea>
            <button class="btn btn-primary btn-full" onclick="completeSale()">✓ Hoàn tất bán hàng</button>
          </div>
        </div>
      </div>

      <!-- TAB: BÁO CÁO BÁN HÀNG -->
      <div class="tab-panel" id="panel-salesreport">
        <div class="table-card" style="margin-bottom:16px">
          <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:16px">
            <h3 style="font-size:16px;font-weight:500;margin:0">📈 Báo cáo bán hàng</h3>
            <input type="date" id="sr-from" class="form-input" style="font-size:13px;width:140px">
            <span style="font-size:13px;color:var(--text2)">đến</span>
            <input type="date" id="sr-to" class="form-input" style="font-size:13px;width:140px">
            <button class="btn btn-primary" onclick="loadSalesReport()">Xem</button>
          </div>
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px">
            <div class="stat-card"><div id="sr-revenue" class="stat-number" style="font-size:20px">—</div><div class="stat-label">Doanh thu</div></div>
            <div class="stat-card"><div id="sr-cost" class="stat-number" style="font-size:20px">—</div><div class="stat-label">Giá vốn</div></div>
            <div class="stat-card success"><div id="sr-profit" class="stat-number" style="font-size:20px">—</div><div class="stat-label">Lợi nhuận</div></div>
            <div class="stat-card"><div id="sr-orders" class="stat-number" style="font-size:20px">—</div><div class="stat-label">Số đơn</div></div>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
          <div class="table-card">
            <h3 style="font-size:14px;font-weight:500;margin-bottom:12px">Top sản phẩm bán chạy</h3>
            <table class="data-table">
              <thead><tr><th>Sản phẩm</th><th>SL bán</th><th>Doanh thu</th></tr></thead>
              <tbody id="sr-top-tbody"></tbody>
            </table>
          </div>
          <div class="table-card">
            <h3 style="font-size:14px;font-weight:500;margin-bottom:12px">Doanh thu theo ngày</h3>
            <table class="data-table">
              <thead><tr><th>Ngày</th><th>Doanh thu</th><th>Lợi nhuận</th><th>Đơn</th></tr></thead>
              <tbody id="sr-daily-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB: BANNERS -->
      <div class="tab-panel" id="panel-banners">
        <div class="table-card" style="margin-bottom:16px">
          <h3 style="font-size:15px;font-weight:600;margin-bottom:4px">Thêm banner / sự kiện</h3>
          <p style="font-size:12px;color:var(--text2);margin-bottom:14px">Tải ảnh lên và gắn link (tùy chọn). Ảnh sẽ hiển thị trên trang chủ.</p>
          <div id="banner-upload-alert" class="alert hidden"></div>
          <div class="upload-zone" id="banner-drop" onclick="document.getElementById('banner-file').click()" ondragover="event.preventDefault();this.classList.add('drag')" ondragleave="this.classList.remove('drag')" ondrop="handleBannerDrop(event)">
            <input type="file" id="banner-file" accept="image/*" onchange="previewBannerFile(this)">
            <div id="banner-preview-wrap" style="display:none;margin-bottom:12px">
              <img id="banner-preview-img" src="" style="max-width:200px;max-height:120px;border-radius:var(--radius);object-fit:cover">
            </div>
            <div id="banner-drop-text">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--text3)" stroke-width="1.5" style="margin:0 auto 8px;display:block"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
              <div style="font-size:13px;font-weight:500;color:var(--text2)">Kéo thả hoặc bấm để chọn ảnh</div>
              <div style="font-size:11px;color:var(--text3);margin-top:4px">JPG, PNG, WEBP — tối đa 5MB</div>
            </div>
          </div>
          <div class="form-group">
            <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">Link khi bấm vào (tùy chọn)</label>
            <input type="url" id="banner-link" class="form-input" placeholder="https://..." style="font-size:13px">
          </div>
          <button class="btn btn-primary btn-full" id="banner-upload-btn" onclick="uploadBanner()">Tải lên</button>
        </div>

        <div class="table-card" style="margin-bottom:0">
          <h3 style="font-size:15px;font-weight:600;margin-bottom:14px">Danh sách banner <span id="banner-count" style="font-size:12px;font-weight:400;color:var(--text2)"></span></h3>
          <div id="banner-list-loading" style="text-align:center;padding:20px;color:var(--text2);font-size:13px">Đang tải...</div>
          <div id="banner-list"></div>
          <div id="banner-list-empty" class="hidden" style="font-size:13px;color:var(--text3);text-align:center;padding:20px">Chưa có banner nào</div>
        </div>
      </div>

      <!-- TAB: SMTP -->
      <div class="tab-panel" id="panel-smtp">
        <!-- Bank Settings -->
        <div class="table-card" style="margin-bottom:16px">
          <div style="margin-bottom:16px">
            <h3 style="font-size:16px;font-weight:500;margin-bottom:2px">🏦 Tài khoản ngân hàng</h3>
            <div style="font-size:12px;color:var(--text2)">Thông tin hiển thị trên QR chuyển khoản cho khách hàng</div>
          </div>
          <div id="bank-alert" class="alert hidden"></div>
          <div class="smtp-row">
            <div class="form-group" style="margin:0">
              <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">Mã ngân hàng (VietQR)</label>
              <input type="text" id="bank-id" class="form-input" style="font-size:13px" placeholder="OCB, BIDV, VCB...">
              <div style="font-size:11px;color:var(--text3);margin-top:4px">Dùng tên viết tắt hoặc BIN. <a href="https://www.vietqr.io/danh-sach-ngan-hang" target="_blank" style="color:var(--green)">Xem danh sách →</a></div>
            </div>
            <div class="form-group" style="margin:0">
              <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">Số tài khoản</label>
              <input type="text" id="bank-account" class="form-input" style="font-size:13px" placeholder="0123456789">
            </div>
          </div>
          <div class="smtp-row">
            <div class="form-group" style="margin:0">
              <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">Chủ tài khoản</label>
              <input type="text" id="bank-owner" class="form-input" style="font-size:13px" placeholder="NGUYEN VAN A">
            </div>
            <div class="form-group" style="margin:0">
              <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">Tên ngân hàng (hiển thị)</label>
              <input type="text" id="bank-name" class="form-input" style="font-size:13px" placeholder="OCB - Ngân hàng Phương Đông">
            </div>
          </div>
          <button class="btn btn-primary btn-full" onclick="saveBank()">Lưu thông tin ngân hàng</button>
        </div>

        <!-- Pricing Settings -->
        <div class="table-card" style="margin-bottom:16px">
          <div style="margin-bottom:16px">
            <h3 style="font-size:16px;font-weight:500;margin-bottom:2px">💰 Bảng giá & Gói tập</h3>
            <div style="font-size:12px;color:var(--text2)">Thay đổi giá sẽ áp dụng cho đơn hàng mới</div>
          </div>
          <div id="pricing-alert" class="alert hidden"></div>

          <!-- Dynamic Packages -->
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
            <div style="font-size:13px;font-weight:500;color:var(--green-dark)">Gói thẻ tập</div>
            <button class="btn btn-outline" style="font-size:12px;padding:5px 12px" onclick="openPkgModal()">+ Thêm gói</button>
          </div>
          <div id="pkg-list" style="margin-bottom:16px"></div>
          <div id="pkg-list-empty" class="hidden" style="font-size:13px;color:var(--text3);text-align:center;padding:16px;background:var(--bg2);border-radius:var(--radius);margin-bottom:16px">Chưa có gói tập nào</div>

          <div style="font-size:13px;font-weight:500;margin-bottom:8px;margin-top:12px;color:var(--green-dark)">Giá lẻ theo khung giờ</div>
          <div class="smtp-row">
            <div class="form-group" style="margin:0">
              <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">Sáng (8h - 11h)</label>
              <input type="number" id="price-morning" class="form-input" style="font-size:13px" min="0" step="5000">
            </div>
            <div class="form-group" style="margin:0">
              <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">Trưa (11h - 16h)</label>
              <input type="number" id="price-noon" class="form-input" style="font-size:13px" min="0" step="5000">
            </div>
          </div>
          <div class="smtp-row">
            <div class="form-group" style="margin:0">
              <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">Chiều/Tối (16h - 22h)</label>
              <input type="number" id="price-evening" class="form-input" style="font-size:13px" min="0" step="5000">
            </div>
            <div class="form-group" style="margin:0">
              <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">Khu vui chơi trẻ em (/trẻ)</label>
              <input type="number" id="price-kids" class="form-input" style="font-size:13px" min="0" step="5000">
            </div>
          </div>
          <button class="btn btn-primary btn-full" onclick="savePricing()" style="margin-top:4px">Lưu bảng giá</button>
        </div>

        <!-- SMTP Settings -->
        <div class="table-card" style="margin-bottom:0">
          <div style="margin-bottom:16px">
            <h3 style="font-size:16px;font-weight:500;margin-bottom:2px">✉ Cấu hình Email SMTP</h3>
            <div style="font-size:12px;color:var(--text2)">Gửi email quên mật khẩu và thông báo cho thành viên</div>
          </div>
          <div id="smtp-alert" class="alert hidden"></div>
          <div class="smtp-row">
            <div class="form-group" style="margin:0">
              <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">Loại SMTP</label>
              <select id="smtp-host" class="form-input" style="font-size:13px" onchange="onSmtpHostChange(this)">
                <option value="smtp.gmail.com">Gmail</option>
                <option value="smtp.mail.yahoo.com">Yahoo Mail</option>
                <option value="smtp.office365.com">Outlook</option>
              </select>
            </div>
            <div class="form-group" style="margin:0">
              <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">Port</label>
              <input type="number" id="smtp-port" class="form-input" style="font-size:13px;background:var(--bg2)" value="587" readonly>
            </div>
          </div>
          <div class="form-group">
            <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">Email gửi đi (Gmail)</label>
            <input type="email" id="smtp-user" class="form-input" placeholder="your@gmail.com">
          </div>
          <div class="form-group">
            <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">
              App Password — Mật khẩu ứng dụng
              <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:var(--green);font-weight:400;margin-left:6px;font-size:11px">Tạo tại đây →</a>
            </label>
            <div class="pw-wrap">
              <input type="password" id="smtp-pass" class="form-input" placeholder="xxxx xxxx xxxx xxxx">
              <button class="pw-toggle" onclick="togglePw('smtp-pass',this)">👁</button>
            </div>
            <div id="smtp-pass-hint" style="font-size:11px;color:var(--text3);margin-top:4px"></div>
          </div>
          <div class="form-group">
            <label style="font-size:12px;color:var(--text2);margin-bottom:4px;display:block">Tên người gửi</label>
            <input type="text" id="smtp-from-name" class="form-input" value="Wonder Pickleball">
          </div>
          <div style="background:#E6F1FB;border:0.5px solid #B5D4F4;border-radius:var(--radius);padding:12px;font-size:12px;color:#185FA5;margin-bottom:14px;line-height:1.8">
            <strong>Hướng dẫn Gmail App Password:</strong><br>
            1. Vào Google Account → Bảo mật → Bật xác minh 2 bước<br>
            2. Vào <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:#185FA5">App Passwords</a> → Tạo mới → Chọn "Mail"<br>
            3. Copy 16 ký tự → dán vào ô App Password bên trên
          </div>
          <div style="display:flex;gap:8px;margin-bottom:12px">
            <input type="email" id="smtp-test-email" class="form-input" placeholder="Email nhận test..." style="flex:1;font-size:13px">
            <button class="btn btn-outline" style="white-space:nowrap;font-size:13px" onclick="testSmtp()" id="test-smtp-btn">Gửi test</button>
          </div>
          <button class="btn btn-primary btn-full" onclick="saveSmtp()">Lưu cấu hình</button>
        </div>
      </div>

    </div><!-- /admin-panel -->
  </main>
</div>

<!-- Add Product Modal -->
<div id="add-product-modal" class="modal-overlay hidden" onclick="if(event.target===this)closeAddProductModal()">
  <div class="modal" style="max-width:420px">
    <button class="modal-close" onclick="closeAddProductModal()">✕</button>
    <div class="modal-title" id="add-product-title">Thêm sản phẩm</div>
    <input type="hidden" id="ap-id">
    <div id="ap-alert" class="alert hidden"></div>
    <div class="form-group"><label>Tên sản phẩm *</label><input type="text" id="ap-name" class="form-input" placeholder="Vd: Vợt Yonex, Bóng..."></div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
      <div class="form-group"><label>Đơn vị tính</label><input type="text" id="ap-unit" class="form-input" placeholder="cái, hộp, chai..." value="cái"></div>
      <div class="form-group"><label>Tồn kho ban đầu</label><input type="number" id="ap-stock" class="form-input" placeholder="0" min="0" value="0"></div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
      <div class="form-group"><label>Giá nhập (đ)</label><input type="number" id="ap-cost" class="form-input" placeholder="0" min="0"></div>
      <div class="form-group"><label>Giá bán (đ)</label><input type="number" id="ap-sell" class="form-input" placeholder="0" min="0"></div>
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeAddProductModal()">Hủy</button>
      <button class="btn btn-primary" onclick="saveProduct()">Lưu</button>
    </div>
  </div>
</div>

<!-- Stock In Modal -->
<div id="stockin-modal" class="modal-overlay hidden" onclick="if(event.target===this)closeStockinModal()">
  <div class="modal" style="max-width:380px">
    <button class="modal-close" onclick="closeStockinModal()">✕</button>
    <div class="modal-title">Nhập kho</div>
    <input type="hidden" id="si-product-id">
    <div id="si-alert" class="alert hidden"></div>
    <div class="form-group"><label id="si-product-label">Sản phẩm</label></div>
    <div class="form-group"><label>Số lượng nhập *</label><input type="number" id="si-quantity" class="form-input" placeholder="0" min="1"></div>
    <div class="form-group"><label>Ghi chú</label><input type="text" id="si-note" class="form-input" placeholder="Nhập hàng từ..."></div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeStockinModal()">Hủy</button>
      <button class="btn btn-primary" onclick="saveStockin()">Nhập kho</button>
    </div>
  </div>
</div>

<!-- Sale Detail Modal -->
<div id="sale-detail-modal" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
  <div class="modal" style="max-width:400px">
    <button class="modal-close" onclick="document.getElementById('sale-detail-modal').classList.add('hidden')">✕</button>
    <div class="modal-title">Chi tiết đơn bán</div>
    <div id="sale-detail-content"></div>
  </div>
</div>

<!-- Add Sessions Modal -->
<div id="add-modal" class="modal-overlay hidden">
  <div class="modal">
    <button class="modal-close" onclick="closeAddModal()">✕</button>
    <div class="modal-title">Cộng buổi tập</div>
    <div id="add-modal-info" class="add-modal-info"></div>
    <div class="pkg-grid" style="margin-bottom:14px" id="add-pkg-grid">
      <!-- Dynamic packages rendered by JS -->
    </div>
    <div class="form-group">
      <label>Hoặc nhập số buổi tùy ý</label>
      <input type="number" id="custom-sessions" class="form-input" min="1" max="100" placeholder="VD: 5" oninput="selectCustomAdd(this)">
    </div>
    <div class="form-group">
      <label>Ghi chú (tuỳ chọn)</label>
      <input type="text" id="add-note" class="form-input" placeholder="VD: Khuyến mãi tháng 3">
    </div>
    <div id="add-modal-alert" class="alert hidden"></div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeAddModal()">Hủy</button>
      <button class="btn btn-primary" onclick="confirmAdd()">Xác nhận cộng</button>
    </div>
  </div>
</div>

<!-- Edit Member Modal -->
<div id="edit-modal" class="modal-overlay hidden" onclick="if(event.target===this)closeEditModal()">
  <div class="modal" style="max-width:440px">
    <button class="modal-close" onclick="closeEditModal()">✕</button>
    <div class="modal-title">Chỉnh sửa thành viên</div>
    <input type="hidden" id="edit-phone-key">
    <div id="edit-modal-alert" class="alert hidden"></div>
    <div class="form-group">
      <label style="font-size:12px;color:var(--text2)">Họ tên</label>
      <input type="text" id="edit-name" class="form-input">
    </div>
    <div class="form-group">
      <label style="font-size:12px;color:var(--text2)">Email</label>
      <input type="email" id="edit-email" class="form-input" placeholder="example@gmail.com">
    </div>
    <div class="smtp-row">
      <div class="form-group" style="margin:0">
        <label style="font-size:12px;color:var(--text2)">Buổi còn lại</label>
        <input type="number" id="edit-sessions" class="form-input" min="0">
      </div>
      <div class="form-group" style="margin:0">
        <label style="font-size:12px;color:var(--text2)">Tổng buổi (max)</label>
        <input type="number" id="edit-max-sessions" class="form-input" min="0">
      </div>
    </div>
    <div class="form-group">
      <label style="font-size:12px;color:var(--text2)">Ngày hết hạn</label>
      <input type="date" id="edit-expiry" class="form-input">
    </div>
    <div class="form-group">
      <label style="font-size:12px;color:var(--text2)">Đặt mật khẩu mới <span style="opacity:.5">(để trống = không đổi)</span></label>
      <input type="text" id="edit-password" class="form-input" placeholder="Nhập mật khẩu mới...">
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeEditModal()">Hủy</button>
      <button class="btn btn-primary" onclick="saveEditMember()">Lưu thay đổi</button>
    </div>
  </div>
</div>

<!-- Bulk Create Modal -->
<div id="bulk-modal" class="modal-overlay hidden" onclick="if(event.target===this)closeBulkModal()">
  <div class="modal" style="max-width:820px">
    <button class="modal-close" onclick="closeBulkModal()">✕</button>
    <div class="modal-title">Tạo tài khoản khách hàng hàng loạt</div>
    <div id="bulk-modal-alert" class="alert hidden"></div>
    <div style="font-size:12px;color:var(--text2);margin-bottom:10px">
      Mật khẩu mặc định: <strong>123456</strong> · SĐT thiếu số 0 đầu sẽ tự động thêm
    </div>
    <div style="overflow-x:auto;max-height:50vh">
      <table class="data-table" style="font-size:13px" id="bulk-table">
        <thead><tr>
          <th style="width:32px">#</th>
          <th>Họ tên *</th>
          <th style="width:140px">SĐT *</th>
          <th>Email</th>
          <th style="width:90px">Buổi</th>
          <th style="width:40px"></th>
        </tr></thead>
        <tbody id="bulk-tbody"></tbody>
      </table>
    </div>
    <div style="display:flex;gap:8px;margin-top:10px">
      <button class="btn btn-ghost" style="font-size:12px;padding:6px 12px" onclick="bulkAddRow()">+ Thêm dòng</button>
      <button class="btn btn-ghost" style="font-size:12px;padding:6px 12px" onclick="bulkAddRow(10)">+ 10 dòng</button>
      <span style="margin-left:auto;font-size:12px;color:var(--text3);align-self:center"><span id="bulk-row-count">0</span> dòng</span>
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeBulkModal()">Hủy</button>
      <button class="btn btn-primary" id="bulk-save-btn" onclick="bulkSave()">Tạo tài khoản</button>
    </div>
  </div>
</div>

<!-- Order QR Preview Modal -->
<div id="order-qr-modal" class="modal-overlay hidden" onclick="if(event.target===this)closeOrderQR()">
  <div class="modal" style="max-width:360px">
    <button class="modal-close" onclick="closeOrderQR()">✕</button>
    <div class="modal-title" id="oqr-title">QR đơn hàng</div>
    <div style="text-align:center;margin-bottom:12px">
      <img id="oqr-img" src="" style="max-width:260px;width:100%;border-radius:var(--radius-lg);border:1px solid var(--border)" alt="QR">
    </div>
    <table class="ck-table" style="width:100%">
      <tr><td>Mã đơn</td><td id="oqr-code" style="font-family:'DM Mono',monospace;font-weight:500"></td></tr>
      <tr><td>Số tiền</td><td id="oqr-amount" style="color:var(--green);font-weight:600"></td></tr>
      <tr><td>Tài khoản</td><td id="oqr-bank-info">—</td></tr>
    </table>
    <div style="margin-top:12px">
      <button class="btn btn-primary btn-full" onclick="approveFromQR()" id="oqr-approve-btn">✓ Duyệt đơn hàng này</button>
    </div>
  </div>
</div>

<!-- Package Add/Edit Modal -->
<div id="pkg-modal" class="modal-overlay hidden" onclick="if(event.target===this)closePkgModal()">
  <div class="modal" style="max-width:440px">
    <button class="modal-close" onclick="closePkgModal()">✕</button>
    <div class="modal-title" id="pkg-modal-title">Thêm gói tập</div>
    <input type="hidden" id="pkg-edit-id" value="">
    <div id="pkg-modal-alert" class="alert hidden"></div>
    <div class="form-group">
      <label style="font-size:12px;color:var(--text2)">Tên gói</label>
      <input type="text" id="pkg-name" class="form-input" placeholder="VD: Gói 10 tặng 3">
    </div>
    <div class="smtp-row">
      <div class="form-group" style="margin:0">
        <label style="font-size:12px;color:var(--text2)">Số buổi</label>
        <input type="number" id="pkg-sessions" class="form-input" min="1" placeholder="VD: 13">
      </div>
      <div class="form-group" style="margin:0">
        <label style="font-size:12px;color:var(--text2)">Giá (VNĐ)</label>
        <input type="number" id="pkg-price" class="form-input" min="0" step="10000" placeholder="VD: 600000">
      </div>
    </div>
    <div class="smtp-row">
      <div class="form-group" style="margin:0">
        <label style="font-size:12px;color:var(--text2)">HSD (ngày)</label>
        <input type="number" id="pkg-expiry" class="form-input" min="1" value="30" placeholder="30">
      </div>
      <div class="form-group" style="margin:0">
        <label style="font-size:12px;color:var(--text2)">Badge (tùy chọn)</label>
        <input type="text" id="pkg-badge" class="form-input" placeholder="VD: Phổ biến, Hot...">
      </div>
    </div>
    <div class="form-group">
      <label style="font-size:12px;color:var(--text2)">Thứ tự hiển thị</label>
      <input type="number" id="pkg-sort" class="form-input" min="0" value="0" placeholder="0">
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closePkgModal()">Hủy</button>
      <button class="btn btn-primary" onclick="savePkg()" id="pkg-save-btn">Lưu gói tập</button>
    </div>
  </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script src="assets/js/app.js"></script>
<script>
const API_BASE  = 'api/customers.php';
const API_ORDERS = 'api/orders.php';
const API_SET   = 'api/settings.php';
const API_BANNERS = 'api/banners.php';
let adminToken   = null;
let allCustomers = [];
let allOrders    = [];
let currentFilterStatus = '';
let addTargetPhone = null, addSessions = 13, addPkg = 'pkg_10';
let currentQROrderId = null;
let autoRefreshTimer = null;

// Report pagination
const RPT_PER_PAGE = 10;
let ciPage = 1, ciData = [];
let lowPage = 1, lowData = [];

// Banners
let bannerFile = null;

async function adminLogin() {
  const pw = document.getElementById('admin-pw').value;
  if (!pw) { showAlert('login-alert','Nhập mật khẩu','warn'); return; }
  try {
    const res = await fetch('api/auth.php?action=admin_login', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({password: pw})
    });
    const json = await res.json();
    if (json.error) { showAlert('login-alert', json.error, 'error'); return; }
    adminToken = json.token;
    localStorage.setItem('wp_admin_pw', pw);
    loadDashboard();
  } catch(e) {
    showAlert('login-alert','Lỗi kết nối server','error');
  }
}

function adminLogout() {
  adminToken = null;
  localStorage.removeItem('wp_admin_pw');
  clearInterval(autoRefreshTimer);
  document.getElementById('login-panel').classList.remove('hidden');
  document.getElementById('admin-panel').classList.add('hidden');
  document.getElementById('admin-pw').value = '';
}

// Auto-login admin từ localStorage
(async function autoAdminLogin() {
  const savedPw = localStorage.getItem('wp_admin_pw');
  if (!savedPw) return;
  try {
    const res = await fetch('api/auth.php?action=admin_login', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({password: savedPw})
    });
    const json = await res.json();
    if (json.error) { localStorage.removeItem('wp_admin_pw'); return; }
    adminToken = json.token;
    loadDashboard();
  } catch(e) { /* silent fail */ }
})();

async function loadDashboard() {
  try {
    const [custRes, ordRes, setRes] = await Promise.all([
      fetch(`${API_BASE}?action=all&admin_token=${adminToken}`),
      fetch(`${API_ORDERS}?action=list&admin_token=${adminToken}`),
      fetch(`${API_SET}?action=get_all&admin_token=${adminToken}`)
    ]);
    const custJ = await custRes.json();
    const ordJ  = await ordRes.json();
    const setJ  = await setRes.json();

    if (custJ.error) { showAlert('login-alert', custJ.error==='Unauthorized'?'Mật khẩu không đúng':custJ.error,'error'); adminToken=null; return; }

    document.getElementById('login-panel').classList.add('hidden');
    document.getElementById('admin-panel').classList.remove('hidden');

    allCustomers = custJ.customers || [];
    allOrders    = ordJ.orders || [];
    const pending = ordJ.pending || 0;

    document.getElementById('stat-total').textContent  = custJ.stats.total;
    document.getElementById('stat-today').textContent  = custJ.stats.checkins_today;
    document.getElementById('stat-pending').textContent = pending;

    const badge = document.getElementById('orders-badge');
    if (pending > 0) { badge.textContent = pending; badge.classList.remove('hidden'); }
    else badge.classList.add('hidden');

    renderOrders(allOrders);
    renderTable(allCustomers);

    clearInterval(autoRefreshTimer);
    autoRefreshTimer = setInterval(silentRefreshOrders, 15000);

    // Apply settings (đã load song song)
    if (setJ.success) applyAllSettings(setJ);
  } catch(e) {
    showAlert('login-alert','Lỗi kết nối server','error');
  }
}

async function loadOrders() {
  try {
    const res = await fetch(`${API_ORDERS}?action=list&admin_token=${adminToken}`);
    const j = await res.json();
    if (!j.error) {
      allOrders = j.orders || [];
      renderOrders(allOrders);
      const p = j.pending||0;
      document.getElementById('stat-pending').textContent = p;
      const badge = document.getElementById('orders-badge');
      if (p>0) { badge.textContent=p; badge.classList.remove('hidden'); } else badge.classList.add('hidden');
    }
  } catch(e){}
}

async function silentRefreshOrders() {
  if (document.getElementById('panel-orders').classList.contains('active')) loadOrders();
}

// ---- TABS ----
function switchTab(name) {
  document.querySelectorAll('.admin-tab').forEach(t=>t.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  document.getElementById('panel-'+name).classList.add('active');
  if (name==='orders')      loadOrders();
  if (name==='report')      loadReport();
  if (name==='banners')     loadBanners();
  if (name==='inventory')   { loadProducts(); loadInventoryLogs(); }
  if (name==='sales')       {
    const today = new Date().toISOString().slice(0,10);
    document.getElementById('sale-filter-date').value = today;
    loadSales(); renderCart();
  }
  if (name==='salesreport') {
    const today = new Date().toISOString().slice(0,10);
    document.getElementById('sr-from').value = today.slice(0,8)+'01';
    document.getElementById('sr-to').value   = today;
    loadSalesReport();
  }
}

// ---- ORDERS ----
function filterOrders(el, status) {
  document.querySelectorAll('.filter-tab').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');
  currentFilterStatus = status;
  renderOrders(allOrders);
}

function renderOrders(orders) {
  const filtered = currentFilterStatus ? orders.filter(o=>o.payment_status===currentFilterStatus) : orders;
  const el = document.getElementById('orders-list');
  const empty = document.getElementById('orders-empty');

  if (!filtered.length) { el.innerHTML=''; empty.classList.remove('hidden'); return; }
  empty.classList.add('hidden');

  el.innerHTML = filtered.map(o => {
    const isPending = o.payment_status === 'Unpaid';
    const ph = o.phone.replace(/\D/g,'');
    const phFmt = ph.slice(0,4)+' '+ph.slice(4,7)+' '+ph.slice(7);
    const amt = parseInt(o.amount).toLocaleString('vi-VN');
    const sessions = parseInt(o.sessions_to_add);
    const kids = parseInt(o.kids_count||0);

    // VietQR link (regenerate for display)
    const qrUrl = `https://img.vietqr.io/image/${encodeURIComponent(bankData.bank_id||'<?= BANK_ID ?>')}-${bankData.bank_account||'<?= BANK_ACCOUNT ?>'}-compact2.png?amount=${o.amount}&addInfo=${encodeURIComponent(o.order_code)}&accountName=${encodeURIComponent(bankData.bank_owner||'<?= BANK_OWNER ?>')}`;

    let actions = '';
    if (isPending) {
      actions = `
        <button class="btn btn-green" style="font-size:13px;padding:7px 14px" onclick="approveOrder(${o.id},'${esc(o.order_code)}')">
          ✓ Duyệt — cộng ${sessions} buổi
        </button>
        <button class="btn btn-ghost" style="font-size:12px;padding:7px 12px" onclick="previewQR(${o.id},'${esc(o.order_code)}',${o.amount},'${qrUrl}')">
          🔍 Xem QR
        </button>
        <button class="btn btn-outline" style="font-size:12px;padding:7px 10px;color:var(--text3)" onclick="cancelOrder(${o.id})">
          Huỷ
        </button>`;
    }

    return `<div class="order-card ${o.payment_status.toLowerCase()}" id="order-${o.id}">
      <div class="order-header">
        <div>
          <span class="order-code">${esc(o.order_code)}</span>
          <span class="order-status status-${o.payment_status}" style="margin-left:8px">${
            o.payment_status==='Unpaid'?'⏳ Chờ duyệt':o.payment_status==='Paid'?'✓ Đã duyệt':'✕ Đã huỷ'
          }</span>
        </div>
        <div style="font-size:16px;font-weight:600;color:var(--green-dark)">${amt}đ</div>
      </div>
      <div class="order-body">
        <div><span class="lbl">Khách hàng</span></div><div><span class="val">${esc(o.customer_name)}</span></div>
        <div><span class="lbl">Số điện thoại</span></div><div><code>${phFmt}</code></div>
        <div><span class="lbl">Gói</span></div><div><span class="val">${pkgLabel(o.pkg_type,sessions)}${kids>0?' + '+kids+' trẻ em':''}</span></div>
        <div><span class="lbl">Thời gian</span></div><div><span class="val">${formatDateTime(o.created_at)}</span></div>
        ${o.paid_at?`<div><span class="lbl">Duyệt lúc</span></div><div><span class="val" style="color:var(--green)">${formatDateTime(o.paid_at)}</span></div>`:''}
      </div>
      ${actions ? `<div class="order-actions">${actions}</div>` : ''}
    </div>`;
  }).join('');
}

function pkgLabel(pkg, sessions) {
  if (pkg==='single') return 'Lẻ 1 buổi';
  if (pkg==='kids') return 'Khu vui chơi trẻ em';
  const found = allPackages.find(p => p.slug === pkg);
  if (found) return found.name + ' (' + sessions + ' buổi)';
  return pkg + ' (' + sessions + ' buổi)';
}

async function approveOrder(id, code) {
  if (!confirm(`Xác nhận duyệt đơn ${code}?\nHệ thống sẽ tự động cộng buổi tập cho khách.`)) return;
  const card = document.getElementById('order-'+id);
  if (card) { card.style.opacity='.5'; card.style.pointerEvents='none'; }
  try {
    const res = await fetch(`${API_ORDERS}?action=approve`, {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({order_id: id, admin_token: adminToken})
    });
    const json = await res.json();
    if (json.error) { alert('Lỗi: '+json.error); if(card){card.style.opacity='1';card.style.pointerEvents='';} return; }
    // Success - reload
    await loadDashboard();
  } catch(e) { alert('Lỗi kết nối'); if(card){card.style.opacity='1';card.style.pointerEvents='';} }
}

async function cancelOrder(id) {
  if (!confirm('Huỷ đơn hàng này?')) return;
  try {
    const res = await fetch(`${API_ORDERS}?action=cancel`, {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({order_id: id, admin_token: adminToken})
    });
    await loadOrders();
  } catch(e) {}
}

function previewQR(id, code, amount, qrUrl) {
  currentQROrderId = id;
  document.getElementById('oqr-title').textContent = 'Đơn hàng ' + code;
  document.getElementById('oqr-img').src = qrUrl;
  document.getElementById('oqr-code').textContent = code;
  document.getElementById('oqr-amount').textContent = parseInt(amount).toLocaleString('vi-VN') + 'đ';
  document.getElementById('order-qr-modal').classList.remove('hidden');
}

function closeOrderQR() { document.getElementById('order-qr-modal').classList.add('hidden'); }

function approveFromQR() {
  closeOrderQR();
  if (currentQROrderId) approveOrder(currentQROrderId, document.getElementById('oqr-code').textContent);
}

// ---- MEMBERS + PAGINATION ----
let memberPage = 1, memberTotalPages = 1, memberFiltered = [];

function renderTable(customers) {
  memberFiltered = customers;
  memberPage = 1;
  renderMemberPage();
}

function renderMemberPage() {
  const tbody = document.getElementById('customer-tbody');
  const noData = document.getElementById('no-data');
  const pager = document.getElementById('member-pager');
  const pageSize = parseInt(document.getElementById('member-page-size').value) || 20;
  const total = memberFiltered.length;
  if (!total) { tbody.innerHTML=''; noData.classList.remove('hidden'); pager.style.display='none'; return; }
  noData.classList.add('hidden');
  memberTotalPages = Math.ceil(total / pageSize);
  if (memberPage < 1) memberPage = 1;
  if (memberPage > memberTotalPages) memberPage = memberTotalPages;
  const from = (memberPage - 1) * pageSize;
  const slice = memberFiltered.slice(from, from + pageSize);
  tbody.innerHTML = slice.map(c => {
    const s = parseInt(c.sessions);
    const badge = s===0?'badge-zero':s<=3?'badge-low':s<=6?'badge-mid':'badge-ok';
    const ph = c.phone.replace(/\D/g,'');
    const phFmt = ph.slice(0,4)+' '+ph.slice(4,7)+' '+ph.slice(7);
    const expiry = c.expires_at ? `<span style="font-size:11px;color:${new Date(c.expires_at)<new Date()?'var(--red)':'var(--text3)'}">${new Date(c.expires_at).toLocaleDateString('vi-VN')}</span>` : '—';
    return `<tr>
      <td style="white-space:nowrap"><strong>${esc(c.name)}</strong></td>
      <td style="white-space:nowrap"><code style="font-size:12px">${phFmt}</code></td>
      <td class="col-hide-mobile" style="font-size:12px;color:var(--text2);max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${esc(c.email||'—')}</td>
      <td style="text-align:center"><span class="sess-badge ${badge}">${s}</span></td>
      <td class="col-hide-mobile" style="white-space:nowrap">${expiry}</td>
      <td style="white-space:nowrap;text-align:right">
        <button class="btn btn-sm btn-outline" style="font-size:11px;padding:3px 8px" onclick="openEditModal('${esc(c.phone)}')">Sửa</button>
        <button class="btn btn-sm btn-primary" style="font-size:11px;padding:3px 8px" onclick="openAddModal('${esc(c.phone)}','${esc(c.name)}',${s})">Cộng</button>
        <button class="btn btn-sm" style="font-size:11px;padding:3px 8px;color:var(--red);border:1px solid var(--red);background:none" onclick="confirmDeleteCustomer('${esc(c.phone)}','${esc(c.name)}')">Xóa</button>
      </td>
    </tr>`;
  }).join('');
  pager.style.display = 'flex';
  document.getElementById('member-page-from').textContent = from + 1;
  document.getElementById('member-page-to').textContent = Math.min(from + pageSize, total);
  document.getElementById('member-page-total').textContent = total;
  document.getElementById('member-page-info').textContent = `Trang ${memberPage}/${memberTotalPages}`;
  document.getElementById('member-first').disabled = memberPage <= 1;
  document.getElementById('member-prev').disabled = memberPage <= 1;
  document.getElementById('member-next').disabled = memberPage >= memberTotalPages;
  document.getElementById('member-last').disabled = memberPage >= memberTotalPages;
}

function memberGoPage(p) { memberPage = p; renderMemberPage(); }
function memberPageSizeChange() { memberPage = 1; renderMemberPage(); }

function filterList(v) {
  const q = v.toLowerCase();
  renderTable(!q ? allCustomers : allCustomers.filter(c =>
    c.name.toLowerCase().includes(q) ||
    c.phone.includes(v.replace(/\D/g,'')) ||
    (c.email||'').toLowerCase().includes(q)
  ));
}

function confirmDeleteCustomer(phone, name) {
  const input = prompt('Xóa tài khoản "' + name + '" (' + phone + ')?\nNhập "xóa" để xác nhận:');
  if (input === null) return;
  if (input.trim().toLowerCase() !== 'xóa' && input.trim().toLowerCase() !== 'xoa') {
    alert('Bạn chưa nhập đúng "xóa". Hủy thao tác.');
    return;
  }
  deleteCustomer(phone);
}
async function deleteCustomer(phone) {
  try {
    const res = await fetch(`${API_BASE}?action=delete_customer`, {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({phone, admin_token: adminToken})
    });
    const json = await res.json();
    if (json.error) { alert('Lỗi: ' + json.error); return; }
    loadDashboard();
  } catch(e) { alert('Lỗi kết nối'); }
}

function openAddModal(phone, name, sessions) {
  addTargetPhone=phone;
  document.getElementById('add-modal-info').innerHTML = `<strong>${name}</strong> — Hiện có <strong>${sessions}</strong> buổi`;
  // Render dynamic pkg cards
  const grid = document.getElementById('add-pkg-grid');
  let html = '';
  if (allPackages.length) {
    html = allPackages.map((p, i) => {
      const badge = p.badge ? `<div class="pkg-badge">${esc(p.badge)}</div>` : '';
      return `<div class="pkg-card${i===0?' selected':''}" onclick="selectAddPkg(this,'${esc(p.slug)}',${p.sessions})">
        ${badge}<div class="pkg-sessions">+${p.sessions}</div><div class="pkg-name">${esc(p.name)}</div>
      </div>`;
    }).join('');
  }
  html += `<div class="pkg-card${!allPackages.length?' selected':''}" onclick="selectAddPkg(this,'single',1)">
    <div class="pkg-sessions" style="font-size:28px">+1</div><div class="pkg-name">Lẻ 1 buổi</div>
  </div>`;
  grid.innerHTML = html;
  // Set default
  if (allPackages.length) { addPkg = allPackages[0].slug; addSessions = allPackages[0].sessions; }
  else { addPkg = 'single'; addSessions = 1; }
  document.getElementById('custom-sessions').value='';
  document.getElementById('add-note').value='';
  hideAlert('add-modal-alert');
  document.getElementById('add-modal').classList.remove('hidden');
}

function selectAddPkg(el, pkg, sessions) {
  document.querySelectorAll('#add-modal .pkg-card').forEach(x=>x.classList.remove('selected'));
  el.classList.add('selected'); addPkg=pkg; addSessions=sessions;
  document.getElementById('custom-sessions').value='';
}

function selectCustomAdd(el) {
  const v=parseInt(el.value);
  if(v>0){document.querySelectorAll('#add-modal .pkg-card').forEach(x=>x.classList.remove('selected'));addPkg='manual';addSessions=v;}
}

async function confirmAdd() {
  const cv=parseInt(document.getElementById('custom-sessions').value);
  if(cv>0){addSessions=cv;addPkg='manual';}
  if(!addSessions||addSessions<=0){showAlert('add-modal-alert','Chọn số buổi','warn');return;}
  const note=document.getElementById('add-note').value.trim()||'Admin cộng thủ công';
  try {
    const res=await fetch(`${API_BASE}?action=add_sessions`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({phone:addTargetPhone,sessions:addSessions,pkg:addPkg,note,admin_token:adminToken})});
    const json=await res.json();
    if(json.error){showAlert('add-modal-alert',json.error,'error');return;}
    closeAddModal(); loadDashboard();
  } catch(e){showAlert('add-modal-alert','Lỗi kết nối','error');}
}

function closeAddModal(){document.getElementById('add-modal').classList.add('hidden');}

// ---- EDIT MEMBER ----
function openEditModal(phone) {
  const c = allCustomers.find(x => x.phone === phone);
  if (!c) return;
  document.getElementById('edit-phone-key').value = c.phone;
  document.getElementById('edit-name').value = c.name;
  document.getElementById('edit-email').value = c.email || '';
  document.getElementById('edit-sessions').value = c.sessions;
  document.getElementById('edit-max-sessions').value = c.max_sessions;
  document.getElementById('edit-expiry').value = c.expires_at || '';
  document.getElementById('edit-password').value = '';
  hideAlert('edit-modal-alert');
  document.getElementById('edit-modal').classList.remove('hidden');
}

function closeEditModal(){document.getElementById('edit-modal').classList.add('hidden');}

async function saveEditMember() {
  const phone = document.getElementById('edit-phone-key').value;
  const data = {
    phone,
    name: document.getElementById('edit-name').value.trim(),
    email: document.getElementById('edit-email').value.trim(),
    sessions: parseInt(document.getElementById('edit-sessions').value) || 0,
    max_sessions: parseInt(document.getElementById('edit-max-sessions').value) || 0,
    expires_at: document.getElementById('edit-expiry').value || null,
    admin_token: adminToken
  };
  const newPw = document.getElementById('edit-password').value;
  if (newPw) {
    if (newPw.length < 6) { showAlert('edit-modal-alert','Mật khẩu tối thiểu 6 ký tự','warn'); return; }
    data.new_password = newPw;
  }
  if (!data.name) { showAlert('edit-modal-alert','Tên không được để trống','warn'); return; }
  try {
    const res = await fetch(`${API_BASE}?action=update_customer`, {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify(data)
    });
    const json = await res.json();
    if (json.error) { showAlert('edit-modal-alert', json.error, 'error'); return; }
    closeEditModal();
    loadDashboard();
  } catch(e) { showAlert('edit-modal-alert','Lỗi kết nối','error'); }
}

// ---- BULK CREATE ----
function openBulkModal() {
  document.getElementById('bulk-tbody').innerHTML = '';
  hideAlert('bulk-modal-alert');
  bulkAddRow(5);
  document.getElementById('bulk-modal').classList.remove('hidden');
}
function closeBulkModal() { document.getElementById('bulk-modal').classList.add('hidden'); }
function bulkAddRow(count=1) {
  const tbody = document.getElementById('bulk-tbody');
  for (let i=0; i<count; i++) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td style="color:var(--text3);font-size:12px" class="bulk-idx"></td>
      <td><input type="text" class="form-input bulk-name" style="font-size:13px;padding:6px 8px" placeholder="Họ tên"></td>
      <td><input type="tel" class="form-input bulk-phone" style="font-size:13px;padding:6px 8px" placeholder="0901234567"></td>
      <td><input type="email" class="form-input bulk-email" style="font-size:13px;padding:6px 8px" placeholder="email@..."></td>
      <td><input type="number" class="form-input bulk-sessions" style="font-size:13px;padding:6px 8px" min="0" value="0"></td>
      <td><button class="btn btn-ghost" style="font-size:14px;padding:4px 8px;color:var(--red)" onclick="this.closest('tr').remove();bulkRenum()">×</button></td>
    `;
    tbody.appendChild(tr);
  }
  bulkRenum();
}
function bulkRenum() {
  const rows = document.querySelectorAll('#bulk-tbody tr');
  rows.forEach((r,i) => { r.querySelector('.bulk-idx').textContent = i+1; });
  document.getElementById('bulk-row-count').textContent = rows.length;
}
async function bulkSave() {
  const rows = document.querySelectorAll('#bulk-tbody tr');
  const customers = [];
  rows.forEach(r => {
    const name = r.querySelector('.bulk-name').value.trim();
    const phone = r.querySelector('.bulk-phone').value.trim();
    const email = r.querySelector('.bulk-email').value.trim();
    const sessions = parseInt(r.querySelector('.bulk-sessions').value) || 0;
    if (name || phone) customers.push({name, phone, email, sessions});
  });
  if (customers.length === 0) { showAlert('bulk-modal-alert','Chưa nhập dữ liệu','warn'); return; }
  const btn = document.getElementById('bulk-save-btn');
  btn.disabled = true; btn.textContent = 'Đang tạo...';
  try {
    const res = await fetch(`${API_BASE}?action=bulk_create`, {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({admin_token: adminToken, default_password: '123456', customers})
    });
    const json = await res.json();
    if (json.error) { showAlert('bulk-modal-alert', json.error, 'error'); return; }
    let msg = `Đã tạo ${json.created} tài khoản`;
    if (json.skipped > 0) msg += ` · Bỏ qua ${json.skipped} dòng`;
    if (json.errors && json.errors.length > 0) msg += '\n• ' + json.errors.slice(0,5).join('\n• ');
    showAlert('bulk-modal-alert', msg, json.created > 0 ? 'success' : 'warn');
    if (json.created > 0) {
      setTimeout(() => { closeBulkModal(); loadDashboard(); }, 1500);
    }
  } catch(e) {
    showAlert('bulk-modal-alert','Lỗi kết nối','error');
  } finally {
    btn.disabled = false; btn.textContent = 'Tạo tài khoản';
  }
}

// ---- EXPORT ----
function exportMembers() {
  window.open(`${API_BASE}?action=export_csv&admin_token=${adminToken}`, '_blank');
}

// ---- SMTP ----
// ---- ALL SETTINGS (1 call thay vì 3) ----
function applyAllSettings(json) {
  // SMTP
  const s = json.smtp || {};
  document.getElementById('smtp-host').value = s.smtp_host || 'smtp.gmail.com';
  document.getElementById('smtp-port').value = s.smtp_port || 587;
  document.getElementById('smtp-user').value = s.smtp_user || '';
  document.getElementById('smtp-from-name').value = s.smtp_from_name || 'Wonder Pickleball';
  if (s.smtp_pass_set) document.getElementById('smtp-pass-hint').textContent = '✓ Mật khẩu đã được lưu. Điền lại nếu muốn thay đổi.';
  // Bank
  const b = json.bank || {};
  bankData = b;
  document.getElementById('bank-id').value = b.bank_id || '';
  document.getElementById('bank-account').value = b.bank_account || '';
  document.getElementById('bank-owner').value = b.bank_owner || '';
  document.getElementById('bank-name').value = b.bank_name || '';
  document.getElementById('oqr-bank-info').textContent = (b.bank_account||'') + ' (' + (b.bank_name||'') + ')';
  // Pricing (slot + kids only)
  const p = json.pricing || {};
  document.getElementById('price-morning').value = p.price_social_morning || 0;
  document.getElementById('price-noon').value = p.price_social_noon || 0;
  document.getElementById('price-evening').value = p.price_social_evening || 0;
  document.getElementById('price-kids').value = p.price_kids || 0;
  // Packages
  if (json.packages) renderPackages(json.packages);
}

async function loadAllSettings() {
  try {
    const res = await fetch(`${API_SET}?action=get_all&admin_token=${adminToken}`);
    const json = await res.json();
    if (json.success) applyAllSettings(json);
  } catch(e) {}
}

async function loadSmtpSettings() {
  try {
    const res=await fetch(`${API_SET}?action=get_smtp&admin_token=${adminToken}`);
    const json=await res.json();
    if(json.data){
      const d=json.data;
      document.getElementById('smtp-host').value=d.smtp_host||'smtp.gmail.com';
      document.getElementById('smtp-port').value=d.smtp_port||587;
      document.getElementById('smtp-user').value=d.smtp_user||'';
      document.getElementById('smtp-from-name').value=d.smtp_from_name||'Wonder Pickleball';
      if(d.smtp_pass_set) document.getElementById('smtp-pass-hint').textContent='✓ Mật khẩu đã được lưu. Điền lại nếu muốn thay đổi.';
    }
  } catch(e){}
}

function onSmtpHostChange(sel) {
  const ports={gmail:587,'smtp.mail.yahoo.com':587,'smtp.office365.com':587};
  document.getElementById('smtp-port').value=587;
}

function togglePw(id,btn){const el=document.getElementById(id);if(el.type==='password'){el.type='text';btn.textContent='🙈';}else{el.type='password';btn.textContent='👁';}}

async function saveSmtp() {
  const data={smtp_host:document.getElementById('smtp-host').value,smtp_port:document.getElementById('smtp-port').value,smtp_user:document.getElementById('smtp-user').value,smtp_pass:document.getElementById('smtp-pass').value,smtp_from_name:document.getElementById('smtp-from-name').value,smtp_enabled:'1',admin_token:adminToken};
  try {
    const res=await fetch(`${API_SET}?action=save_smtp`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});
    const json=await res.json();
    if(json.error){showAlert('smtp-alert',json.error,'error');}
    else{showAlert('smtp-alert','✓ Đã lưu cấu hình SMTP','success');document.getElementById('smtp-pass').value='';loadAllSettings();}
  }catch(e){showAlert('smtp-alert','Lỗi kết nối','error');}
}

async function testSmtp() {
  const email=document.getElementById('smtp-test-email').value.trim();
  if(!email){showAlert('smtp-alert','Nhập email test','warn');return;}
  const btn=document.getElementById('test-smtp-btn');
  btn.disabled=true;btn.textContent='Đang gửi...';
  try {
    const res=await fetch(`${API_SET}?action=test_smtp`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({test_email:email,admin_token:adminToken})});
    const json=await res.json();
    if(json.error)showAlert('smtp-alert',json.error,'error');
    else showAlert('smtp-alert','✓ '+json.message,'success');
  }catch(e){showAlert('smtp-alert','Lỗi kết nối','error');}
  btn.disabled=false;btn.textContent='Gửi test';
}

// ---- BANK SETTINGS ----
let bankData = {};
async function loadBankSettings() {
  try {
    const res = await fetch(`${API_SET}?action=get_bank&admin_token=${adminToken}`);
    const json = await res.json();
    if (json.data) {
      bankData = json.data;
      document.getElementById('bank-id').value = json.data.bank_id || '';
      document.getElementById('bank-account').value = json.data.bank_account || '';
      document.getElementById('bank-owner').value = json.data.bank_owner || '';
      document.getElementById('bank-name').value = json.data.bank_name || '';
      document.getElementById('oqr-bank-info').textContent = (json.data.bank_account||'') + ' (' + (json.data.bank_name||'') + ')';
    }
  } catch(e) {}
}

async function saveBank() {
  const data = {
    bank_id: document.getElementById('bank-id').value.trim(),
    bank_account: document.getElementById('bank-account').value.trim(),
    bank_owner: document.getElementById('bank-owner').value.trim(),
    bank_name: document.getElementById('bank-name').value.trim(),
    admin_token: adminToken
  };
  if (!data.bank_id || !data.bank_account || !data.bank_owner) {
    showAlert('bank-alert','Vui lòng điền đầy đủ thông tin','warn'); return;
  }
  try {
    const res = await fetch(`${API_SET}?action=save_bank`, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)});
    const json = await res.json();
    if (json.error) showAlert('bank-alert', json.error, 'error');
    else { showAlert('bank-alert','✓ Đã lưu thông tin ngân hàng','success'); loadAllSettings(); }
  } catch(e) { showAlert('bank-alert','Lỗi kết nối','error'); }
}

// ---- PRICING ----
let allPackages = [];

async function savePricing() {
  const data = {
    price_social_morning: parseInt(document.getElementById('price-morning').value) || 0,
    price_social_noon: parseInt(document.getElementById('price-noon').value) || 0,
    price_social_evening: parseInt(document.getElementById('price-evening').value) || 0,
    price_kids: parseInt(document.getElementById('price-kids').value) || 0,
    admin_token: adminToken
  };
  try {
    const res = await fetch(`${API_SET}?action=save_pricing`, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)});
    const json = await res.json();
    if (json.error) showAlert('pricing-alert', json.error, 'error');
    else showAlert('pricing-alert', '✓ Đã lưu bảng giá', 'success');
  } catch(e) { showAlert('pricing-alert','Lỗi kết nối','error'); }
}

// ---- PACKAGES ----
function renderPackages(packages) {
  allPackages = packages || [];
  const el = document.getElementById('pkg-list');
  const empty = document.getElementById('pkg-list-empty');
  if (!allPackages.length) { el.innerHTML=''; empty.classList.remove('hidden'); return; }
  empty.classList.add('hidden');
  el.innerHTML = allPackages.map(p => {
    const badge = p.badge ? `<span style="display:inline-block;background:var(--green-light);color:var(--green-dark);font-size:10px;font-weight:600;padding:1px 6px;border-radius:99px;margin-left:6px">${esc(p.badge)}</span>` : '';
    return `<div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg2);border-radius:var(--radius-lg);margin-bottom:8px;border:0.5px solid var(--border)" id="pkg-item-${p.id}">
      <div style="flex:1;min-width:0">
        <div style="font-size:14px;font-weight:500">${esc(p.name)}${badge}</div>
        <div style="font-size:12px;color:var(--text2);margin-top:2px">${p.sessions} buổi · HSD ${p.expiry_days} ngày · ${parseInt(p.price).toLocaleString('vi-VN')}đ</div>
      </div>
      <div style="display:flex;gap:6px;flex-shrink:0">
        <button class="btn btn-sm btn-outline" style="font-size:11px;padding:3px 8px" onclick="editPkg(${p.id})">✏ Sửa</button>
        <button class="btn btn-sm btn-outline" style="font-size:11px;padding:3px 8px;color:var(--red)" onclick="deletePkg(${p.id},'${esc(p.name)}')">Xóa</button>
      </div>
    </div>`;
  }).join('');
}

function openPkgModal(pkg) {
  document.getElementById('pkg-edit-id').value = pkg ? pkg.id : '';
  document.getElementById('pkg-modal-title').textContent = pkg ? 'Chỉnh sửa gói tập' : 'Thêm gói tập mới';
  document.getElementById('pkg-name').value = pkg ? pkg.name : '';
  document.getElementById('pkg-sessions').value = pkg ? pkg.sessions : '';
  document.getElementById('pkg-price').value = pkg ? pkg.price : '';
  document.getElementById('pkg-expiry').value = pkg ? pkg.expiry_days : 30;
  document.getElementById('pkg-badge').value = pkg ? (pkg.badge || '') : '';
  document.getElementById('pkg-sort').value = pkg ? pkg.sort_order : 0;
  hideAlert('pkg-modal-alert');
  document.getElementById('pkg-modal').classList.remove('hidden');
}

function closePkgModal() { document.getElementById('pkg-modal').classList.add('hidden'); }

function editPkg(id) {
  const pkg = allPackages.find(p => p.id == id);
  if (pkg) openPkgModal(pkg);
}

async function savePkg() {
  const id = document.getElementById('pkg-edit-id').value;
  const data = {
    name: document.getElementById('pkg-name').value.trim(),
    sessions: parseInt(document.getElementById('pkg-sessions').value) || 0,
    price: parseInt(document.getElementById('pkg-price').value) || 0,
    expiry_days: parseInt(document.getElementById('pkg-expiry').value) || 30,
    badge: document.getElementById('pkg-badge').value.trim(),
    sort_order: parseInt(document.getElementById('pkg-sort').value) || 0,
    admin_token: adminToken
  };
  if (id) data.id = parseInt(id);

  const action = id ? 'update_package' : 'create_package';
  const btn = document.getElementById('pkg-save-btn');
  btn.disabled = true; btn.textContent = 'Đang lưu...';
  try {
    const res = await fetch(`${API_SET}?action=${action}`, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)});
    const text = await res.text();
    let json;
    try { json = JSON.parse(text); } catch(pe) { showAlert('pkg-modal-alert', 'Lỗi server: ' + text.substring(0, 200), 'error'); btn.disabled = false; btn.textContent = 'Lưu gói tập'; return; }
    if (json.error) { showAlert('pkg-modal-alert', json.error, 'error'); }
    else { closePkgModal(); showAlert('pricing-alert', '✓ ' + json.message, 'success'); loadPackages(); }
  } catch(e) { showAlert('pkg-modal-alert','Lỗi kết nối','error'); }
  btn.disabled = false; btn.textContent = 'Lưu gói tập';
}

async function deletePkg(id, name) {
  if (!confirm(`Xóa gói "${name}"?\nGói sẽ bị ẩn, các đơn hàng cũ không bị ảnh hưởng.`)) return;
  const el = document.getElementById('pkg-item-'+id);
  if (el) { el.style.opacity='.4'; el.style.pointerEvents='none'; }
  try {
    const res = await fetch(`${API_SET}?action=delete_package`, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id, admin_token:adminToken})});
    const json = await res.json();
    if (json.error) { alert('Lỗi: '+json.error); if(el){el.style.opacity='1';el.style.pointerEvents='';} return; }
    showAlert('pricing-alert', '✓ Đã xóa gói tập', 'success');
    loadPackages();
  } catch(e) { if(el){el.style.opacity='1';el.style.pointerEvents='';} }
}

async function loadPackages() {
  try {
    const res = await fetch(`${API_SET}?action=list_packages&admin_token=${adminToken}`);
    const json = await res.json();
    if (json.success) renderPackages(json.packages);
  } catch(e) {}
}

// ---- REPORT ----
let rptDateInited = false;

function initReportDates() {
  if (rptDateInited) return;
  rptDateInited = true;
  const today = new Date().toISOString().slice(0,10);
  document.getElementById('rpt-date-from').value = today;
  document.getElementById('rpt-date-to').value = today;
}

function setReportDate(preset) {
  const today = new Date();
  const fmt = d => d.toISOString().slice(0,10);
  let from, to = fmt(today);
  if (preset === 'today') { from = to; }
  else if (preset === '7d') { const d = new Date(today); d.setDate(d.getDate()-6); from = fmt(d); }
  else if (preset === 'month') { from = fmt(today).slice(0,8) + '01'; }
  document.getElementById('rpt-date-from').value = from;
  document.getElementById('rpt-date-to').value = to;
  loadReport();
}

async function loadReport() {
  initReportDates();
  const loading = document.getElementById('report-loading');
  const content = document.getElementById('report-content');
  loading.classList.remove('hidden'); content.classList.add('hidden');

  const dateFrom = document.getElementById('rpt-date-from').value;
  const dateTo = document.getElementById('rpt-date-to').value;
  const isToday = dateFrom === dateTo && dateFrom === new Date().toISOString().slice(0,10);
  const dateLabel = isToday ? 'hôm nay' : (dateFrom === dateTo ? formatDateShort(dateFrom) : formatDateShort(dateFrom) + ' → ' + formatDateShort(dateTo));

  try {
    const res = await fetch(`${API_BASE}?action=report&admin_token=${adminToken}&date_from=${dateFrom}&date_to=${dateTo}`);
    const json = await res.json();
    if (json.error) { loading.textContent = 'Lỗi: ' + json.error; return; }

    loading.classList.add('hidden'); content.classList.remove('hidden');

    // Doanh thu
    document.getElementById('rpt-rev-label').textContent = dateLabel;
    document.getElementById('rpt-revenue').textContent = formatMoney(json.revenue.total) + 'đ';
    document.getElementById('rpt-revenue-sub').textContent = json.revenue.paid_count + ' đơn đã duyệt';

    // Doanh thu tháng
    if (json.revenue_month) {
      document.getElementById('rpt-rev-month').textContent = formatMoney(json.revenue_month.total) + 'đ';
      document.getElementById('rpt-rev-month-sub').textContent = json.revenue_month.paid_count + ' đơn — T' + json.revenue_month.month_label;
    }

    // Doanh thu tách combo / vãng lai
    if (json.revenue_combo) {
      document.getElementById('rpt-rev-combo').textContent = formatMoney(json.revenue_combo.total) + 'đ';
      document.getElementById('rpt-rev-combo-sub').textContent = json.revenue_combo.paid_count + ' đơn';
    }
    if (json.revenue_walkin) {
      document.getElementById('rpt-rev-walkin').textContent = formatMoney(json.revenue_walkin.total) + 'đ';
      document.getElementById('rpt-rev-walkin-sub').textContent = json.revenue_walkin.paid_count + ' đơn';
    }

    // Doanh thu theo ngày
    const revDaysEl = document.getElementById('rpt-rev-days');
    const revDaysEmpty = document.getElementById('rpt-rev-days-empty');
    if (json.revenue_days && json.revenue_days.length > 0) {
      revDaysEmpty.classList.add('hidden');
      revDaysEl.innerHTML = json.revenue_days.map(d => `
        <div class="rev-day">
          <span class="rd-date">${formatDateShort(d.day)}</span>
          <span class="rd-amount">${formatMoney(d.revenue)}đ (${d.orders} đơn)</span>
        </div>`).join('');
    } else {
      revDaysEl.innerHTML = ''; revDaysEmpty.classList.remove('hidden');
    }

    // Lượt chơi
    document.getElementById('rpt-ci-label').textContent = dateLabel;
    document.getElementById('rpt-people').textContent = json.checkin_stats.total_people;
    document.getElementById('rpt-checkins').textContent = json.checkin_stats.total_checkins;

    // Lượt chơi tách combo / vãng lai
    if (json.checkin_combo) {
      document.getElementById('rpt-ci-combo-ppl').textContent = json.checkin_combo.people;
      document.getElementById('rpt-ci-combo-ci').textContent = json.checkin_combo.checkins;
    }
    if (json.checkin_walkin) {
      document.getElementById('rpt-ci-walk-ppl').textContent = json.checkin_walkin.people;
      document.getElementById('rpt-ci-walk-ci').textContent = json.checkin_walkin.checkins;
    }
    ciData = json.date_checkins || [];
    ciPage = 1;
    renderCiPage();

    // Hội viên cần chăm sóc
    document.getElementById('rpt-low-count').textContent = '(' + (json.low_session_members?.length || 0) + ')';
    lowData = json.low_session_members || [];
    lowPage = 1;
    renderLowPage();
  } catch(e) {
    loading.textContent = 'Lỗi kết nối server';
  }
}

function renderCiPage() {
  const ciEl = document.getElementById('rpt-checkin-list');
  const ciEmpty = document.getElementById('rpt-checkin-empty');
  const pager = document.getElementById('rpt-checkin-pager');
  if (!ciData.length) { ciEl.innerHTML=''; ciEmpty.classList.remove('hidden'); pager.classList.add('hidden'); return; }
  ciEmpty.classList.add('hidden');
  const total = ciData.length;
  const pages = Math.ceil(total / RPT_PER_PAGE);
  const slice = ciData.slice((ciPage-1)*RPT_PER_PAGE, ciPage*RPT_PER_PAGE);
  ciEl.innerHTML = slice.map(ci => {
    const ppl = parseInt(ci.people_count) || 1;
    const dt = new Date(ci.checked_in_at);
    const time = dt.toLocaleDateString('vi-VN',{day:'2-digit',month:'2-digit'}) + ' ' + dt.toLocaleTimeString('vi-VN',{hour:'2-digit',minute:'2-digit'});
    return `<div class="report-item">
      <div>
        <span class="ri-name">${esc(ci.name||'—')}</span>
        <span class="ri-phone" style="margin-left:8px">${esc(ci.phone)}</span>
      </div>
      <div style="display:flex;align-items:center;gap:12px">
        ${ppl > 1 ? '<span style="font-size:12px;color:var(--blue)">'+ppl+' người</span>' : ''}
        <span class="ri-sessions">${ci.sessions_before} → ${ci.sessions_after}</span>
        <span class="ri-time">${time}</span>
      </div>
    </div>`;
  }).join('');
  if (pages > 1) {
    pager.classList.remove('hidden');
    document.getElementById('rpt-ci-page-info').textContent = `Trang ${ciPage}/${pages} (${total} lượt)`;
    document.getElementById('rpt-ci-prev').disabled = ciPage <= 1;
    document.getElementById('rpt-ci-next').disabled = ciPage >= pages;
  } else {
    pager.classList.add('hidden');
  }
}
function ciPageChange(d) { ciPage += d; renderCiPage(); }

function renderLowPage() {
  const lowEl = document.getElementById('rpt-low-list');
  const lowEmpty = document.getElementById('rpt-low-empty');
  const pager = document.getElementById('rpt-low-pager');
  if (!lowData.length) { lowEl.innerHTML=''; lowEmpty.classList.remove('hidden'); pager.classList.add('hidden'); return; }
  lowEmpty.classList.add('hidden');
  const total = lowData.length;
  const pages = Math.ceil(total / RPT_PER_PAGE);
  const slice = lowData.slice((lowPage-1)*RPT_PER_PAGE, lowPage*RPT_PER_PAGE);
  lowEl.innerHTML = slice.map(m => {
    const s = parseInt(m.sessions);
    const cls = s <= 2 ? 'critical' : 'low';
    const ph = m.phone.replace(/\D/g,'');
    const phFmt = ph.slice(0,4)+' '+ph.slice(4,7)+' '+ph.slice(7);
    const expiry = m.expires_at ? new Date(m.expires_at).toLocaleDateString('vi-VN') : '—';
    return `<div class="report-item">
      <div>
        <span class="ri-name">${esc(m.name)}</span>
        <span class="ri-phone" style="margin-left:8px">${phFmt}</span>
      </div>
      <div style="display:flex;align-items:center;gap:12px">
        <span style="font-size:11px;color:var(--text3)">HH: ${expiry}</span>
        <span class="ri-sessions ${cls}">${s} buổi</span>
        <a href="tel:${ph}" class="btn btn-sm btn-outline" style="font-size:11px;padding:3px 8px">📞 Gọi</a>
      </div>
    </div>`;
  }).join('');
  if (pages > 1) {
    pager.classList.remove('hidden');
    document.getElementById('rpt-low-page-info').textContent = `Trang ${lowPage}/${pages} (${total} hội viên)`;
    document.getElementById('rpt-low-prev').disabled = lowPage <= 1;
    document.getElementById('rpt-low-next').disabled = lowPage >= pages;
  } else {
    pager.classList.add('hidden');
  }
}
function lowPageChange(d) { lowPage += d; renderLowPage(); }

function exportReport() {
  const dateFrom = document.getElementById('rpt-date-from').value;
  const dateTo = document.getElementById('rpt-date-to').value;
  window.open(`${API_BASE}?action=export_report&admin_token=${adminToken}&date_from=${dateFrom}&date_to=${dateTo}`, '_blank');
}

// ---- CHECK-IN ----
let ciCustomer = null, ciPeople = 1, ciQrScanner = null;

function ciSearch() {
  const val = document.getElementById('ci-phone-input').value.trim();
  if (!val) { showAlert('ci-search-alert','Vui lòng nhập tên hoặc số điện thoại','warn'); return; }
  hideAlert('ci-search-alert');
  document.getElementById('ci-matches-list').innerHTML = '';

  // Nếu có chữ cái → tìm theo tên
  const isName = /[a-zA-ZÀ-ỹ]/.test(val);
  if (isName) {
    fetch(`${API_BASE}?action=search_name&q=${encodeURIComponent(val)}`)
      .then(r=>r.json()).then(json => {
        const list = json.matches || [];
        if (list.length === 0) { showAlert('ci-search-alert','Không tìm thấy khách hàng','error'); document.getElementById('ci-customer-panel').classList.add('hidden'); return; }
        if (list.length === 1) { ciSelectMatch(list[0].phone); return; }
        renderCiMatches(list);
        document.getElementById('ci-customer-panel').classList.add('hidden');
      }).catch(()=>showAlert('ci-search-alert','Lỗi kết nối','error'));
    return;
  }

  // Tìm theo số điện thoại
  const raw = val.replace(/\D/g,'');
  if (raw.length < 4) { showAlert('ci-search-alert','Nhập tối thiểu 4 số cuối','warn'); return; }
  fetch(`${API_BASE}?action=get&phone=${raw}`)
    .then(r=>r.json()).then(json => {
      if (json.partial) {
        const list = json.matches || [];
        if (list.length === 0) { showAlert('ci-search-alert','Không tìm thấy khách hàng','error'); document.getElementById('ci-customer-panel').classList.add('hidden'); return; }
        if (list.length === 1) { ciSelectMatch(list[0].phone); return; }
        renderCiMatches(list);
        document.getElementById('ci-customer-panel').classList.add('hidden');
        return;
      }
      if (!json.data) { showAlert('ci-search-alert','Không tìm thấy khách hàng','error'); document.getElementById('ci-customer-panel').classList.add('hidden'); return; }
      ciCustomer = json.data; ciPeople = 1;
      ciRender(json.data, json.checkins||[]);
    }).catch(()=>showAlert('ci-search-alert','Lỗi kết nối','error'));
}

function renderCiMatches(list) {
  const wrap = document.getElementById('ci-matches-list');
  wrap.innerHTML = '<div style="font-size:13px;color:var(--text2);margin-bottom:8px">Tìm thấy ' + list.length + ' khách hàng:</div>' +
    list.map(c => {
      const ph = (c.phone||'').replace(/\D/g,'');
      const phFmt = ph.slice(0,4)+' '+ph.slice(4,7)+' '+ph.slice(7);
      const initials = (c.name||'').trim().split(' ').map(w=>w[0]).filter(Boolean).slice(-2).join('').toUpperCase();
      return `<div onclick="ciSelectMatch('${ph}')" style="display:flex;align-items:center;gap:12px;padding:12px;border:0.5px solid var(--border2);border-radius:var(--radius-lg);margin-bottom:8px;cursor:pointer;background:var(--bg)">
        <div class="customer-avatar" style="width:40px;height:40px;font-size:14px">${initials||'NA'}</div>
        <div style="flex:1;min-width:0"><div style="font-weight:600;font-size:14px">${c.name||'—'}</div><div style="font-size:12px;color:var(--text2)">${phFmt} · Còn ${c.sessions||0} buổi</div></div>
        <div style="font-size:18px;color:var(--text3)">›</div>
      </div>`;
    }).join('');
}

function ciSelectMatch(phone) {
  document.getElementById('ci-matches-list').innerHTML = '';
  document.getElementById('ci-phone-input').value = phone;
  fetch(`${API_BASE}?action=get&phone=${phone}`)
    .then(r=>r.json()).then(json => {
      if (!json.data) { showAlert('ci-search-alert','Không tìm thấy khách hàng','error'); return; }
      ciCustomer = json.data; ciPeople = 1;
      ciRender(json.data, json.checkins||[]);
    }).catch(()=>showAlert('ci-search-alert','Lỗi kết nối','error'));
}

function ciRender(c, checkins) {
  document.getElementById('ci-customer-panel').classList.remove('hidden');
  document.getElementById('ci-avatar').textContent = c.name.trim().split(' ').map(w=>w[0]).filter(Boolean).slice(-2).join('').toUpperCase();
  document.getElementById('ci-name').textContent = c.name;
  const ph = c.phone.replace(/\D/g,'');
  document.getElementById('ci-phone-display').textContent = ph.slice(0,4)+' '+ph.slice(4,7)+' '+ph.slice(7);
  document.getElementById('ci-sessions').textContent = c.sessions;
  ciPeople = 1;
  document.getElementById('ci-people-count').textContent = '1';
  document.getElementById('ci-people-note').textContent = 'Trừ 1 lượt';
  ciUpdateBtn();
  hideAlert('ci-checkin-alert');
  // History
  const el = document.getElementById('ci-history');
  if (!checkins.length) { el.innerHTML='<div style="font-size:13px;color:var(--text3);text-align:center;padding:12px">Chưa có lịch sử</div>'; return; }
  el.innerHTML = checkins.slice(0,8).map(ci => {
    const ppl = parseInt(ci.people_count)||1;
    const time = new Date(ci.checked_in_at).toLocaleTimeString('vi-VN',{hour:'2-digit',minute:'2-digit'});
    const date = new Date(ci.checked_in_at).toLocaleDateString('vi-VN',{day:'2-digit',month:'2-digit'});
    return `<div class="report-item">
      <div><span class="ri-name">${date} ${time}</span></div>
      <div style="display:flex;align-items:center;gap:8px">
        ${ppl>1?'<span style="font-size:11px;color:var(--blue)">'+ppl+' người</span>':''}
        <span class="ri-sessions">${ci.sessions_before} → ${ci.sessions_after}</span>
      </div>
    </div>`;
  }).join('');
}

function ciChangePeople(d) {
  const max = ciCustomer ? parseInt(ciCustomer.sessions) : 20;
  ciPeople = Math.max(1, Math.min(max, ciPeople+d));
  document.getElementById('ci-people-count').textContent = ciPeople;
  document.getElementById('ci-people-note').textContent = `Trừ ${ciPeople} lượt`;
  ciUpdateBtn();
}

function ciUpdateBtn() {
  const btn = document.getElementById('ci-checkin-btn');
  const s = ciCustomer ? parseInt(ciCustomer.sessions) : 0;
  const ok = s >= ciPeople;
  btn.disabled = !ok; btn.style.opacity = ok?'1':'.45';
  btn.textContent = `✓ Check In — trừ ${ciPeople} buổi`;
}

async function ciDoCheckin() {
  if (!ciCustomer || parseInt(ciCustomer.sessions) < ciPeople) return;
  const btn = document.getElementById('ci-checkin-btn');
  btn.disabled = true; btn.textContent = 'Đang xử lý...';
  try {
    const res = await fetch(`${API_BASE}?action=checkin`, {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({phone: ciCustomer.phone, count: ciPeople})
    });
    const json = await res.json();
    if (json.error) { showAlert('ci-checkin-alert', json.error, 'error'); }
    else {
      ciCustomer = json.data;
      document.getElementById('ci-sessions').textContent = json.data.sessions;
      const pplText = (json.people_count||1)>1 ? ` (${json.people_count} người)` : '';
      showAlert('ci-checkin-alert', `✓ Check-in thành công${pplText}! Trừ ${json.sessions_before-json.sessions_after} buổi, còn ${json.sessions_after} buổi.`, 'success');
      ciPeople = 1;
      document.getElementById('ci-people-count').textContent = '1';
      document.getElementById('ci-people-note').textContent = 'Trừ 1 lượt';
      // Refresh history
      try { const r2 = await fetch(`${API_BASE}?action=get&phone=${ciCustomer.phone}`); const j2 = await r2.json(); if(j2.checkins) ciRender(j2.data, j2.checkins); } catch(_){}
    }
  } catch(e) { showAlert('ci-checkin-alert','Lỗi kết nối','error'); }
  ciUpdateBtn();
}

function ciStartQR() {
  document.getElementById('ci-qr-wrap').style.display = 'block';
  document.getElementById('ci-scan-btn').disabled = true;
  ciQrScanner = new Html5Qrcode('ci-qr-reader');
  Html5Qrcode.getCameras().then(cameras => {
    if (!cameras.length) { showAlert('ci-search-alert','Không tìm thấy camera','error'); ciStopQR(); return; }
    const cam = cameras.find(c => /back|rear|environment/i.test(c.label)) || cameras[cameras.length-1];
    ciQrScanner.start(cam.id, {fps:10, qrbox:{width:250,height:250}}, text => {
      ciStopQR();
      let phone = text.startsWith('WP-') ? text.replace('WP-','') : text.replace(/\D/g,'');
      if (phone.length >= 9) { document.getElementById('ci-phone-input').value = phone; ciSearch(); }
      else showAlert('ci-search-alert','Mã QR không hợp lệ','error');
    }, ()=>{}).catch(err => { showAlert('ci-search-alert','Không thể mở camera: '+err,'error'); ciStopQR(); });
  }).catch(()=>{ showAlert('ci-search-alert','Trình duyệt không hỗ trợ camera','error'); ciStopQR(); });
}

function ciStopQR() {
  if (ciQrScanner) { ciQrScanner.stop().catch(()=>{}); ciQrScanner=null; }
  document.getElementById('ci-qr-wrap').style.display = 'none';
  document.getElementById('ci-scan-btn').disabled = false;
}

// ---- BANNERS ----
async function loadBanners() {
  const list = document.getElementById('banner-list');
  const loading = document.getElementById('banner-list-loading');
  const empty = document.getElementById('banner-list-empty');
  loading.classList.remove('hidden'); list.innerHTML=''; empty.classList.add('hidden');
  try {
    const res = await fetch(`${API_BANNERS}?action=list&admin_token=${adminToken}`);
    const json = await res.json();
    loading.classList.add('hidden');
    if (json.error) { list.innerHTML=`<p style="color:var(--red);font-size:13px">${esc(json.error)}</p>`; return; }
    const banners = json.banners || [];
    document.getElementById('banner-count').textContent = `(${banners.length})`;
    if (!banners.length) { empty.classList.remove('hidden'); return; }
    list.innerHTML = banners.map(b => `
      <div class="banner-card" id="banner-${b.id}">
        <img class="banner-thumb" src="${esc(b.image_url)}" alt="banner" onerror="this.style.background='var(--bg2)'">
        <div class="banner-info">
          <div style="font-size:13px;font-weight:500;margin-bottom:2px">Ảnh #${b.id}</div>
          <div class="banner-link">${b.link_url ? `<a href="${esc(b.link_url)}" target="_blank" style="color:var(--green)">${esc(b.link_url)}</a>` : '<span style="color:var(--text3)">Không có link</span>'}</div>
          <div style="font-size:11px;color:var(--text3);margin-top:2px">${formatDateTime(b.created_at)}</div>
        </div>
        <div class="banner-actions">
          <button class="btn btn-sm btn-outline" style="color:var(--red);font-size:12px" onclick="deleteBanner(${b.id})">Xóa</button>
        </div>
      </div>`).join('');
  } catch(e) { loading.classList.add('hidden'); list.innerHTML='<p style="color:var(--red);font-size:13px">Lỗi kết nối</p>'; }
}

function previewBannerFile(input) {
  bannerFile = input.files[0] || null;
  if (!bannerFile) return;
  const url = URL.createObjectURL(bannerFile);
  document.getElementById('banner-preview-img').src = url;
  document.getElementById('banner-preview-wrap').style.display='block';
  document.getElementById('banner-drop-text').style.display='none';
}

function handleBannerDrop(e) {
  e.preventDefault();
  document.getElementById('banner-drop').classList.remove('drag');
  const f = e.dataTransfer.files[0];
  if (f && f.type.startsWith('image/')) {
    bannerFile = f;
    const url = URL.createObjectURL(f);
    document.getElementById('banner-preview-img').src = url;
    document.getElementById('banner-preview-wrap').style.display='block';
    document.getElementById('banner-drop-text').style.display='none';
  }
}

async function uploadBanner() {
  if (!bannerFile) { showAlert('banner-upload-alert','Chưa chọn ảnh','warn'); return; }
  if (bannerFile.size > 5*1024*1024) { showAlert('banner-upload-alert','Ảnh quá 5MB','warn'); return; }
  const link = document.getElementById('banner-link').value.trim();
  const btn = document.getElementById('banner-upload-btn');
  btn.disabled=true; btn.textContent='Đang tải lên...';
  try {
    const fd = new FormData();
    fd.append('image', bannerFile);
    if (link) fd.append('link_url', link);
    fd.append('admin_token', adminToken);
    const res = await fetch(`${API_BANNERS}?action=create`, {method:'POST', body:fd});
    const json = await res.json();
    if (json.error) { showAlert('banner-upload-alert', json.error, 'error'); }
    else {
      showAlert('banner-upload-alert','✓ Đã tải lên thành công','success');
      bannerFile=null;
      document.getElementById('banner-file').value='';
      document.getElementById('banner-preview-wrap').style.display='none';
      document.getElementById('banner-drop-text').style.display='block';
      document.getElementById('banner-link').value='';
      loadBanners();
    }
  } catch(e) { showAlert('banner-upload-alert','Lỗi kết nối','error'); }
  btn.disabled=false; btn.textContent='Tải lên';
}

async function deleteBanner(id) {
  if (!confirm('Xóa banner này?')) return;
  const card = document.getElementById('banner-'+id);
  if (card) { card.style.opacity='.4'; card.style.pointerEvents='none'; }
  try {
    const res = await fetch(`${API_BANNERS}?action=delete`, {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({id, admin_token: adminToken})
    });
    const json = await res.json();
    if (json.error) { alert('Lỗi: '+json.error); if(card){card.style.opacity='1';card.style.pointerEvents='';} return; }
    loadBanners();
  } catch(e) { if(card){card.style.opacity='1';card.style.pointerEvents='';} }
}

function formatMoney(n) { return parseInt(n||0).toLocaleString('vi-VN'); }
function formatDateShort(d) { const dt = new Date(d); return dt.toLocaleDateString('vi-VN', {weekday:'short', day:'2-digit', month:'2-digit'}); }

// ---- UTILS ----
function esc(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}

function formatDateTime(d){if(!d)return'—';const dt=new Date(d);return dt.toLocaleDateString('vi-VN',{day:'2-digit',month:'2-digit'})+' '+dt.toLocaleTimeString('vi-VN',{hour:'2-digit',minute:'2-digit'});}

// ===================== INVENTORY & SALES =====================
const API_INV = 'api/inventory.php';

// ---- Products ----
let invSearchTimer;
function searchProducts() {
  clearTimeout(invSearchTimer);
  invSearchTimer = setTimeout(loadProducts, 300);
}

async function loadProducts() {
  const q = document.getElementById('inv-search')?.value.trim() || '';
  const tbody = document.getElementById('inv-tbody');
  if (!tbody) return;
  try {
    const res = await fetch(`${API_INV}?action=list_products&q=${encodeURIComponent(q)}&admin_token=${adminToken}`);
    const json = await res.json();
    if (!json.data || !json.data.length) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--text3);padding:20px">Chưa có sản phẩm</td></tr>'; return;
    }
    tbody.innerHTML = json.data.map(p => `
      <tr>
        <td style="font-weight:500">${esc(p.name)}</td>
        <td style="color:var(--text2)">${esc(p.unit)}</td>
        <td>${formatMoney(p.cost_price)}đ</td>
        <td>${formatMoney(p.sell_price)}đ</td>
        <td><span style="font-weight:600;color:${p.stock<=3?'var(--red)':'var(--text)'}">${p.stock}</span></td>
        <td>
          <button class="btn btn-ghost" style="font-size:12px;padding:4px 8px" onclick="openStockinModal(${p.id},'${esc(p.name)}',${p.stock})">Nhập kho</button>
          <button class="btn btn-ghost" style="font-size:12px;padding:4px 8px" onclick="openEditProductModal(${p.id},'${esc(p.name)}','${esc(p.unit)}',${p.cost_price},${p.sell_price})">Sửa</button>
          <button class="btn" style="font-size:12px;padding:4px 8px;background:var(--red);color:white;border:none;border-radius:var(--radius);cursor:pointer" onclick="deleteProduct(${p.id},'${esc(p.name)}')">Xóa</button>
        </td>
      </tr>`).join('');
  } catch(e) { tbody.innerHTML = '<tr><td colspan="6" style="color:var(--red);text-align:center">Lỗi tải dữ liệu</td></tr>'; }
}

async function loadInventoryLogs() {
  const tbody = document.getElementById('inv-log-tbody');
  if (!tbody) return;
  try {
    const res = await fetch(`${API_INV}?action=inventory_logs&admin_token=${adminToken}`);
    const json = await res.json();
    if (!json.data?.length) { tbody.innerHTML='<tr><td colspan="4" style="text-align:center;color:var(--text3);padding:16px">Chưa có lịch sử</td></tr>'; return; }
    tbody.innerHTML = json.data.map(l => `
      <tr>
        <td style="font-size:12px;color:var(--text2)">${formatDateTime(l.created_at)}</td>
        <td>${esc(l.product_name)}</td>
        <td><span style="color:${l.type==='in'?'var(--green)':'var(--red)';}">${l.type==='in'?'+':'−'}${l.quantity}</span></td>
        <td style="font-size:12px;color:var(--text2)">${esc(l.note||'')}</td>
      </tr>`).join('');
  } catch(e) {}
}

function openAddProductModal() {
  document.getElementById('add-product-title').textContent = 'Thêm sản phẩm';
  document.getElementById('ap-id').value = '';
  document.getElementById('ap-name').value = '';
  document.getElementById('ap-unit').value = 'cái';
  document.getElementById('ap-stock').value = 0;
  document.getElementById('ap-stock').disabled = false;
  document.getElementById('ap-cost').value = '';
  document.getElementById('ap-sell').value = '';
  hideAlert('ap-alert');
  document.getElementById('add-product-modal').classList.remove('hidden');
}

function openEditProductModal(id, name, unit, cost, sell) {
  document.getElementById('add-product-title').textContent = 'Sửa sản phẩm';
  document.getElementById('ap-id').value = id;
  document.getElementById('ap-name').value = name;
  document.getElementById('ap-unit').value = unit;
  document.getElementById('ap-stock').value = '';
  document.getElementById('ap-stock').disabled = true;
  document.getElementById('ap-cost').value = cost;
  document.getElementById('ap-sell').value = sell;
  hideAlert('ap-alert');
  document.getElementById('add-product-modal').classList.remove('hidden');
}

function closeAddProductModal() { document.getElementById('add-product-modal').classList.add('hidden'); }

async function saveProduct() {
  const id   = document.getElementById('ap-id').value;
  const name = document.getElementById('ap-name').value.trim();
  const unit = document.getElementById('ap-unit').value.trim() || 'cái';
  const cost = parseInt(document.getElementById('ap-cost').value) || 0;
  const sell = parseInt(document.getElementById('ap-sell').value) || 0;
  const stock= parseInt(document.getElementById('ap-stock').value) || 0;
  if (!name) { showAlert('ap-alert','Vui lòng nhập tên sản phẩm','warn'); return; }
  const action = id ? 'update_product' : 'create_product';
  const body = id ? {id:+id,name,unit,cost_price:cost,sell_price:sell,admin_token:adminToken}
                  : {name,unit,cost_price:cost,sell_price:sell,stock,admin_token:adminToken};
  try {
    const res = await fetch(`${API_INV}?action=${action}`, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const json = await res.json();
    if (json.error) { showAlert('ap-alert',json.error,'error'); return; }
    closeAddProductModal();
    loadProducts(); loadInventoryLogs();
  } catch(e) { showAlert('ap-alert','Lỗi kết nối','error'); }
}

async function deleteProduct(id, name) {
  if (prompt(`Nhập "xóa" để xóa sản phẩm "${name}"`) !== 'xóa') return;
  try {
    const res = await fetch(`${API_INV}?action=delete_product`, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,admin_token:adminToken})});
    const json = await res.json();
    if (json.error) { showAlert('inv-alert',json.error,'error'); return; }
    loadProducts();
  } catch(e) { showAlert('inv-alert','Lỗi kết nối','error'); }
}

// ---- Stock In ----
function openStockinModal(id, name, stock) {
  document.getElementById('si-product-id').value = id;
  document.getElementById('si-product-label').innerHTML = `<strong>${esc(name)}</strong> <span style="font-size:12px;color:var(--text2)">· Đang có ${stock}</span>`;
  document.getElementById('si-quantity').value = '';
  document.getElementById('si-note').value = '';
  hideAlert('si-alert');
  document.getElementById('stockin-modal').classList.remove('hidden');
}
function closeStockinModal() { document.getElementById('stockin-modal').classList.add('hidden'); }

async function saveStockin() {
  const product_id = +document.getElementById('si-product-id').value;
  const quantity   = parseInt(document.getElementById('si-quantity').value);
  const note       = document.getElementById('si-note').value.trim();
  if (!quantity || quantity <= 0) { showAlert('si-alert','Vui lòng nhập số lượng','warn'); return; }
  try {
    const res = await fetch(`${API_INV}?action=stock_in`, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({product_id,quantity,note,admin_token:adminToken})});
    const json = await res.json();
    if (json.error) { showAlert('si-alert',json.error,'error'); return; }
    closeStockinModal();
    loadProducts(); loadInventoryLogs();
  } catch(e) { showAlert('si-alert','Lỗi kết nối','error'); }
}

// ---- Sales ----
let cart = [];
let saleSearchTimer;

function saleProdSearch() {
  clearTimeout(saleSearchTimer);
  saleSearchTimer = setTimeout(async () => {
    const q = document.getElementById('sale-search').value.trim();
    const box = document.getElementById('sale-suggestions');
    if (q.length < 1) { box.style.display='none'; return; }
    try {
      const res = await fetch(`${API_INV}?action=list_products&q=${encodeURIComponent(q)}&admin_token=${adminToken}`);
      const json = await res.json();
      const list = json.data || [];
      if (!list.length) { box.style.display='none'; return; }
      box.style.display = 'block';
      box.innerHTML = list.map(p => `
        <div onclick="addToCart(${p.id},'${esc(p.name)}',${p.sell_price},${p.cost_price},'${esc(p.unit)}',${p.stock})"
             style="padding:10px 14px;cursor:pointer;border-bottom:0.5px solid var(--border2);display:flex;justify-content:space-between;align-items:center"
             onmouseover="this.style.background='var(--bg2)'" onmouseout="this.style.background=''">
          <div>
            <div style="font-size:14px;font-weight:500">${esc(p.name)}</div>
            <div style="font-size:12px;color:var(--text2)">Tồn: ${p.stock} ${esc(p.unit)}</div>
          </div>
          <div style="font-size:13px;font-weight:600;color:var(--green)">${formatMoney(p.sell_price)}đ</div>
        </div>`).join('');
    } catch(e) { box.style.display='none'; }
  }, 250);
}

document.addEventListener('click', e => {
  if (!e.target.closest('#sale-search') && !e.target.closest('#sale-suggestions'))
    document.getElementById('sale-suggestions') && (document.getElementById('sale-suggestions').style.display='none');
});

function addToCart(id, name, sell_price, cost_price, unit, stock) {
  document.getElementById('sale-suggestions').style.display='none';
  document.getElementById('sale-search').value='';
  const existing = cart.find(c => c.id === id);
  if (existing) {
    if (existing.qty >= stock) { showAlert('sale-alert',`"${name}" chỉ còn ${stock} ${unit}`,'warn'); return; }
    existing.qty++;
  } else {
    if (stock <= 0) { showAlert('sale-alert',`"${name}" đã hết hàng`,'warn'); return; }
    cart.push({id, name, sell_price, cost_price, unit, stock, qty:1});
  }
  hideAlert('sale-alert');
  renderCart();
}

function updateCartQty(id, delta) {
  const item = cart.find(c => c.id === id);
  if (!item) return;
  item.qty += delta;
  if (item.qty <= 0) cart = cart.filter(c => c.id !== id);
  renderCart();
}

function renderCart() {
  const el = document.getElementById('cart-list');
  const totalEl = document.getElementById('cart-total');
  if (!cart.length) {
    el.innerHTML = '<div style="color:var(--text3);font-size:13px;text-align:center;padding:20px 0">Chưa có sản phẩm</div>';
    totalEl.textContent = '0đ'; return;
  }
  let total = 0;
  el.innerHTML = cart.map(c => {
    total += c.sell_price * c.qty;
    return `<div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:500">${esc(c.name)}</div>
        <div style="font-size:12px;color:var(--text2)">${formatMoney(c.sell_price)}đ / ${c.unit}</div>
      </div>
      <div style="display:flex;align-items:center;gap:4px">
        <button onclick="updateCartQty(${c.id},-1)" style="width:26px;height:26px;border:0.5px solid var(--border2);background:var(--bg2);border-radius:var(--radius);cursor:pointer;font-size:16px;line-height:1">−</button>
        <span style="min-width:24px;text-align:center;font-weight:600">${c.qty}</span>
        <button onclick="updateCartQty(${c.id},1)" style="width:26px;height:26px;border:0.5px solid var(--border2);background:var(--bg2);border-radius:var(--radius);cursor:pointer;font-size:16px;line-height:1">+</button>
      </div>
      <div style="font-size:13px;font-weight:600;width:70px;text-align:right">${formatMoney(c.sell_price*c.qty)}đ</div>
    </div>`;
  }).join('');
  totalEl.textContent = formatMoney(total) + 'đ';
}

async function completeSale() {
  if (!cart.length) { showAlert('sale-alert','Giỏ hàng trống','warn'); return; }
  const note  = document.getElementById('sale-note').value.trim();
  const items = cart.map(c => ({product_id:c.id, quantity:c.qty}));
  try {
    const res = await fetch(`${API_INV}?action=create_sale`, {method:'POST',headers:{'Content-Type':'application/json'},
      body:JSON.stringify({items, note, admin_token:adminToken})});
    const json = await res.json();
    if (json.error) { showAlert('sale-alert',json.error,'error'); return; }
    cart = [];
    document.getElementById('sale-note').value = '';
    renderCart();
    showAlert('sale-alert',`Đã bán thành công! Tổng: ${formatMoney(json.total_amount)}đ`,'success');
    loadSales(); loadProducts();
  } catch(e) { showAlert('sale-alert','Lỗi kết nối','error'); }
}

async function loadSales() {
  const date  = document.getElementById('sale-filter-date')?.value || '';
  const tbody = document.getElementById('sales-tbody');
  if (!tbody) return;
  try {
    const res = await fetch(`${API_INV}?action=list_sales&date=${date}&admin_token=${adminToken}`);
    const json = await res.json();
    if (!json.data?.length) { tbody.innerHTML='<tr><td colspan="4" style="text-align:center;color:var(--text3);padding:16px">Chưa có đơn nào</td></tr>'; return; }
    tbody.innerHTML = json.data.map(s => `
      <tr>
        <td style="font-size:12px">${formatDateTime(s.created_at)}</td>
        <td style="font-size:12px;color:var(--text2);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${esc(s.items_summary||'')}</td>
        <td style="font-weight:600;color:var(--green)">${formatMoney(s.total_amount)}đ</td>
        <td style="white-space:nowrap">
          <button class="btn btn-ghost" style="font-size:11px;padding:3px 7px" onclick="viewSaleDetail(${s.id})">Chi tiết</button>
          <button class="btn" style="font-size:11px;padding:3px 7px;background:var(--red);color:white;border:none;border-radius:var(--radius);cursor:pointer" onclick="deleteSale(${s.id})">Huỷ</button>
        </td>
      </tr>`).join('');
  } catch(e) {}
}

async function viewSaleDetail(id) {
  try {
    const res = await fetch(`${API_INV}?action=sale_detail&id=${id}&admin_token=${adminToken}`);
    const json = await res.json();
    const s = json.sale; const items = json.items||[];
    document.getElementById('sale-detail-content').innerHTML = `
      <div style="font-size:12px;color:var(--text2);margin-bottom:10px">${formatDateTime(s.created_at)}${s.note?` · ${esc(s.note)}`:''}</div>
      <table class="data-table" style="margin-bottom:12px">
        <thead><tr><th>Sản phẩm</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
        <tbody>${items.map(i=>`<tr><td>${esc(i.product_name)}</td><td>${i.quantity}</td><td>${formatMoney(i.sell_price)}đ</td><td>${formatMoney(i.sell_price*i.quantity)}đ</td></tr>`).join('')}</tbody>
      </table>
      <div style="display:flex;justify-content:space-between;font-size:14px;padding:4px 0;border-top:0.5px solid var(--border2)">
        <span>Tổng doanh thu</span><strong style="color:var(--green)">${formatMoney(s.total_amount)}đ</strong>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0;color:var(--text2)">
        <span>Giá vốn</span><span>${formatMoney(s.total_cost)}đ</span>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:14px;padding:4px 0">
        <span>Lợi nhuận</span><strong style="color:var(--green)">${formatMoney(s.total_amount-s.total_cost)}đ</strong>
      </div>`;
    document.getElementById('sale-detail-modal').classList.remove('hidden');
  } catch(e) {}
}

async function deleteSale(id) {
  if (!confirm('Huỷ đơn bán này? Tồn kho sẽ được hoàn trả.')) return;
  try {
    const res = await fetch(`${API_INV}?action=delete_sale`, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,admin_token:adminToken})});
    const json = await res.json();
    if (json.error) { alert('Lỗi: '+json.error); return; }
    loadSales(); loadProducts();
  } catch(e) {}
}

// ---- Sales Report ----
async function loadSalesReport() {
  const from = document.getElementById('sr-from').value;
  const to   = document.getElementById('sr-to').value;
  if (!from||!to) { alert('Chọn khoảng thời gian'); return; }
  try {
    const res = await fetch(`${API_INV}?action=sales_report&from=${from}&to=${to}&admin_token=${adminToken}`);
    const json = await res.json();
    const s = json.summary||{};
    document.getElementById('sr-revenue').textContent = formatMoney(s.revenue||0)+'đ';
    document.getElementById('sr-cost').textContent    = formatMoney(s.cost||0)+'đ';
    document.getElementById('sr-profit').textContent  = formatMoney(s.profit||0)+'đ';
    document.getElementById('sr-orders').textContent  = s.total_orders||0;
    const topTbody = document.getElementById('sr-top-tbody');
    topTbody.innerHTML = (json.top_products||[]).map(p=>`
      <tr><td>${esc(p.product_name)}</td><td>${p.total_qty}</td><td>${formatMoney(p.revenue)}đ</td></tr>`).join('') || '<tr><td colspan="3" style="text-align:center;color:var(--text3)">Không có dữ liệu</td></tr>';
    const dailyTbody = document.getElementById('sr-daily-tbody');
    dailyTbody.innerHTML = (json.daily||[]).map(d=>`
      <tr><td style="font-size:12px">${d.date}</td><td>${formatMoney(d.revenue)}đ</td><td style="color:var(--green)">${formatMoney(d.profit)}đ</td><td>${d.orders}</td></tr>`).join('') || '<tr><td colspan="4" style="text-align:center;color:var(--text3)">Không có dữ liệu</td></tr>';
  } catch(e) { alert('Lỗi tải báo cáo'); }
}

</script>
</body>
</html>
