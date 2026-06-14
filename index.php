<?php
require_once 'auth.php';
require_once 'db/connect.php';
require_login();
$currentUser = current_user();
$company = get_company_settings();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($company['company_name_en']); ?> - POS</title>
<style>
/* ===== RESET & BASE ===== */
* { margin:0; padding:0; box-sizing:border-box; }
html, body { height:100%; font-family: Tahoma, Arial, sans-serif; background:#f5f7fa; color:#2c3e50; font-size:14px; overflow:hidden; }

/* ===== LAYOUT ===== */
#app { display:flex; flex-direction:column; height:100vh; }

/* ===== HEADER ===== */
#header {
    background:linear-gradient(135deg, #8ab4f8, #7aa0e8);
    padding:10px 20px; display:flex; align-items:center; justify-content:space-between;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
    flex-shrink:0;
}
#header h1 { font-size:18px; letter-spacing:1px; }
#header .subtitle { font-size:11px; opacity:0.9; }
#header .nav-links a {
    color:#fff; text-decoration:none; background:rgba(255,255,255,0.2);
    padding:6px 14px; border-radius:4px; margin-left:6px; font-size:13px;
    border:1px solid rgba(255,255,255,0.3); transition:background 0.2s;
}
#header .nav-links a:hover { background:rgba(255,255,255,0.35); }

/* ===== MAIN CONTENT ===== */
#main { display:flex; flex:1; overflow:hidden; }

/* ===== LEFT: MENU PANEL (OLD SIDEBAR LAYOUT) ===== */
#menu-panel { flex:1; display:flex; flex-direction:row; overflow:hidden; border-right:1px solid #e1e4e8; background:#fff; }

/* Category Sidebar — kept from old file */
#cat-tabs {
    display:flex; flex-direction:column; gap:3px; padding:5px;
    background:#e9ecef; border-right:1px solid #dee2e6; flex-shrink:0;
    width:110px; overflow-y:auto; align-items:center;
}
#cat-tabs::-webkit-scrollbar { width:6px; }
#cat-tabs::-webkit-scrollbar-thumb { background:#bdc3c7; border-radius:3px; }
#cat-tabs::-webkit-scrollbar-track { background:#e9ecef; }
.cat-tab {
    width:100% !important; height:60px !important; padding:5px !important; background:#fff; color:#495057;
    border:1px solid #dee2e6 !important; cursor:pointer; border-radius:6px; font-size:11px;
    font-family:Tahoma,Arial,sans-serif; font-weight:bold;
    transition:all 0.15s; border:2px solid transparent;
    display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px;
    touch-action:manipulation; flex-shrink:0; box-sizing:border-box; text-align:center;
}
.cat-tab small { font-size:9px !important; }
.cat-tab:hover { background:#f8f9fa; color:#3498db; border-color:#3498db; }
.cat-tab.active { background:#3498db; color:#fff; border-color:#2980b9; }
.cat-tab-img { width:32px; height:32px; object-fit:cover; border-radius:5px; flex-shrink:0; }
.cat-tab-text { display:block; }
.cat-tab .cat-tab-img { width:32px; height:32px; border-radius:5px; }

/* Item Grid */
#items-grid {
    flex:1; overflow-y:auto; padding:10px;
    display:grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap:7px;
    background:#f5f7fa; align-content:start;
}
.item-btn {
    background:#fff; border:1px solid #dee2e6; color:#495057; padding:8px 10px; border-radius:6px; cursor:pointer; box-shadow:0 1px 3px rgba(0,0,0,0.05); transition:all 0.15s; text-align:center;
}
.item-btn:hover { background:#f8f9fa; border-color:#8ab4f8; }
.item-btn.selected { background:#8ab4f8; color:#fff; border-color:#8ab4f8; }
.item-btn .item-img { width:60px; height:60px; object-fit:contain; margin:0 auto 6px; border-radius:4px; border:1px solid #e8eaed; }
.item-btn .item-name { font-size:13px; font-weight:bold; line-height:1.2; color:#2c3e50; }
.item-btn .item-price { font-size:14px; color:#e67e22; font-weight:bold; }
.item-btn .item-name-ar { font-size:14px; color:#7f8c8d; direction:rtl; }
.item-btn.subcat { border-color:#e67e22; background:#fff9e6; }
.item-btn.subcat.active { background:#8ab4f8; color:#fff; border-color:#8ab4f8; }

/* ===== RIGHT: ORDER PANEL ===== */
#order-panel {
    width:310px; display:flex; flex-direction:column;
    background:#fff; flex-shrink:0; border-left:1px solid #e1e4e8;
}
#order-header {
    background:#f8f9fa; padding:10px 14px;
    font-size:15px; font-weight:bold; color:#e67e22;
    border-bottom:1px solid #dee2e6; flex-shrink:0;
    display:flex; justify-content:space-between; align-items:center;
}
#order-header span { font-size:12px; color:#7f8c8d; }

/* ===== CUSTOMER SEARCH (Pre-order) ===== */
#customer-panel { display:none; padding:8px 10px; background:#fffbe6; border-bottom:1px solid #f0e68c; }
#customer-search-wrap { position:relative; }
#customer-search { width:100%; padding:6px 10px; border:1px solid #f0e68c; border-radius:6px; font-size:13px; }
#customer-dropdown {
    position:absolute; left:0; right:0; top:100%; background:#fff; border:1px solid #dee2e6;
    border-radius:6px; margin-top:2px; max-height:160px; overflow-y:auto; z-index:100;
    box-shadow:0 4px 12px rgba(0,0,0,0.1); display:none;
}
.customer-option { padding:8px 10px; cursor:pointer; border-bottom:1px solid #f0f0f0; font-size:12px; }
.customer-option:hover { background:#e8f4fd; }
.customer-option .c-name { font-weight:bold; color:#2c3e50; }
.customer-option .c-phone { color:#7f8c8d; font-size:11px; }
#customer-selected { display:none; padding:6px 0; font-size:12px; }
#customer-selected .cs-label { color:#7f8c8d; }
#customer-selected .cs-value { font-weight:bold; color:#2c3e50; }
.btn-new-customer {
    padding:4px 10px; background:#e67e22; color:#fff; border:none; border-radius:4px;
    font-size:11px; cursor:pointer; margin-top:4px;
}

/* ===== NEW CUSTOMER MODAL ===== */
#nc-modal-overlay {
    position:fixed; top:0; left:0; right:0; bottom:0;
    background:rgba(0,0,0,0.5); z-index:1000; display:none;
    align-items:center; justify-content:center;
}
#nc-modal-overlay.show { display:flex; }
#nc-modal {
    background:#fff; border-radius:12px; width:380px; max-width:92%;
    box-shadow:0 20px 60px rgba(0,0,0,0.3); overflow:hidden;
    animation:ncSlideIn 0.25s ease-out;
}
@keyframes ncSlideIn {
    from { transform:translateY(-30px); opacity:0; }
    to   { transform:translateY(0); opacity:1; }
}
#nc-modal-header {
    background:linear-gradient(135deg,#e67e22,#d35400); color:#fff;
    padding:14px 18px; font-size:15px; font-weight:bold;
}
#nc-modal-body { padding:16px 18px; }
.nc-field { margin-bottom:12px; }
.nc-field label { display:block; font-size:12px; color:#5a6c7d; font-weight:600; margin-bottom:4px; }
.nc-field input, .nc-field textarea {
    width:100%; padding:8px 10px; border:1px solid #ced4da; border-radius:6px;
    font-size:13px; font-family:Tahoma,Arial,sans-serif; color:#2c3e50; box-sizing:border-box;
}
.nc-field input:focus, .nc-field textarea:focus {
    outline:none; border-color:#e67e22; box-shadow:0 0 0 3px rgba(230,126,34,0.15);
}
.nc-field input::placeholder { color:#adb5bd; }
#nc-modal-footer { padding:0 18px 16px; display:flex; gap:8px; justify-content:flex-end; }
#nc-modal-footer button {
    padding:8px 18px; border:none; border-radius:6px; font-size:13px;
    cursor:pointer; font-family:Tahoma,Arial,sans-serif; font-weight:600;
}
#nc-btn-save { background:#e67e22; color:#fff; }
#nc-btn-save:hover { background:#d35400; }
#nc-btn-cancel { background:#e9ecef; color:#495057; }
#nc-btn-cancel:hover { background:#dee2e6; }
#nc-error { color:#e74c3c; font-size:12px; display:none; padding:0 18px 8px; }

#order-items { flex:1; overflow-y:auto; padding:6px; background:#f5f7fa; }
.order-item {
    display:flex; align-items:center; padding:7px 6px;
    border-bottom:1px solid #e9ecef; gap:6px; background:#fff; border-radius:4px; margin-bottom:4px;
}
.order-item:last-child { border-bottom:none; }
.oi-name { flex:1; font-size:12px; color:#495057; }
.oi-size { font-size:10px; color:#7f8c8d; display:block; }
.oi-price { font-size:12px; color:#e67e22; width:60px; text-align:right; font-weight:bold; }
.oi-qty { display:flex; align-items:center; gap:4px; }
.oi-qty button {
    width:22px; height:22px; border:none; border-radius:4px; cursor:pointer;
    font-size:14px; font-weight:bold; line-height:1; font-family:Tahoma,Arial,sans-serif;
}
.oi-qty .btn-minus { background:#e74c3c; color:#fff; }
.oi-qty .btn-plus  { background:#27ae60; color:#fff; }
.oi-qty .qty-val { font-size:13px; min-width:18px; text-align:center; color:#2c3e50; }
.oi-del { background:#f8f9fa; border:1px solid #e9ecef; color:#e74c3c; cursor:pointer; border-radius:4px; padding:2px 6px; font-size:13px; }
.oi-del:hover { background:#e74c3c; color:#fff; }

/* Empty order */
#empty-order { text-align:center; padding:30px; color:#95a5a6; font-size:13px; }

/* ===== ORDER FOOTER ===== */
#order-footer { background:#f8f9fa; border-top:1px solid #dee2e6; padding:10px 14px; flex-shrink:0; }
.total-row { display:flex; justify-content:space-between; padding:4px 0; color:#7f8c8d; font-size:13px; }
.total-row.grand { font-size:18px; font-weight:bold; color:#e67e22; border-top:2px solid #dee2e6; padding-top:8px; margin-top:4px; }

.cash-row { margin-top:10px; display:flex; gap:8px; align-items:center; }
.cash-row label { font-size:13px; color:#495057; white-space:nowrap; }
.cash-row input {
    flex:1; padding:7px 10px; background:#fff; border:1px solid #ced4da;
    color:#2c3e50; border-radius:6px; font-size:15px; font-family:Tahoma,Arial,sans-serif;
    text-align:right; width:100%;
}
.cash-row input:focus { outline:none; border-color:#3498db; }
.cash-row select {
    flex:1; padding:7px 10px; background:#fff; border:1px solid #ced4da;
    color:#2c3e50; border-radius:6px; font-size:15px; font-family:Tahoma,Arial,sans-serif;
}
.cash-row select:focus { outline:none; border-color:#3498db; }
.change-row { display:flex; justify-content:space-between; padding:6px 0; font-size:14px; color:#27ae60; font-weight:bold; }

#btn-clear {
    width:100%; padding:8px; margin-top:6px;
    background:#fff; color:#e74c3c; border:1px solid #e74c3c;
    border-radius:8px; font-size:13px; font-weight:bold;
    cursor:pointer; font-family:Tahoma,Arial,sans-serif; transition:all 0.2s;
}
#btn-clear:hover { background:#e74c3c; color:#fff; }

/* ===== SIZE MODAL ===== */
#size-modal-overlay {
    display:none; position:fixed; top:0; left:0; right:0; bottom:0;
    background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;
}
#size-modal-overlay.show { display:flex; }
#size-modal {
    background:#fff; border:1px solid #dee2e6; border-radius:12px;
    padding:24px; min-width:280px; text-align:center; box-shadow:0 10px 40px rgba(0,0,0,0.2);
}
#size-modal h3 { color:#e67e22; margin-bottom:6px; font-size:17px; }
#size-modal .item-ar { color:#7f8c8d; font-size:12px; direction:rtl; margin-bottom:16px; }
.size-btns { display:flex; gap:10px; justify-content:center; flex-wrap:wrap; }
.size-btn {
    padding:12px 20px; border:2px solid #3498db; border-radius:8px;
    background:#fff; color:#3498db; cursor:pointer; font-size:14px;
    font-family:Tahoma,Arial,sans-serif; font-weight:bold;
    transition:all 0.15s; min-width:85px;
}
.size-btn:hover { background:#3498db; color:#fff; transform:scale(1.05); }
.size-btn .sz-label { display:block; font-size:12px; color:#7f8c8d; margin-bottom:4px; }
.size-btn .sz-price { display:block; font-size:16px; color:#e67e22; }
#size-modal-cancel {
    margin-top:14px; background:none; border:none; color:#7f8c8d;
    cursor:pointer; font-size:13px; font-family:Tahoma,Arial,sans-serif;
    text-decoration:underline;
}
#size-modal-cancel:hover { color:#3498db; }

/* ===== STATUS BAR ===== */
#status-bar {
    background:#f8f9fa; padding:4px 12px; font-size:11px; color:#7f8c8d;
    border-top:1px solid #dee2e6; flex-shrink:0;
    display:flex; justify-content:space-between;
}

/* ===== SCROLLBAR ===== */
::-webkit-scrollbar { width:6px; height:6px; }
::-webkit-scrollbar-track { background:#f1f1f1; }
::-webkit-scrollbar-thumb { background:#bdc3c7; border-radius:3px; }
::-webkit-scrollbar-thumb:hover { background:#95a5a6; }

/* ===== TOAST ===== */
#toast {
    position:fixed; bottom:20px; left:50%; transform:translateX(-50%) translateY(60px);
    background:#27ae60; color:#fff; padding:10px 24px; border-radius:8px;
    font-size:14px; font-weight:bold; z-index:9999; opacity:0;
    transition:all 0.3s; pointer-events:none; box-shadow:0 4px 12px rgba(0,0,0,0.15);
}
#toast.show { opacity:1; transform:translateX(-50%) translateY(0); }
#toast.error { background:#e74c3c; }
</style>
</head>
<body>
<div id="app">

  <!-- HEADER -->
  <div id="header">
    <div>
      <div class="subtitle">Point of Sale System</div>
      <h1>&#127828; <?php echo htmlspecialchars($company['company_name_en']); ?> &nbsp; | &nbsp; <span style="color:#d1e3fc;"><?php echo htmlspecialchars($company['company_name_ar']); ?></span></h1>
    </div>
    <div class="nav-links">
      <span style="font-size:12px;margin-right:8px;">User: <?php echo htmlspecialchars($currentUser['full_name']); ?></span>
      <a href="dashboard.php">&#128200; Dashboard</a>
      <a href="invoices.php">&#128196; Invoices</a>
      <a href="tables.php">&#127860; Tables</a>
      <a href="pre_orders.php">&#128203; Pre-Orders</a>
      <?php if (is_admin()): ?><a href="settings.php">&#9881; Settings</a><?php endif; ?>
      <?php if (is_admin()): ?><a href="users.php">&#128101; Users</a><?php endif; ?>
      <a href="logout.php" onclick="showConfirm('Logout','Are you sure you want to logout?','Yes, Logout','\uD83D\uDEAA',function(){ window.location.href='logout.php'; }); return false;">Logout</a>
    </div>
  </div>

  <!-- MAIN -->
  <div id="main">

    <!-- MENU PANEL (old vertical sidebar layout) -->
    <div id="menu-panel">
      <div id="cat-tabs">
        <div style="color:#888;font-size:13px;padding:8px 4px;align-self:center;">Loading menu...</div>
      </div>
      <div id="items-grid">
      </div>
    </div>

    <!-- ORDER PANEL -->
    <div id="order-panel">
      <div id="order-header">
        &#128203; Current Order
        <span id="order-count">0 items</span>
      </div>

      <!-- PRE-ORDER TOGGLE -->
      <div id="preorder-toggle-row" style="padding:8px 10px;background:#fffbe6;border-bottom:1px solid #f0e68c;display:flex;align-items:center;gap:8px;flex-shrink:0;">
        <label style="font-size:12px;color:#5a6c7d;font-weight:600;white-space:nowrap;">&#128203; Mode:</label>
        <button id="btn-preorder-mode" onclick="togglePreOrderMode()" style="flex:1;padding:6px 10px;border:1px solid #e67e22;border-radius:7px;font-size:13px;background:#fff;color:#e67e22;cursor:pointer;font-weight:600;">Pre-Order: OFF</button>
      </div>

      <!-- TABLE SELECTOR -->
      <div id="table-row" style="padding:8px 10px;background:#f0f4ff;border-bottom:1px solid #dee2e6;display:flex;align-items:center;gap:8px;flex-shrink:0;">
        <label style="font-size:12px;color:#5a6c7d;font-weight:600;white-space:nowrap;">&#127860; Table:</label>
        <select id="table-select" style="flex:1;padding:6px 10px;border:1px solid #dfe4ea;border-radius:7px;font-size:13px;background:#fff;color:#2c3e50;">
          <option value="">-- No Table (Takeaway) --</option>
        </select>
        <span id="table-status-badge" style="font-size:11px;padding:3px 8px;border-radius:10px;display:none;"></span>
      </div>

      <!-- CUSTOMER SEARCH (Pre-order mode only) -->
      <div id="customer-panel" style="display:none;">
        <div id="customer-search-wrap">
          <input type="text" id="customer-search" placeholder="Search by name or phone..." oninput="searchCustomers()" onkeydown="handleCustomerKey(event)" autocomplete="off">
          <div id="customer-dropdown"></div>
        </div>
        <div id="customer-selected">
          <div><span class="cs-label">Name:</span> <span class="cs-value" id="cs-name"></span></div>
          <div><span class="cs-label">Phone:</span> <span class="cs-value" id="cs-phone"></span></div>
          <button class="btn-new-customer" onclick="clearCustomer()">Change Customer</button>
        </div>
        <button class="btn-new-customer" id="btn-add-customer" onclick="showNewCustomerModal()">+ New Customer</button>
      </div>

      <div id="order-items">
        <div id="empty-order">&#128203;<br>No items added yet.<br>Click menu items to add.</div>
      </div>

      <div id="order-footer">
        <div class="total-row grand">
          <span>TOTAL</span>
          <span id="total-display">0.000 KD</span>
        </div>
        <div class="cash-row">
          <label>Payment:</label>
          <select id="payment-mode">
            <option value="Cash">Cash</option>
            <option value="KNET">KNET</option>
            <option value="Talabat">Talabat</option>
            <option value="Keeta">Keeta</option>
          </select>
        </div>
        <div class="cash-row" id="payment-ref-row" style="display:none;">
          <label>Reference:</label>
          <input type="text" id="payment-ref" placeholder="Enter reference number" style="width:100%;padding:6px;border:1px solid #dee2e6;border-radius:4px;">
        </div>
        <div class="cash-row">
          <label>Cash Paid:</label>
          <input type="number" id="cash-input" placeholder="Optional / for change" step="0.001" min="0">
        </div>
        <div class="change-row">
          <span>Change Due:</span>
          <span id="change-display">0.000 KD</span>
        </div>

        <!-- NORMAL PAYMENT BUTTONS -->
        <div id="payment-buttons" style="display:flex;gap:6px;margin-top:10px;">
          <button onclick="if(orderItems.length>0)saveOrder(false)" style="flex:1;padding:12px 6px;background:linear-gradient(135deg,#2980b9,#1a5276);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:bold;cursor:pointer;font-family:Tahoma,Arial,sans-serif;">F1<br><span style="font-size:10px;font-weight:normal;">Pay</span></button>
          <button onclick="if(orderItems.length>0)saveOrder(true)" style="flex:1;padding:12px 6px;background:linear-gradient(135deg,#27ae60,#1e8449);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:bold;cursor:pointer;font-family:Tahoma,Arial,sans-serif;">F2<br><span style="font-size:10px;font-weight:normal;">Pay &amp; Print</span></button>
          <button onclick="holdOrder()" id="btn-hold" style="flex:1;padding:12px 6px;background:linear-gradient(135deg,#e67e22,#d35400);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:bold;cursor:pointer;font-family:Tahoma,Arial,sans-serif;">F3<br><span style="font-size:10px;font-weight:normal;">Hold</span></button>
        </div>

        <!-- PRE-ORDER BUTTONS -->
        <div id="preorder-buttons" style="display:none;gap:6px;margin-top:10px;">
          <button onclick="if(orderItems.length>0)savePreOrder()" style="flex:1;padding:12px 6px;background:linear-gradient(135deg,#9b59b6,#8e44ad);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:bold;cursor:pointer;font-family:Tahoma,Arial,sans-serif;">F1<br><span style="font-size:10px;font-weight:normal;">Save Pre-Order</span></button>
          <button onclick="clearOrder()" style="flex:1;padding:12px 6px;background:linear-gradient(135deg,#95a5a6,#7f8c8d);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:bold;cursor:pointer;font-family:Tahoma,Arial,sans-serif;"><span style="font-size:10px;font-weight:normal;">Cancel</span></button>
        </div>

        <button id="btn-clear">&#128465; Clear Order</button>
      </div>
    </div>

  </div><!-- /main -->

  <!-- STATUS BAR -->
  <div id="status-bar">
    <span id="status-left">Ready</span>
    <span id="status-right">Burge Al Salhiya POS v1.0 &nbsp;|&nbsp; Tel: 9670 6364</span>
  </div>

</div><!-- /app -->

<!-- SIZE MODAL -->
<div id="size-modal-overlay">
  <div id="size-modal">
    <h3 id="modal-item-name">Select Size</h3>
    <div class="item-ar" id="modal-item-ar"></div>
    <div class="size-btns" id="modal-size-btns"></div>
    <button id="size-modal-cancel">Cancel</button>
  </div>
</div>

<!-- NEW CUSTOMER MODAL -->
<div id="nc-modal-overlay" onclick="handleNcOverlayClick(event)">
  <div id="nc-modal">
    <div id="nc-modal-header">&#128203; Add New Customer</div>
    <div id="nc-modal-body">
      <div class="nc-field">
        <label>Customer Name *</label>
        <input type="text" id="nc-name" placeholder="e.g. Ahmed Mohammed" autocomplete="off">
      </div>
      <div class="nc-field">
        <label>Mobile Number *</label>
        <input type="text" id="nc-phone" placeholder="e.g. 66680241" autocomplete="off">
      </div>
      <div class="nc-field">
        <label>Address</label>
        <textarea id="nc-address" rows="2" placeholder="e.g. Block 3, Street 12, Salhiya"></textarea>
      </div>
    </div>
    <div id="nc-error"></div>
    <div id="nc-modal-footer">
      <button id="nc-btn-cancel" onclick="closeNewCustomerModal()">Cancel</button>
      <button id="nc-btn-save" onclick="saveNewCustomerFromModal()">Save Customer</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div id="toast"></div>
<?php include 'includes/confirm_modal.php'; ?>

<!-- WELCOME MODAL -->
<div id="welcome-overlay" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.75);z-index:99998;align-items:center;justify-content:center;">
  <div style="background:#16213e;border:2px solid #f39c12;border-radius:16px;padding:36px 40px;max-width:400px;width:92%;text-align:center;box-shadow:0 12px 50px rgba(0,0,0,0.85);animation:confirmPop 0.25s ease;">
    <div style="font-size:48px;margin-bottom:12px;">&#128075;</div>
    <div style="color:#f39c12;font-size:22px;font-weight:bold;margin-bottom:8px;">Welcome!</div>
    <div style="color:#fff;font-size:17px;font-weight:bold;margin-bottom:6px;"><?php echo htmlspecialchars($currentUser['full_name']); ?></div>
    <div style="color:#aac4ff;font-size:13px;margin-bottom:24px;">مرحباً بك في نظام نقطة البيع<br><?php echo htmlspecialchars($company['company_name_en']); ?> POS</div>
    <button onclick="document.getElementById('welcome-overlay').style.display='none'" style="padding:11px 40px;background:linear-gradient(135deg,#f39c12,#e67e22);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:bold;cursor:pointer;font-family:Tahoma,Arial,sans-serif;">Let's Go!</button>
  </div>
</div>
<?php if (!empty($_SESSION['just_logged_in'])): unset($_SESSION['just_logged_in']); ?>
<script>
window.addEventListener('load', function() {
    var ov = document.getElementById('welcome-overlay');
    ov.style.display = 'flex';
    setTimeout(function() { ov.style.display = 'none'; }, 5000);
});
</script>
<?php endif; ?>

<script>
// ===== STATE =====
var menuData   = [];
var orderItems = [];
var activeCategory = 0;

// Pre-order state
var selectedCustomer  = null;
var preOrderMode      = false;
var loadedPreorderId  = null;

// ===== UTILITY FUNCTIONS =====
function esc(v) {
    return String(v == null ? '' : v).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}
function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ===== LOAD MENU =====
function loadMenu() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/menu.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    menuData = JSON.parse(xhr.responseText);
                    renderCategories();
                    if (menuData.length > 0) showCategory(menuData[0].id);
                    document.getElementById('status-left').textContent = 'Menu loaded — ' + new Date().toLocaleTimeString();
                } catch(e) {
                    showToast('Failed to parse menu data', true);
                }
            } else {
                showToast('Could not connect to database', true);
            }
        }
    };
    xhr.send();
}

function findCategory(catId, list) {
    list = list || menuData;
    for (var i = 0; i < list.length; i++) {
        if (list[i].id == catId) return list[i];
        var found = findCategory(catId, list[i].children || []);
        if (found) return found;
    }
    return null;
}

// ===== RENDER CATEGORIES (old vertical sidebar) =====
function renderCategories() {
    var html = '';
    var tabsBox = document.getElementById('cat-tabs');
    tabsBox.className = '';
    for (var i = 0; i < menuData.length; i++) {
        var cat = menuData[i];
        html += '<button class="cat-tab" onclick="showCategory(' + cat.id + ')" id="cat-' + cat.id + '">';
        if (cat.image_path) html += '<img class="cat-tab-img" src="' + cat.image_path + '">';
        html += '<span class="cat-tab-text">' + cat.name_en + ' <small style="opacity:0.7;font-size:11px;display:block;direction:rtl">' + cat.name_ar + '</small></span>';
        html += '</button>';
    }
    tabsBox.innerHTML = html;
    setTimeout(function() {
        var tabs = document.querySelectorAll('.cat-tab');
        for (var i = 0; i < tabs.length; i++) {
            tabs[i].style.width = '125px';
            tabs[i].style.height = '42px';
            tabs[i].style.padding = '6px 8px';
            tabs[i].style.boxSizing = 'border-box';
        }
    }, 10);
}

// ===== SHOW CATEGORY ITEMS =====
function showCategory(catId) {
    activeCategory = catId;
    var tabs = document.querySelectorAll('.cat-tab');
    for (var i = 0; i < tabs.length; i++) tabs[i].classList.remove('active');
    var t = document.getElementById('cat-' + catId);
    if (t) t.classList.add('active');

    var cat = findCategory(catId);
    if (!cat) return;

    var html = '';
    var children = cat.children || [];
    for (var sc = 0; sc < children.length; sc++) {
        var child = children[sc];
        html += '<div class="item-btn subcat" onclick="showCategory(' + child.id + ')">';
        if (child.image_path) html += '<img class="cat-tab-img" src="' + child.image_path + '">';
        html += '<div class="item-name">' + child.name_en + '</div>';
        html += '<div class="item-name-ar">' + child.name_ar + '</div>';
        html += '<div class="item-price">Sub Category</div>';
        html += '</div>';
    }

    for (var j = 0; j < cat.items.length; j++) {
        var item = cat.items[j];
        var hasSizes = item.price_small !== null;

        if (hasSizes) {
            html += '<div class="item-btn has-sizes" onclick="openSizeModal(' + JSON.stringify(item).replace(/"/g, '&quot;') + ')">';
            if (item.image_path) {
                html += '<img class="item-img" src="' + item.image_path + '" alt="' + esc(item.name_en) + '">';
            }
            html += '<div class="item-name">' + item.name_en + '</div>';
            html += '<div class="item-name-ar">' + item.name_ar + '</div>';
            html += '<div class="item-price">S: ' + parseFloat(item.price_small).toFixed(3) + '</div>';
            html += '<div style="font-size:10px;color:#bb8fce">S / M / L</div>';
            html += '</div>';
        } else {
            var price = parseFloat(item.price).toFixed(3);
            html += '<div class="item-btn" onclick="addToOrder(' + JSON.stringify(item).replace(/"/g, '&quot;') + ', null)">';
            if (item.image_path) {
                html += '<img class="item-img" src="' + item.image_path + '" alt="' + esc(item.name_en) + '">';
            }
            html += '<div class="item-name">' + item.name_en + '</div>';
            html += '<div class="item-name-ar">' + item.name_ar + '</div>';
            html += '<div class="item-price">' + price + ' KD</div>';
            html += '</div>';
        }
    }
    document.getElementById('items-grid').innerHTML = html || '<div style="color:#555;padding:20px">No items in this category</div>';
}

// ===== SIZE MODAL =====
var pendingItem = null;
function openSizeModal(item) {
    pendingItem = item;
    document.getElementById('modal-item-name').textContent = item.name_en;
    document.getElementById('modal-item-ar').textContent = item.name_ar;

    var sizes = [
        { label: 'Small',  price: item.price_small },
        { label: 'Medium', price: item.price_medium },
        { label: 'Large',  price: item.price_large }
    ];
    var html = '';
    for (var i = 0; i < sizes.length; i++) {
        var s = sizes[i];
        if (s.price === null) continue;
        html += '<button class="size-btn" onclick="addWithSize(\'' + s.label + '\', ' + s.price + ')">';
        html += '<span class="sz-label">' + s.label + '</span>';
        html += '<span class="sz-price">' + parseFloat(s.price).toFixed(3) + ' KD</span>';
        html += '</button>';
    }
    document.getElementById('modal-size-btns').innerHTML = html;
    document.getElementById('size-modal-overlay').className = 'show';
}
function addWithSize(sizeLabel, price) {
    document.getElementById('size-modal-overlay').className = '';
    if (pendingItem) {
        addToOrder(pendingItem, sizeLabel, price);
        pendingItem = null;
    }
}
document.getElementById('size-modal-cancel').onclick = function() {
    document.getElementById('size-modal-overlay').className = '';
    pendingItem = null;
};

// ===== ADD TO ORDER =====
function addToOrder(item, size, overridePrice) {
    var price = overridePrice !== undefined ? parseFloat(overridePrice) : parseFloat(item.price);
    var key   = item.id + (size ? '_' + size : '');

    for (var i = 0; i < orderItems.length; i++) {
        if (orderItems[i].key === key) {
            orderItems[i].qty++;
            renderOrder();
            return;
        }
    }
    orderItems.push({
        key:     key,
        id:      item.id,
        name:    item.name_en + (size ? ' (' + size + ')' : ''),
        name_ar: item.name_ar,
        size:    size,
        price:   price,
        qty:     1
    });
    renderOrder();
}

// ===== RENDER ORDER =====
function renderOrder() {
    var container = document.getElementById('order-items');
    if (orderItems.length === 0) {
        container.innerHTML = '<div id="empty-order">&#128203;<br>No items added yet.<br>Click menu items to add.</div>';
        document.getElementById('total-display').textContent = '0.000 KD';
        document.getElementById('change-display').textContent = '0.000 KD';
        document.getElementById('order-count').textContent = '0 items';
        return;
    }

    var html = '';
    var total    = 0;
    var totalQty = 0;

    for (var i = 0; i < orderItems.length; i++) {
        var oi  = orderItems[i];
        var sub = oi.price * oi.qty;
        total    += sub;
        totalQty += oi.qty;

        html += '<div class="order-item">';
        html += '<div class="oi-name">' + oi.name + '<span class="oi-size">' + oi.name_ar + '</span></div>';
        html += '<div class="oi-qty">';
        html += '<button class="btn-minus" onclick="changeQty(' + i + ', -1)">-</button>';
        html += '<span class="qty-val">' + oi.qty + '</span>';
        html += '<button class="btn-plus" onclick="changeQty(' + i + ', 1)">+</button>';
        html += '</div>';
        html += '<div class="oi-price">' + sub.toFixed(3) + '</div>';
        html += '<button class="oi-del" onclick="removeItem(' + i + ')">&#10005;</button>';
        html += '</div>';
    }

    container.innerHTML = html;
    document.getElementById('total-display').textContent = total.toFixed(3) + ' KD';
    document.getElementById('order-count').textContent = totalQty + (totalQty === 1 ? ' item' : ' items');
    var pmode = document.getElementById('payment-mode').value;
    if (pmode === 'Talabat' || pmode === 'Keeta') {
        document.getElementById('cash-input').value = total.toFixed(3);
    }
    updateChange();
}

// ===== QTY CHANGE =====
function changeQty(index, delta) {
    orderItems[index].qty += delta;
    if (orderItems[index].qty <= 0) orderItems.splice(index, 1);
    renderOrder();
}

// ===== REMOVE ITEM =====
function removeItem(index) {
    orderItems.splice(index, 1);
    renderOrder();
}

// ===== PAYMENT MODE HANDLER =====
document.getElementById('payment-mode').addEventListener('change', function() {
    var mode      = this.value;
    var cashInput = document.getElementById('cash-input');
    var refRow    = document.getElementById('payment-ref-row');
    var refInput  = document.getElementById('payment-ref');

    if (mode === 'KNET' || mode === 'Keeta' || mode === 'Talabat') {
        refRow.style.display = 'flex';
    } else {
        refRow.style.display = 'none';
        refInput.value = '';
    }

    if (mode === 'Talabat' || mode === 'Keeta') {
        cashInput.value    = calcTotal().toFixed(3);
        cashInput.readOnly = true;
        cashInput.style.background = '#f0f0f0';
    } else {
        cashInput.readOnly = false;
        cashInput.style.background = '#fff';
        cashInput.value = '';
    }
    updateChange();
});

// ===== CHANGE CALCULATION =====
document.getElementById('cash-input').addEventListener('input', updateChange);
function updateChange() {
    var total  = calcTotal();
    var cash   = parseFloat(document.getElementById('cash-input').value) || 0;
    var change = cash - total;
    var el     = document.getElementById('change-display');
    if (change < 0) {
        el.textContent = 'Insufficient (' + Math.abs(change).toFixed(3) + ' KD short)';
        el.style.color = '#e74c3c';
    } else {
        el.textContent = change.toFixed(3) + ' KD';
        el.style.color = '#2ecc71';
    }
}
function calcTotal() {
    var t = 0;
    for (var i = 0; i < orderItems.length; i++) t += orderItems[i].price * orderItems[i].qty;
    return t;
}

// ===== CHECKOUT =====
function saveOrder(printReceipt) {
    if (orderItems.length === 0) return;
    var total          = calcTotal();
    var paymentMode    = document.getElementById('payment-mode').value;
    var cashInputValue = document.getElementById('cash-input').value.trim();
    var cashPaid       = cashInputValue === '' ? total : parseFloat(cashInputValue);
    if (isNaN(cashPaid)) cashPaid = total;
    var change = cashPaid - total;

    if (cashPaid < total) { showToast('Cash paid is less than total!', true); return; }

    var tableId    = document.getElementById('table-select').value;
    var tableSel   = document.getElementById('table-select');
    var tableName  = tableId ? tableSel.options[tableSel.selectedIndex].getAttribute('data-name') : null;
    var paymentRef = document.getElementById('payment-ref').value.trim();

    var payload = {
        items:             orderItems,
        total:             total,
        payment_mode:      paymentMode,
        payment_reference: paymentRef,
        cash_paid:         cashPaid,
        change_due:        change,
        table_id:          tableId ? parseInt(tableId) : null,
        table_name:        tableName
    };
    if (loadedPreorderId) payload.preorder_id = loadedPreorderId;

    showToast('Saving order...');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/save_order.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    showToast('Order saved!');
                    if (printReceipt) {
                        window.open('receipt.php?id=' + res.invoice_id + '&autoprint=1', '_blank');
                    }
                    orderItems       = [];
                    loadedPreorderId = null;
                    selectedCustomer = null;
                    document.getElementById('customer-panel').style.display = 'none';
                    document.getElementById('customer-search-wrap').style.display = 'block';
                    document.getElementById('customer-selected').style.display = 'none';
                    var ci = document.getElementById('cash-input');
                    ci.value = ''; ci.readOnly = false; ci.style.background = '#fff';
                    document.getElementById('payment-mode').value = 'Cash';
                    document.getElementById('payment-ref').value = '';
                    document.getElementById('payment-ref-row').style.display = 'none';
                    document.getElementById('table-select').value = '';
                    document.getElementById('table-status-badge').style.display = 'none';
                    renderOrder();
                    loadTables();
                } else {
                    showToast('Error: ' + (res.error || 'Unknown error'), true);
                }
            } catch(e) {
                showToast('Server error. Check XAMPP/MySQL.', true);
            }
        }
    };
    xhr.send(JSON.stringify(payload));
}

// ===== CLEAR ORDER =====
document.getElementById('btn-clear').onclick = function() {
    if (orderItems.length === 0) { renderOrder(); return; }
    showConfirm('Clear Order', 'Are you sure you want to clear the current order?', 'Yes, Clear', '&#128465;', function() {
        orderItems = [];
        var ci = document.getElementById('cash-input');
        ci.value = ''; ci.readOnly = false; ci.style.background = '#fff';
        document.getElementById('payment-mode').value = 'Cash';
        document.getElementById('payment-ref').value = '';
        document.getElementById('payment-ref-row').style.display = 'none';
        renderOrder();
    });
};

// ===== TOAST =====
var toastTimeout;
function showToast(msg, isError) {
    var el = document.getElementById('toast');
    el.textContent = msg;
    el.className   = 'show' + (isError ? ' error' : '');
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(function() { el.className = ''; }, 2800);
}

// ===== PRE-ORDER TOGGLE =====
function togglePreOrderMode() {
    preOrderMode = !preOrderMode;
    var btn            = document.getElementById('btn-preorder-mode');
    var tableRow       = document.getElementById('table-row');
    var customerPanel  = document.getElementById('customer-panel');
    var paymentSection = document.getElementById('payment-mode').parentElement;
    var refRow         = document.getElementById('payment-ref-row');
    var cashRow        = document.getElementById('cash-input').parentElement;
    var changeRow      = document.querySelector('.change-row');
    var payButtons     = document.getElementById('payment-buttons');
    var preButtons     = document.getElementById('preorder-buttons');
    var btnClear       = document.getElementById('btn-clear');

    if (preOrderMode) {
        btn.textContent = 'Pre-Order: ON';
        btn.style.background = '#e67e22';
        btn.style.color = '#fff';
        tableRow.style.display = 'none';
        customerPanel.style.display = 'block';
        paymentSection.style.display = 'none';
        refRow.style.display = 'none';
        cashRow.style.display = 'none';
        changeRow.style.display = 'none';
        payButtons.style.display = 'none';
        preButtons.style.display = 'flex';
        btnClear.style.display = 'none';
        document.getElementById('table-select').value = '';
        document.getElementById('customer-search').focus();
    } else {
        btn.textContent = 'Pre-Order: OFF';
        btn.style.background = '#fff';
        btn.style.color = '#e67e22';
        tableRow.style.display = 'flex';
        customerPanel.style.display = 'none';
        paymentSection.style.display = 'flex';
        refRow.style.display = 'none';
        cashRow.style.display = 'flex';
        changeRow.style.display = 'flex';
        payButtons.style.display = 'flex';
        preButtons.style.display = 'none';
        btnClear.style.display = 'block';
        clearCustomer();
    }
}

// ===== CUSTOMER SEARCH =====
var customerSearchTimeout;
function searchCustomers() {
    clearTimeout(customerSearchTimeout);
    var q        = document.getElementById('customer-search').value.trim();
    var dropdown = document.getElementById('customer-dropdown');
    if (q.length < 2) { dropdown.style.display = 'none'; return; }

    customerSearchTimeout = setTimeout(function() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'api/search_customer.php?q=' + encodeURIComponent(q), true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var list = JSON.parse(xhr.responseText);
                    dropdown.innerHTML = '';
                    if (list.length === 0) {
                        dropdown.innerHTML = '<div class="customer-option"><span style="color:#999;">No customer found</span></div>';
                    } else {
                        for (var i = 0; i < list.length; i++) {
                            var c   = list[i];
                            var div = document.createElement('div');
                            div.className = 'customer-option';
                            div.innerHTML = '<div class="c-name">' + escapeHtml(c.name) + '</div><div class="c-phone">' + escapeHtml(c.phone) + '</div>';
                            div.onclick = (function(customer) { return function() { selectCustomer(customer); }; })(c);
                            dropdown.appendChild(div);
                        }
                    }
                    dropdown.style.display = 'block';
                } catch(e) {}
            }
        };
        xhr.send();
    }, 250);
}

function handleCustomerKey(e) {
    if (e.key === 'Escape') document.getElementById('customer-dropdown').style.display = 'none';
}

function selectCustomer(customer) {
    selectedCustomer = customer;
    document.getElementById('customer-search').value = '';
    document.getElementById('customer-dropdown').style.display = 'none';
    document.getElementById('customer-search-wrap').style.display = 'none';
    document.getElementById('customer-selected').style.display = 'block';
    document.getElementById('btn-add-customer').style.display = 'none';
    document.getElementById('cs-name').textContent  = customer.name;
    document.getElementById('cs-phone').textContent = customer.phone;
}

function clearCustomer() {
    selectedCustomer = null;
    document.getElementById('customer-search-wrap').style.display = 'block';
    document.getElementById('customer-selected').style.display = 'none';
    document.getElementById('btn-add-customer').style.display = 'block';
    document.getElementById('customer-search').focus();
}

// ===== NEW CUSTOMER MODAL =====
function showNewCustomerModal() { openNewCustomerModal(); }

function openNewCustomerModal() {
    document.getElementById('nc-name').value    = '';
    document.getElementById('nc-phone').value   = '';
    document.getElementById('nc-address').value = '';
    document.getElementById('nc-error').style.display = 'none';
    document.getElementById('nc-modal-overlay').classList.add('show');
    setTimeout(function() { document.getElementById('nc-name').focus(); }, 100);
}
function closeNewCustomerModal() {
    document.getElementById('nc-modal-overlay').classList.remove('show');
}
function handleNcOverlayClick(e) {
    if (e.target.id === 'nc-modal-overlay') closeNewCustomerModal();
}
function saveNewCustomerFromModal() {
    var name    = document.getElementById('nc-name').value.trim();
    var phone   = document.getElementById('nc-phone').value.trim();
    var address = document.getElementById('nc-address').value.trim();
    var err     = document.getElementById('nc-error');

    if (!name)  { err.textContent = 'Please enter customer name.';  err.style.display = 'block'; return; }
    if (!phone) { err.textContent = 'Please enter customer phone.'; err.style.display = 'block'; return; }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/save_customer.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    closeNewCustomerModal();
                    selectCustomer({id: res.id, name: res.name, phone: res.phone});
                    showToast('Customer added: ' + res.name);
                } else if (res.id) {
                    closeNewCustomerModal();
                    selectCustomer({id: res.id, name: name, phone: phone});
                    showToast('Customer already exists, selected.', true);
                } else {
                    err.textContent = 'Error: ' + (res.error || 'Unknown');
                    err.style.display = 'block';
                }
            } catch(e) {
                err.textContent = 'Server error. Check XAMPP/MySQL.';
                err.style.display = 'block';
            }
        }
    };
    xhr.send(JSON.stringify({name: name, phone: phone, address: address}));
}

// ===== SAVE PRE-ORDER =====
function savePreOrder() {
    if (orderItems.length === 0) return;
    if (!selectedCustomer) {
        showToast('Please search and select a customer first', true);
        document.getElementById('customer-search').focus();
        return;
    }
    var total   = calcTotal();
    var payload = {
        items:          orderItems,
        total:          total,
        customer_id:    selectedCustomer.id,
        customer_name:  selectedCustomer.name,
        customer_phone: selectedCustomer.phone
    };
    showToast('Saving pre-order...');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/save_preorder.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    showToast('Pre-order saved: ' + res.invoice_number);
                    orderItems       = [];
                    loadedPreorderId = null;
                    clearCustomer();
                    if (preOrderMode) { preOrderMode = false; togglePreOrderMode(); }
                    renderOrder();
                } else {
                    showToast('Error: ' + (res.error || 'Unknown error'), true);
                }
            } catch(e) {
                showToast('Server error. Check XAMPP/MySQL.', true);
            }
        }
    };
    xhr.send(JSON.stringify(payload));
}

function clearOrder() {
    if (orderItems.length === 0) { renderOrder(); return; }
    showConfirm('Clear Order', 'Are you sure you want to clear the current order?', 'Yes, Clear', '&#128465;', function() {
        orderItems = [];
        clearCustomer();
        if (preOrderMode) { preOrderMode = false; togglePreOrderMode(); }
        renderOrder();
    });
}

// ===== KEYBOARD SHORTCUTS =====
document.addEventListener('keydown', function(e) {
    if (e.key === 'F1') {
        e.preventDefault();
        if (orderItems.length > 0) {
            if (preOrderMode) savePreOrder();
            else saveOrder(false);
        }
    } else if (e.key === 'F2') {
        e.preventDefault();
        if (orderItems.length > 0 && !preOrderMode) saveOrder(true);
    } else if (e.key === 'F3') {
        e.preventDefault();
        holdOrder();
    } else if (e.key === 'Escape') {
        if (document.getElementById('nc-modal-overlay').classList.contains('show')) {
            closeNewCustomerModal();
        }
    } else if (e.key === 'Enter' && document.getElementById('nc-modal-overlay').classList.contains('show')) {
        if (document.activeElement.id === 'nc-name' ||
            document.activeElement.id === 'nc-phone' ||
            document.activeElement.id === 'nc-address') {
            saveNewCustomerFromModal();
        }
    }
});

// ===== TABLES =====
var tablesData = [];

function loadTables() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/get_tables.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                tablesData = JSON.parse(xhr.responseText);
                renderTableSelect();
            } catch(e) {}
        }
    };
    xhr.send();
}

function renderTableSelect() {
    var sel     = document.getElementById('table-select');
    var current = sel.value;
    sel.innerHTML = '<option value="">-- No Table (Takeaway) --</option>';
    for (var i = 0; i < tablesData.length; i++) {
        var t     = tablesData[i];
        var label = t.name + (t.status === 'occupied' ? ' \uD83D\uDD34 Occupied' : ' \uD83D\uDFE2');
        var opt   = document.createElement('option');
        opt.value = t.id;
        opt.setAttribute('data-name', t.name);
        opt.setAttribute('data-status', t.status);
        opt.textContent = label;
        sel.appendChild(opt);
    }
    if (current) sel.value = current;
}

document.getElementById('table-select').addEventListener('change', function() {
    var tableId = this.value;
    var badge   = document.getElementById('table-status-badge');
    if (!tableId) { badge.style.display = 'none'; return; }

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/get_open_order.php?table_id=' + tableId, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.invoice && res.items.length > 0) {
                    orderItems = [];
                    for (var i = 0; i < res.items.length; i++) {
                        var it = res.items[i];
                        orderItems.push({
                            key:     it.id + '_loaded',
                            id:      it.id,
                            name:    it.item_name + (it.size ? ' (' + it.size + ')' : ''),
                            name_ar: it.item_name_ar || '',
                            size:    it.size,
                            price:   parseFloat(it.price),
                            qty:     parseInt(it.quantity)
                        });
                    }
                    renderOrder();
                    badge.style.display = 'inline-block';
                    badge.style.background = '#fff3cd';
                    badge.style.color = '#856404';
                    badge.textContent = 'Open order loaded';
                    showToast('Loaded open order for ' + document.getElementById('table-select').options[document.getElementById('table-select').selectedIndex].getAttribute('data-name'));
                } else {
                    orderItems = [];
                    renderOrder();
                    badge.style.display = 'none';
                }
            } catch(e) {}
        }
    };
    xhr.send();
});

function holdOrder() {
    if (orderItems.length === 0) { showToast('No items to hold', true); return; }
    var tableId = document.getElementById('table-select').value;
    if (!tableId) { showToast('Please select a table to hold the order', true); return; }
    var tableName = document.getElementById('table-select').options[document.getElementById('table-select').selectedIndex].getAttribute('data-name');
    var total     = calcTotal();
    var payload   = { table_id: parseInt(tableId), table_name: tableName, items: orderItems, total: total };

    showToast('Holding order for ' + tableName + '...');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/hold_order.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    showToast('Order held for ' + tableName + '!');
                    orderItems = [];
                    document.getElementById('cash-input').value = '';
                    document.getElementById('table-select').value = '';
                    document.getElementById('table-status-badge').style.display = 'none';
                    renderOrder();
                    loadTables();
                } else {
                    showToast('Error: ' + (res.error || 'Unknown error'), true);
                }
            } catch(e) {
                showToast('Server error', true);
            }
        }
    };
    xhr.send(JSON.stringify(payload));
}

// ===== LOAD PRE-ORDER FROM URL =====
var urlParams      = new URLSearchParams(window.location.search);
var loadPreorderId = urlParams.get('load_preorder');
if (loadPreorderId) {
    var xhrPO = new XMLHttpRequest();
    xhrPO.open('GET', 'api/get_preorder.php?id=' + encodeURIComponent(loadPreorderId), true);
    xhrPO.onreadystatechange = function() {
        if (xhrPO.readyState === 4 && xhrPO.status === 200) {
            try {
                var res = JSON.parse(xhrPO.responseText);
                if (res.success) {
                    orderItems = [];
                    for (var i = 0; i < res.items.length; i++) {
                        var it = res.items[i];
                        orderItems.push({
                            name:    it.item_name,
                            name_ar: it.item_name_ar,
                            size:    it.size,
                            price:   parseFloat(it.price),
                            qty:     parseInt(it.quantity)
                        });
                    }
                    loadedPreorderId = parseInt(loadPreorderId);

                    if (res.invoice.customer_id) {
                        selectedCustomer = {
                            id:    res.invoice.customer_id,
                            name:  res.invoice.customer_name,
                            phone: res.invoice.customer_phone
                        };
                        document.getElementById('customer-search-wrap').style.display = 'none';
                        document.getElementById('customer-selected').style.display = 'block';
                        document.getElementById('btn-add-customer').style.display = 'none';
                        document.getElementById('cs-name').textContent  = res.invoice.customer_name;
                        document.getElementById('cs-phone').textContent = res.invoice.customer_phone;
                        document.getElementById('customer-panel').style.display = 'block';
                    }

                    renderOrder();
                    showToast('Pre-order loaded. Complete payment to finish.');
                } else {
                    showToast('Error loading pre-order: ' + (res.error || ''), true);
                }
            } catch(e) {}
        }
    };
    xhrPO.send();
}

// ===== INIT =====
loadMenu();
loadTables();
</script>
</body>
</html>