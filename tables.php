<?php
require_once 'db/connect.php';
require_once 'auth.php';
require_login();
$company = get_company_settings();
$currentUser = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tables - <?php echo htmlspecialchars($company['company_name_en']); ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
html, body { min-height:100%; font-family:Tahoma,Arial,sans-serif; background:#f5f7fa; color:#2c3e50; font-size:14px; }

#header {
    background:linear-gradient(135deg, #8ab4f8, #7aa0e8);
    padding:10px 20px; display:flex; align-items:center; justify-content:space-between;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}
#header h1 { font-size:18px; color:#fff; }
#header a { color:#fff; text-decoration:none; background:rgba(255,255,255,0.2); padding:6px 16px; border-radius:4px; font-size:13px; border:1px solid rgba(255,255,255,0.3); margin-left:6px; }
#header a:hover { background:rgba(255,255,255,0.35); }

#content { padding:24px; max-width:1200px; margin:0 auto; }

.page-title { font-size:22px; font-weight:700; color:#2c3e50; margin-bottom:6px; }
.page-sub { font-size:13px; color:#7f8c8d; margin-bottom:24px; }

/* Tables Grid */
.tables-grid {
    display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));
    gap:16px; margin-bottom:32px;
}
.table-card {
    background:#fff; border:2px solid #e8eaed; border-radius:14px;
    padding:20px 16px; text-align:center; cursor:pointer;
    box-shadow:0 2px 8px rgba(0,0,0,0.06); transition:all 0.2s; position:relative;
}
.table-card:hover { box-shadow:0 6px 20px rgba(0,0,0,0.12); transform:translateY(-2px); }
.table-card.occupied { border-color:#e67e22; background:#fff9f0; }
.table-card.available { border-color:#27ae60; }

.table-icon { font-size:36px; margin-bottom:8px; display:block; }
.table-name { font-size:16px; font-weight:700; color:#2c3e50; margin-bottom:6px; }
.table-badge {
    display:inline-block; padding:3px 12px; border-radius:12px; font-size:11px; font-weight:600;
}
.table-badge.available { background:#d5f5e3; color:#1a8a3a; }
.table-badge.occupied  { background:#fde8cc; color:#d35400; }
.table-total { font-size:18px; font-weight:bold; color:#e67e22; margin-top:8px; }
.table-items-count { font-size:11px; color:#7f8c8d; margin-top:2px; }

/* Order Detail Modal */
#order-modal-overlay {
    display:none; position:fixed; top:0; left:0; right:0; bottom:0;
    background:rgba(0,0,0,0.55); z-index:1000; align-items:center; justify-content:center;
}
#order-modal-overlay.show { display:flex; }
#order-modal {
    background:#fff; border-radius:16px; padding:28px; width:500px; max-width:95vw;
    max-height:85vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3);
}
#order-modal h2 { font-size:20px; color:#e67e22; margin-bottom:4px; }
#order-modal .modal-sub { font-size:12px; color:#7f8c8d; margin-bottom:20px; }

.modal-items { width:100%; border-collapse:collapse; margin-bottom:16px; }
.modal-items th { background:#f8f9fa; padding:10px 12px; text-align:left; font-size:12px; color:#5a6c7d; font-weight:600; border-bottom:2px solid #e8eaed; }
.modal-items td { padding:10px 12px; border-bottom:1px solid #f0f2f5; font-size:13px; }
.modal-items tr:last-child td { border-bottom:none; }
.modal-total { display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-top:2px solid #e8eaed; font-size:18px; font-weight:bold; color:#e67e22; margin-bottom:16px; }

.modal-pay-section { background:#f8f9fa; border-radius:10px; padding:16px; margin-bottom:16px; }
.modal-pay-row { display:flex; gap:10px; align-items:center; margin-bottom:10px; }
.modal-pay-row label { font-size:13px; color:#5a6c7d; font-weight:600; white-space:nowrap; min-width:90px; }
.modal-pay-row select, .modal-pay-row input {
    flex:1; padding:8px 12px; border:1px solid #dfe4ea; border-radius:8px;
    font-size:14px; background:#fff; font-family:Tahoma,Arial,sans-serif;
}
.modal-pay-row select:focus, .modal-pay-row input:focus { outline:none; border-color:#8ab4f8; }

.modal-btns { display:flex; gap:10px; }
.modal-btn {
    flex:1; padding:12px; border:none; border-radius:8px; font-size:14px;
    font-weight:bold; cursor:pointer; font-family:Tahoma,Arial,sans-serif; transition:all 0.2s;
}
.modal-btn-pay { background:linear-gradient(135deg,#27ae60,#1e8449); color:#fff; box-shadow:0 2px 8px rgba(39,174,96,0.3); }
.modal-btn-pay:hover { background:linear-gradient(135deg,#2ecc71,#27ae60); transform:translateY(-1px); }
.modal-btn-print { background:linear-gradient(135deg,#8ab4f8,#7aa0e8); color:#fff; box-shadow:0 2px 8px rgba(138,180,248,0.3); }
.modal-btn-print:hover { background:linear-gradient(135deg,#7aa0e8,#6a90d8); transform:translateY(-1px); }
.modal-btn-cancel { background:#fff; color:#7f8c8d; border:1px solid #dee2e6; }
.modal-btn-cancel:hover { background:#f8f9fa; }
.modal-btn-edit { background:linear-gradient(135deg,#e67e22,#d35400); color:#fff; box-shadow:0 2px 8px rgba(230,126,34,0.3); }
.modal-btn-edit:hover { background:linear-gradient(135deg,#f39c12,#e67e22); transform:translateY(-1px); }

.change-info { font-size:13px; color:#27ae60; font-weight:bold; text-align:right; margin-top:4px; }
.change-info.negative { color:#e74c3c; }

.table-card.sub-table { border-style:dashed; opacity:0.92; }
.table-card.sub-table .table-icon { font-size:26px; }
.table-card.sub-table .table-name { font-size:14px; }
.sub-table-label { font-size:10px; color:#8ab4f8; font-weight:600; margin-bottom:2px; }
.mt-btn-seat { background:linear-gradient(135deg,#8ab4f8,#7aa0e8); color:#fff; font-size:11px; padding:5px 9px; white-space:nowrap; }

#toast {
    position:fixed; bottom:20px; left:50%; transform:translateX(-50%) translateY(60px);
    background:#27ae60; color:#fff; padding:10px 24px; border-radius:8px;
    font-size:14px; font-weight:bold; z-index:9999; opacity:0; transition:all 0.3s;
    pointer-events:none; box-shadow:0 4px 12px rgba(0,0,0,0.15);
}
#toast.show { opacity:1; transform:translateX(-50%) translateY(0); }
#toast.error { background:#e74c3c; }

.refresh-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
.refresh-btn {
    padding:8px 18px; background:linear-gradient(135deg,#8ab4f8,#7aa0e8); color:#fff;
    border:none; border-radius:8px; cursor:pointer; font-size:13px; font-family:Tahoma,Arial,sans-serif;
    transition:all 0.2s;
}
.refresh-btn:hover { background:linear-gradient(135deg,#7aa0e8,#6a90d8); }

.legend { display:flex; gap:16px; font-size:12px; color:#7f8c8d; }
.legend span { display:flex; align-items:center; gap:5px; }
.dot { width:10px; height:10px; border-radius:50%; display:inline-block; }
.dot.green { background:#27ae60; }
.dot.orange { background:#e67e22; }

/* Manage Tables Modal */
#manage-modal-overlay {
    display:none; position:fixed; top:0; left:0; right:0; bottom:0;
    background:rgba(0,0,0,0.55); z-index:1000; align-items:center; justify-content:center;
}
#manage-modal-overlay.show { display:flex; }
#manage-modal {
    background:#fff; border-radius:16px; padding:28px; width:460px; max-width:95vw;
    max-height:85vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.3);
}
#manage-modal h2 { font-size:20px; color:#27ae60; margin-bottom:4px; }
#manage-modal .modal-sub { font-size:12px; color:#7f8c8d; margin-bottom:20px; }
.add-table-row { display:flex; gap:10px; margin-bottom:18px; }
.add-table-row input {
    flex:1; padding:10px 14px; border:1px solid #dfe4ea; border-radius:8px;
    font-size:14px; font-family:Tahoma,Arial,sans-serif;
}
.add-table-row input:focus { outline:none; border-color:#27ae60; }
.mt-add-btn {
    padding:10px 20px; background:linear-gradient(135deg,#27ae60,#1e8449); color:#fff;
    border:none; border-radius:8px; font-size:14px; font-weight:bold; cursor:pointer; white-space:nowrap;
}
.mt-add-btn:hover { background:linear-gradient(135deg,#2ecc71,#27ae60); }
.manage-list { display:flex; flex-direction:column; gap:8px; }
.mt-row {
    display:flex; align-items:center; gap:10px; padding:10px 12px;
    border:1px solid #eef0f3; border-radius:10px; background:#fbfcfe;
}
.mt-row input {
    flex:1; padding:7px 10px; border:1px solid #e8eaed; border-radius:6px; font-size:13px;
    font-family:Tahoma,Arial,sans-serif; background:#fff;
}
.mt-row input:focus { outline:none; border-color:#8ab4f8; }
.mt-status { font-size:10px; padding:2px 8px; border-radius:10px; font-weight:600; white-space:nowrap; }
.mt-status.available { background:#d5f5e3; color:#1a8a3a; }
.mt-status.occupied { background:#fde8cc; color:#d35400; }
.mt-btn { border:none; border-radius:6px; padding:7px 11px; cursor:pointer; font-size:13px; }
.mt-btn-save { background:#8ab4f8; color:#fff; }
.mt-btn-save:hover { background:#7aa0e8; }
.mt-btn-del { background:#fff; color:#e74c3c; border:1px solid #e74c3c; }
.mt-btn-del:hover { background:#fdeaea; }
.mt-empty { color:#95a5a6; text-align:center; padding:14px; font-size:13px; }
</style>
</head>
<body>

<div id="header">
    <div>
        <div style="font-size:11px;color:rgba(255,255,255,0.8);">Restaurant Tables</div>
        <h1>&#127860; <?php echo htmlspecialchars($company['company_name_en']); ?> &nbsp;|&nbsp; <span style="color:#d1e3fc;"><?php echo htmlspecialchars($company['company_name_ar']); ?></span></h1>
    </div>
    <div>
        <span style="font-size:12px;color:rgba(255,255,255,0.9);margin-right:10px;">User: <?php echo htmlspecialchars($currentUser['full_name']); ?></span>
        <a href="index.php">&#128203; POS</a>
        <a href="dashboard.php">&#128200; Dashboard</a>
        <a href="invoices.php">&#128196; Invoices</a>
        <?php if (is_admin()): ?><a href="settings.php">&#9881; Settings</a><?php endif; ?>
    </div>
</div>

<div id="content">
    <div class="refresh-bar">
        <div>
            <div class="page-title">&#127860; Tables Overview</div>
            <div class="page-sub">Click an occupied table to view its order and complete payment</div>
        </div>
        <div style="display:flex;align-items:center;gap:16px;">
            <div class="legend">
                <span><span class="dot green"></span> Available</span>
                <span><span class="dot orange"></span> Occupied</span>
            </div>
            <?php if (is_admin()): ?>
            <button class="refresh-btn" style="background:linear-gradient(135deg,#27ae60,#1e8449);" onclick="openManageModal()">&#9881; Manage Tables</button>
            <?php endif; ?>
            <button class="refresh-btn" onclick="loadTables()">&#8635; Refresh</button>
        </div>
    </div>

    <div class="tables-grid" id="tables-grid">
        <div style="color:#888;padding:20px;">Loading tables...</div>
    </div>
</div>

<?php if (is_admin()): ?>
<!-- MANAGE TABLES MODAL -->
<div id="manage-modal-overlay">
    <div id="manage-modal">
        <h2>&#9881; Manage Tables</h2>
        <div class="modal-sub">Add, rename, or delete dine-in tables</div>

        <div class="add-table-row">
            <input type="text" id="new-table-name" placeholder="New table name (e.g. Table 6, VIP 1)" onkeypress="if(event.key==='Enter')addTable()">
            <button class="mt-add-btn" onclick="addTable()">&#43; Add</button>
        </div>

        <div id="manage-tables-list" class="manage-list"></div>

        <div style="text-align:right;margin-top:18px;">
            <button class="modal-btn modal-btn-cancel" style="flex:none;padding:10px 24px;" onclick="closeManageModal()">Close</button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ORDER DETAIL MODAL -->
<div id="order-modal-overlay">
    <div id="order-modal">
        <h2 id="modal-table-name">Table</h2>
        <div class="modal-sub" id="modal-invoice-num"></div>

        <table class="modal-items">
            <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
            <tbody id="modal-items-body"></tbody>
        </table>

        <div class="modal-total">
            <span>Total</span>
            <span id="modal-total">0.000 KD</span>
        </div>

        <div class="modal-pay-section">
            <div class="modal-pay-row">
                <label>Payment:</label>
                <select id="modal-payment" onchange="togglePaymentRef()">
                    <option value="Cash">Cash</option>
                    <option value="KNET">KNET</option>
                    <option value="Talabat">Talabat</option>
                    <option value="Keeta">Keeta</option>
                </select>
            </div>
            <div class="modal-pay-row" id="modal-ref-row" style="display:none;">
                <label>Reference:</label>
                <input type="text" id="modal-ref" placeholder="Enter reference number">
            </div>
            <div class="modal-pay-row">
                <label>Cash Paid:</label>
                <input type="number" id="modal-cash" step="0.001" min="0" placeholder="Leave empty = exact amount" oninput="updateModalChange()">
            </div>
            <div class="change-info" id="modal-change"></div>
        </div>

        <div class="modal-btns">
            <button class="modal-btn modal-btn-pay" onclick="completePayment(false)">&#10003; Pay</button>
            <button class="modal-btn modal-btn-print" onclick="completePayment(true)">&#128424; Pay &amp; Print</button>
            <button class="modal-btn modal-btn-edit" onclick="editTableOrder()">&#9998; Edit in POS</button>
            <button class="modal-btn modal-btn-cancel" onclick="closeModal()">Close</button>
        </div>
        <div style="margin-top:10px;text-align:center;">
            <button class="modal-btn" style="background:#fff;color:#e74c3c;border:1px solid #e74c3c;font-size:13px;padding:8px 20px;" onclick="cancelTableOrder()">&#128465; Cancel Order &amp; Clear Table</button>
        </div>
    </div>
</div>

<div id="toast"></div>
<?php include 'includes/confirm_modal.php'; ?>

<script>
var currentInvoiceId = null;
var currentTableId = null;
var currentTableName = null;
var currentTotal = 0;

function loadTables() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/get_tables.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var tables = JSON.parse(xhr.responseText);
                renderTables(tables);
            } catch(e) {
                document.getElementById('tables-grid').innerHTML = '<div style="color:#e74c3c;">Failed to load tables</div>';
            }
        }
    };
    xhr.send();
}

var tablesCache = [];

function isSubTable(name) {
    return /^.+ [A-Z]$/.test(name.trim());
}

function getParentName(name) {
    return name.trim().replace(/ [A-Z]$/, '');
}

function renderTables(tables) {
    tablesCache = tables;
    var grid = document.getElementById('tables-grid');
    if (!tables.length) {
        grid.innerHTML = '<div style="color:#888;padding:20px;">No tables found. Run the SQL migration first.</div>';
        return;
    }
    var html = '';
    for (var i = 0; i < tables.length; i++) {
        var t = tables[i];
        var isOccupied = t.status === 'occupied';
        var isSub = isSubTable(t.name);
        var icon = isOccupied ? '🔴' : '🟢';
        var emoji = t.name.toLowerCase().includes('takeaway') ? '🥡' : t.name.toLowerCase().includes('delivery') ? '🛵' : (isSub ? '🪑' : '🍽️');
        html += '<div class="table-card ' + t.status + (isSub ? ' sub-table' : '') + '" onclick="' + (isOccupied ? 'openTableOrder(' + t.id + ', \'' + t.name.replace(/'/g, '') + '\')' : 'showToast(\'Table is available\')') + '">';
        if (isSub) {
            html += '<div class="sub-table-label">↳ ' + getParentName(t.name) + ' Seat</div>';
        }
        html += '<span class="table-icon">' + emoji + '</span>';
        html += '<div class="table-name">' + t.name + '</div>';
        html += '<div><span class="table-badge ' + t.status + '">' + icon + ' ' + (isOccupied ? 'Occupied' : 'Available') + '</span></div>';
        if (isOccupied) {
            html += '<div class="table-items-count">Click to view order</div>';
        }
        html += '</div>';
    }
    grid.innerHTML = html;
}

function openTableOrder(tableId, tableName) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/get_open_order.php?table_id=' + tableId, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (!res.invoice) { showToast('No open order for this table', true); return; }
                showOrderModal(res, tableId, tableName);
            } catch(e) {
                showToast('Error loading order', true);
            }
        }
    };
    xhr.send();
}

function showOrderModal(res, tableId, tableName) {
    currentInvoiceId = res.invoice.id;
    currentTableId = tableId;
    currentTableName = tableName;

    document.getElementById('modal-table-name').textContent = '🍽️ ' + tableName;
    document.getElementById('modal-invoice-num').textContent = 'Invoice: ' + res.invoice.invoice_number + ' | Staff: ' + res.invoice.user_name;

    var total = 0;
    var rows = '';
    for (var i = 0; i < res.items.length; i++) {
        var it = res.items[i];
        var sub = parseFloat(it.price) * parseInt(it.quantity);
        total += sub;
        rows += '<tr>';
        rows += '<td>' + it.item_name + (it.size ? ' <small style="color:#7f8c8d">(' + it.size + ')</small>' : '') + '<br><small style="color:#aaa;direction:rtl;">' + (it.item_name_ar || '') + '</small></td>';
        rows += '<td style="text-align:center">' + it.quantity + '</td>';
        rows += '<td style="text-align:right;color:#e67e22">' + parseFloat(it.price).toFixed(3) + '</td>';
        rows += '<td style="text-align:right;font-weight:bold">' + sub.toFixed(3) + '</td>';
        rows += '</tr>';
    }
    document.getElementById('modal-items-body').innerHTML = rows;
    currentTotal = total;
    document.getElementById('modal-total').textContent = total.toFixed(3) + ' KD';
    document.getElementById('modal-cash').value = '';
    document.getElementById('modal-change').textContent = '';
    document.getElementById('order-modal-overlay').className = 'show';
}

function updateModalChange() {
    var cash = parseFloat(document.getElementById('modal-cash').value) || 0;
    var change = cash - currentTotal;
    var el = document.getElementById('modal-change');
    if (!cash) { el.textContent = ''; return; }
    if (change < 0) {
        el.className = 'change-info negative';
        el.textContent = 'Short by ' + Math.abs(change).toFixed(3) + ' KD';
    } else {
        el.className = 'change-info';
        el.textContent = 'Change: ' + change.toFixed(3) + ' KD';
    }
}

function togglePaymentRef() {
    var mode = document.getElementById('modal-payment').value;
    var refRow = document.getElementById('modal-ref-row');
    var refInput = document.getElementById('modal-ref');
    
    if (mode === 'KNET' || mode === 'Keeta' || mode === 'Talabat') {
        refRow.style.display = 'flex';
    } else {
        refRow.style.display = 'none';
        refInput.value = '';
    }
}

function completePayment(printReceipt) {
    var paymentMode = document.getElementById('modal-payment').value;
    var paymentRef = document.getElementById('modal-ref').value.trim();
    var cashVal = document.getElementById('modal-cash').value.trim();
    var cashPaid = cashVal === '' ? currentTotal : parseFloat(cashVal);
    if (isNaN(cashPaid)) cashPaid = currentTotal;
    var changeDue = cashPaid - currentTotal;

    if (cashPaid < currentTotal) {
        showToast('Cash paid is less than total!', true);
        return;
    }

    var payload = {
        invoice_id: currentInvoiceId,
        payment_mode: paymentMode,
        payment_reference: paymentRef,
        total: currentTotal,
        cash_paid: cashPaid,
        change_due: changeDue
    };

    showToast('Processing payment...');
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/complete_order.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    showToast('Payment complete for ' + currentTableName + '!');
                    closeModal();
                    document.getElementById('modal-ref').value = '';
                    document.getElementById('modal-ref-row').style.display = 'none';
                    if (printReceipt) {
                        window.open('receipt.php?id=' + res.invoice_id + '&autoprint=1', '_blank');
                    }
                    loadTables();
                } else {
                    showToast('Error: ' + (res.error || 'Unknown'), true);
                }
            } catch(e) {
                showToast('Server error', true);
            }
        }
    };
    xhr.send(JSON.stringify(payload));
}

function editTableOrder() {
    window.location.href = 'index.php?table_id=' + currentTableId;
}

function cancelTableOrder() {
    showConfirm('Cancel Order', 'Are you sure you want to cancel the order for ' + currentTableName + '? This cannot be undone.', 'Yes, Cancel Order', '&#128465;', function() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'api/cancel_order.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        showToast('Order cancelled. ' + currentTableName + ' is now free.');
                        closeModal();
                        loadTables();
                    } else {
                        showToast('Error: ' + (res.error || 'Unknown'), true);
                    }
                } catch(e) {
                    showToast('Server error', true);
                }
            }
        };
        xhr.send(JSON.stringify({ invoice_id: currentInvoiceId }));
    });
}

function closeModal() {
    document.getElementById('order-modal-overlay').className = '';
    currentInvoiceId = null;
}

// Close modal on overlay click
document.getElementById('order-modal-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Toast
var toastTimeout;
function showToast(msg, isError) {
    var el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'show' + (isError ? ' error' : '');
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(function() { el.className = ''; }, 2800);
}

// ===== MANAGE TABLES MODAL =====
function openManageModal() {
    document.getElementById('manage-modal-overlay').className = 'show';
    renderManageList();
}

function closeManageModal() {
    document.getElementById('manage-modal-overlay').className = '';
    document.getElementById('new-table-name').value = '';
}

function renderManageList() {
    var list = document.getElementById('manage-tables-list');
    if (!tablesCache.length) {
        list.innerHTML = '<div class="mt-empty">No tables yet. Add one above.</div>';
        return;
    }
    var html = '';
    for (var i = 0; i < tablesCache.length; i++) {
        var t = tablesCache[i];
        var isSub = isSubTable(t.name);
        html += '<div class="mt-row">';
        html += '<input type="text" value="' + esc(t.name) + '" id="mt-name-' + t.id + '">';
        html += '<span class="mt-status ' + t.status + '">' + t.status + '</span>';
        html += '<button class="mt-btn mt-btn-save" onclick="renameTable(' + t.id + ')">&#10003;</button>';
        if (!isSub) {
            html += '<button class="mt-btn mt-btn-seat" title="Add a seat/sub-table" onclick="addSubTable(\'' + esc(t.name) + '\')">&#43; Seat</button>';
        }
        html += '<button class="mt-btn mt-btn-del" onclick="deleteTable(' + t.id + ', \'' + esc(t.name) + '\')">&#128465;</button>';
        html += '</div>';
    }
    list.innerHTML = html;
}

function addSubTable(parentName) {
    var letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var existingNames = tablesCache.map(function(t) { return t.name; });
    var nextName = null;
    for (var i = 0; i < letters.length; i++) {
        var candidate = parentName + ' ' + letters[i];
        if (existingNames.indexOf(candidate) === -1) {
            nextName = candidate;
            break;
        }
    }
    if (!nextName) { showToast('Maximum seats reached for ' + parentName, true); return; }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/manage_tables.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    showToast('Seat "' + nextName + '" added');
                    loadTables();
                } else {
                    showToast(res.error || 'Failed to add seat', true);
                }
            } catch(e) { showToast('Server error', true); }
        }
    };
    xhr.send(JSON.stringify({action: 'add_table', name: nextName}));
}

function addTable() {
    var name = document.getElementById('new-table-name').value.trim();
    if (!name) { showToast('Enter a table name', true); return; }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/manage_tables.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    showToast('Table added');
                    document.getElementById('new-table-name').value = '';
                    loadTables();
                } else {
                    showToast(res.error || 'Failed to add table', true);
                }
            } catch(e) {
                showToast('Server error', true);
            }
        }
    };
    xhr.send(JSON.stringify({action: 'add_table', name: name}));
}

function renameTable(id) {
    var name = document.getElementById('mt-name-' + id).value.trim();
    if (!name) { showToast('Table name cannot be empty', true); return; }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/manage_tables.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    showToast('Table renamed');
                    loadTables();
                } else {
                    showToast(res.error || 'Failed to rename', true);
                }
            } catch(e) {
                showToast('Server error', true);
            }
        }
    };
    xhr.send(JSON.stringify({action: 'rename_table', id: id, name: name}));
}

function deleteTable(id, name) {
    showConfirm('Delete Table', 'Are you sure you want to delete "' + name + '"? This cannot be undone.', 'Yes, Delete', '&#128465;', function() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'api/manage_tables.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        showToast('Table deleted');
                        loadTables();
                    } else {
                        showToast(res.error || 'Failed to delete', true);
                    }
                } catch(e) {
                    showToast('Server error', true);
                }
            }
        };
        xhr.send(JSON.stringify({action: 'delete_table', id: id}));
    });
}

function esc(v) {
    return String(v).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

loadTables();

// Auto refresh every 30 seconds
setInterval(loadTables, 30000);
</script>
</body>
</html>
