<?php
require_once 'auth.php';
require_once 'db/connect.php';
require_login();
$company = get_company_settings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Invoices - <?php echo htmlspecialchars($company['company_name_en']); ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
html, body { height:100%; font-family:Tahoma,Arial,sans-serif; background:#f5f7fa; color:#2c3e50; font-size:14px; }

#header {
    background:linear-gradient(135deg, #8ab4f8, #7aa0e8);
    padding:10px 20px; display:flex; align-items:center; justify-content:space-between;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}
#header h1 { font-size:18px; color:#fff; }
#header a { color:#fff; text-decoration:none; background:rgba(255,255,255,0.2); padding:6px 16px; border-radius:4px; font-size:13px; border:1px solid rgba(255,255,255,0.3); }
#header a:hover { background:rgba(255,255,255,0.35); }

#content { padding:20px; max-width:900px; margin:0 auto; }

.summary-bar {
    display:flex; gap:16px; margin-bottom:20px; flex-wrap:wrap;
}
.summary-card {
    background:#fff; border:1px solid #dee2e6; border-radius:8px;
    padding:12px 20px; text-align:center; flex:1; min-width:120px; box-shadow:0 1px 3px rgba(0,0,0,0.05);
}
.summary-card .val { font-size:22px; font-weight:bold; color:#e67e22; }
.summary-card .lbl { font-size:11px; color:#7f8c8d; margin-top:2px; }

.search-bar { margin-bottom:14px; display:flex; gap:10px; flex-wrap:wrap; }
.search-bar input {
    flex:1; padding:8px 12px; background:#fff; border:1px solid #ced4da;
    color:#2c3e50; border-radius:6px; font-size:14px; font-family:Tahoma,Arial,sans-serif;
}
.search-bar input[type="date"] { flex:0 0 155px; }
.search-bar input:focus { outline:none; border-color:#3498db; }
.filter-btn {
    padding:8px 14px; border:none; border-radius:6px; cursor:pointer;
    background:#27ae60; color:#fff; font-weight:bold; font-family:Tahoma,Arial,sans-serif;
}
.filter-btn.clear { background:#95a5a6; }

table { width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05); }
thead { background:#e9ecef; }
thead th { padding:10px 12px; text-align:left; color:#495057; font-size:12px; font-weight:bold; border-bottom:2px solid #dee2e6; }
tbody tr { border-bottom:1px solid #e9ecef; transition:background 0.15s; }
tbody tr:hover { background:#f8f9fa; }
tbody td { padding:9px 12px; font-size:13px; }
tbody td.amount { color:#e67e22; font-weight:bold; }
tbody td.change-td { color:#27ae60; }

.btn-view {
    background:#8ab4f8; color:#fff; border:none;
    padding:4px 10px; border-radius:4px; cursor:pointer; font-size:12px;
    font-family:Tahoma,Arial,sans-serif; text-decoration:none; display:inline-block;
}
.btn-view:hover { background:#7aa0e8; }
.btn-delete {
    background:#e74c3c; color:#fff; border:none;
    padding:4px 10px; border-radius:4px; cursor:pointer; font-size:12px;
    font-family:Tahoma,Arial,sans-serif; display:inline-block;
}
.btn-delete:hover { background:#c0392b; }
.filter-summary { background:#fff; border:1px solid #dee2e6; border-radius:8px; padding:10px 16px; margin-bottom:14px; font-size:13px; color:#495057; display:flex; gap:20px; flex-wrap:wrap; }
.filter-summary span { color:#e67e22; font-weight:bold; }

.pagination { display:flex; gap:6px; justify-content:center; margin-top:16px; }
.page-btn {
    padding:6px 14px; background:#fff; border:1px solid #ced4da; color:#495057;
    border-radius:4px; cursor:pointer; font-size:13px; font-family:Tahoma,Arial,sans-serif;
}
.page-btn:hover, .page-btn.active { background:#3498db; color:#fff; border-color:#3498db; }
.page-btn:disabled { opacity:0.4; cursor:not-allowed; }

#detail-overlay {
    display:none; position:fixed; top:0; left:0; right:0; bottom:0;
    background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;
}
#detail-overlay.show { display:flex; }
#detail-box {
    background:#fff; border:1px solid #dee2e6; border-radius:10px;
    padding:24px; max-width:500px; width:95%; max-height:80vh; overflow-y:auto; box-shadow:0 10px 40px rgba(0,0,0,0.2);
}
#detail-box h3 { color:#e67e22; margin-bottom:12px; }
.detail-table { width:100%; font-size:13px; border-collapse:collapse; }
.detail-table td { padding:5px 8px; border-bottom:1px solid #e9ecef; }
.detail-table .d-name { color:#495057; }
.detail-table .d-price { text-align:right; color:#e67e22; }
.detail-total { margin-top:12px; font-size:15px; font-weight:bold; color:#e67e22; text-align:right; }
.detail-btns { display:flex; gap:8px; margin-top:14px; }
.detail-btns button, .detail-btns a {
    flex:1; padding:8px; border:none; border-radius:6px; cursor:pointer;
    font-size:13px; font-weight:bold; font-family:Tahoma,Arial,sans-serif;
    text-align:center; text-decoration:none; display:block;
}
.btn-reprint { background:#8ab4f8; color:#fff; }
.btn-close   { background:#95a5a6; color:#fff; }

.loading { text-align:center; padding:40px; color:#7f8c8d; }
::-webkit-scrollbar { width:6px; } ::-webkit-scrollbar-track { background:#f1f1f1; } ::-webkit-scrollbar-thumb { background:#bdc3c7; border-radius:3px; }

/* ===== EXPORT BUTTONS ===== */
.export-bar { display:flex; gap:8px; margin-bottom:14px; }
.export-btn {
    padding:8px 14px; border:none; border-radius:6px; cursor:pointer;
    font-size:13px; font-weight:bold; font-family:Tahoma,Arial,sans-serif;
}
.export-print { background:#8ab4f8; color:#fff; }
.export-print:hover { background:#7aa0e8; }
.export-excel { background:#27ae60; color:#fff; }
.export-excel:hover { background:#2ecc71; }
.export-pdf { background:#e74c3c; color:#fff; }
.export-pdf:hover { background:#c0392b; }

/* ===== PRINT STYLES ===== */
@media print {
    #header, .search-bar, .pagination, .export-bar, .btn-view, .btn-delete, #detail-overlay { display:none !important; }
    body { background:#fff; }
    #content { padding:10px; max-width:100%; }
    table { box-shadow:none; }
    .summary-card { border:1px solid #000; background:#fff; }
    .filter-summary { border:1px solid #000; display:block !important; }
    @page { margin:10mm; }
}
</style>
</head>
<body>

<div id="header">
  <h1>&#128196; Invoice History - <?php echo htmlspecialchars($company['company_name_en']); ?></h1>
  <div>
    <a href="dashboard.php">&#128200; Dashboard</a>
    <a href="tables.php">&#127860; Tables</a>
    <a href="index.php">&#8592; Back to POS</a>
    <a href="logout.php" onclick="showConfirm('Logout','Are you sure you want to logout?','Yes, Logout','\uD83D\uDEAA',function(){ window.location.href='logout.php'; }); return false;">Logout</a>
  </div>
</div>

<div id="content">

  <div class="summary-bar" id="summary-bar">
    <div class="summary-card"><div class="val" id="s-total-inv">-</div><div class="lbl">Total Invoices</div></div>
    <div class="summary-card"><div class="val" id="s-filter-revenue">-</div><div class="lbl">Filtered Revenue (KD)</div></div>
    <div class="summary-card"><div class="val" id="s-today">-</div><div class="lbl">Today's Invoices</div></div>
    <div class="summary-card"><div class="val" id="s-revenue">-</div><div class="lbl">Today's Revenue (KD)</div></div>
  </div>

  <div class="search-bar">
    <input type="text" id="search-input" placeholder="Search invoice number..." oninput="filterTable()">
    <input type="date" id="from-date">
    <input type="date" id="to-date">
    <select id="payment-filter" style="padding:8px 12px;background:#fff;border:1px solid #ced4da;color:#2c3e50;border-radius:6px;font-size:14px;">
      <option value="">All Payment</option>
      <option value="Cash">Cash</option>
      <option value="KNET">KNET</option>
      <option value="WAMT">WAMT</option>
      <option value="Talabat">Talabat</option>
      <option value="Keeta">Keeta</option>
    </select>
    <?php if (is_admin()): ?><select id="user-filter" style="padding:8px 12px;background:#fff;border:1px solid #ced4da;color:#2c3e50;border-radius:6px;font-size:14px;"><option value="">All Users</option></select><?php endif; ?>
    <button class="filter-btn" onclick="loadInvoices(1)">&#128269; Filter</button>
    <button class="filter-btn clear" onclick="clearDateFilter()">&#10006; Clear</button>
  </div>

  <div class="export-bar">
    <button class="export-btn export-print" onclick="window.print()">&#128424; Print Report</button>
    <button class="export-btn export-pdf" onclick="exportToPDF()">&#128196; Export to PDF</button>
    <button class="export-btn export-excel" onclick="exportToExcel()">&#128200; Export to Excel</button>
  </div>
  <div class="filter-summary" id="filter-summary" style="display:none">
    <div>Filtered: <span id="fs-count">0</span> invoices</div>
    <div>Total: <span id="fs-total">0.000</span> KD</div>
    <div>Period: <span id="fs-period">-</span></div>
    <div>Payment: <span id="fs-payment">All</span></div>
    <div>User: <span id="fs-user">All</span></div>
  </div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Invoice No.</th>
        <th>Date</th>
        <th>User</th>
        <th>Total (KD)</th>
        <th>Payment</th>
        <th>Ref</th>
        <th>Change</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody id="inv-table-body">
      <tr><td colspan="9" class="loading">Loading invoices...</td></tr>
    </tbody>
  </table>

  <div class="pagination" id="pagination"></div>
</div>

<!-- Detail Overlay -->
<div id="detail-overlay">
  <div id="detail-box">
    <h3 id="detail-title">Invoice Details</h3>
    <div id="detail-content"></div>
    <div class="detail-btns">
      <a class="btn-reprint" id="detail-reprint-link" href="#" target="_blank">&#128424; Reprint</a>
      <button class="btn-close" onclick="closeDetail()">Close</button>
    </div>
  </div>
</div>

<script>
var allInvoices = [];
var currentPage = 1;

function loadInvoices(page) {
    currentPage = page || 1;
    var params = 'page=' + currentPage;
    var from = document.getElementById('from-date').value;
    var to = document.getElementById('to-date').value;
    if (from) params += '&from=' + encodeURIComponent(from);
    if (to) params += '&to=' + encodeURIComponent(to);
    var paymentFilter = document.getElementById('payment-filter');
    var paymentName = 'All';
    if (paymentFilter && paymentFilter.value) {
        params += '&payment=' + encodeURIComponent(paymentFilter.value);
        paymentName = paymentFilter.options[paymentFilter.selectedIndex].text;
    }
    var userFilter = document.getElementById('user-filter');
    var userName = 'All';
    if (userFilter && userFilter.value) {
        params += '&user_id=' + encodeURIComponent(userFilter.value);
        userName = userFilter.options[userFilter.selectedIndex].text;
    }
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/get_invoices.php?' + params, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            allInvoices = data.invoices || [];
            renderTable(allInvoices);
            renderPagination(data.total_pages, currentPage);
            document.getElementById('s-total-inv').textContent = data.total;
            document.getElementById('s-filter-revenue').textContent = parseFloat(data.revenue || 0).toFixed(3);
            document.getElementById('s-today').textContent = data.today_total || 0;
            document.getElementById('s-revenue').textContent = parseFloat(data.today_revenue || 0).toFixed(3);
            renderUserFilter(data.users || []);
            renderUserSummary(data.user_summary || []);
            // Update filter summary bar
            var fs = document.getElementById('filter-summary');
            if (from || to || (userFilter && userFilter.value) || (paymentFilter && paymentFilter.value)) {
                fs.style.display = 'flex';
                document.getElementById('fs-count').textContent = data.total;
                document.getElementById('fs-total').textContent = parseFloat(data.revenue || 0).toFixed(3);
                document.getElementById('fs-period').textContent = (from || '?') + ' → ' + (to || '?');
                document.getElementById('fs-user').textContent = userName;
                document.getElementById('fs-payment').textContent = paymentName;
            } else {
                fs.style.display = 'none';
            }
        }
    };
    xhr.send();
}

function renderTable(invoices) {
    if (!invoices || invoices.length === 0) {
        document.getElementById('inv-table-body').innerHTML = '<tr><td colspan="8" class="loading">No invoices found.</td></tr>';
        return;
    }
    var isAdmin = <?php echo is_admin() ? 'true' : 'false'; ?>;
    var html = '';
    for (var i = 0; i < invoices.length; i++) {
        var inv = invoices[i];
        html += '<tr id="inv-row-' + inv.id + '">';
        html += '<td style="color:#555">' + inv.id + '</td>';
        html += '<td style="color:#aac4ff">' + inv.invoice_number + '</td>';
        html += '<td>' + inv.created_at + '</td>';
        html += '<td style="color:#aac4ff">' + (inv.user_name || 'Unknown') + '</td>';
        html += '<td class="amount">' + parseFloat(inv.total).toFixed(3) + '</td>';
        html += '<td style="color:#888">' + (inv.payment_mode || 'Cash') + '</td>';
        html += '<td style="color:#666;font-size:12px">' + (inv.payment_reference || '-') + '</td>';
        html += '<td class="change-td">' + parseFloat(inv.change_due).toFixed(3) + '</td>';
        html += '<td style="white-space:nowrap">';
        html += '<button class="btn-view" onclick="showDetail(' + inv.id + ')">&#128065; View</button> ';
        html += '<a class="btn-view" href="receipt.php?id=' + inv.id + '" target="_blank">&#128424; Print</a>';
        if (isAdmin) {
            html += ' <button class="btn-delete" onclick="deleteInvoice(' + inv.id + ', \'' + inv.invoice_number + '\')">&#128465; Del</button>';
        }
        html += '</td>';
        html += '</tr>';
    }
    document.getElementById('inv-table-body').innerHTML = html;
}

function renderUserFilter(users) {
    var sel = document.getElementById('user-filter');
    if (!sel || sel.options.length > 1) return;
    for (var i = 0; i < users.length; i++) {
        var opt = document.createElement('option');
        opt.value = users[i].id;
        opt.textContent = users[i].full_name;
        sel.appendChild(opt);
    }
}

function renderUserSummary(rows) {
    var old = document.getElementById('user-summary-box');
    if (old) old.remove();
    var box = document.createElement('div');
    box.id = 'user-summary-box';
    box.className = 'summary-bar';
    var html = '';
    for (var i = 0; i < rows.length; i++) {
        html += '<div class="summary-card"><div class="val">' + parseFloat(rows[i].total_sales || 0).toFixed(3) + '</div><div class="lbl">' + rows[i].user_name + ' (' + rows[i].invoice_count + ' invoices)</div></div>';
    }
    box.innerHTML = html || '<div class="summary-card"><div class="val">0.000</div><div class="lbl">No user sales</div></div>';
    document.getElementById('summary-bar').after(box);
}

function filterTable() {
    var q = document.getElementById('search-input').value.toLowerCase();
    if (!q) { renderTable(allInvoices); return; }
    var filtered = allInvoices.filter(function(inv) {
        return inv.invoice_number.toLowerCase().indexOf(q) > -1;
    });
    renderTable(filtered);
}

function clearDateFilter() {
    document.getElementById('from-date').value = '';
    document.getElementById('to-date').value = '';
    var paymentFilter = document.getElementById('payment-filter');
    if (paymentFilter) paymentFilter.value = '';
    var userFilter = document.getElementById('user-filter');
    if (userFilter) userFilter.value = '';
    loadInvoices(1);
}

function renderPagination(total, current) {
    if (total <= 1) { document.getElementById('pagination').innerHTML = ''; return; }
    var html = '';
    // First & Prev
    html += '<button class="page-btn" onclick="loadInvoices(1)" ' + (current===1?'disabled':'') + '>&laquo; First</button>';
    html += '<button class="page-btn" onclick="loadInvoices(' + (current-1) + ')" ' + (current===1?'disabled':'') + '>&lsaquo; Prev</button>';
    // Numbered window: show up to 5 pages around current
    var start = Math.max(1, current - 2);
    var end   = Math.min(total, current + 2);
    if (start > 1) html += '<span style="color:#555;padding:6px 4px">...</span>';
    for (var p = start; p <= end; p++) {
        html += '<button class="page-btn' + (p===current?' active':'') + '" onclick="loadInvoices(' + p + ')">' + p + '</button>';
    }
    if (end < total) html += '<span style="color:#555;padding:6px 4px">...</span>';
    // Next & Last
    html += '<button class="page-btn" onclick="loadInvoices(' + (current+1) + ')" ' + (current===total?'disabled':'') + '>Next &rsaquo;</button>';
    html += '<button class="page-btn" onclick="loadInvoices(' + total + ')" ' + (current===total?'disabled':'') + '>Last &raquo;</button>';
    html += '<span style="color:#666;font-size:12px;padding:6px 8px">Page ' + current + ' of ' + total + '</span>';
    document.getElementById('pagination').innerHTML = html;
}

function showDetail(id) {
    document.getElementById('detail-content').innerHTML = '<div class="loading">Loading...</div>';
    document.getElementById('detail-overlay').className = 'show';
    document.getElementById('detail-reprint-link').href = 'receipt.php?id=' + id;

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/get_invoices.php?id=' + id, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var inv = JSON.parse(xhr.responseText);
            document.getElementById('detail-title').textContent = inv.invoice_number;
            var html = '<table class="detail-table">';
            html += '<tr><td class="d-name"><b>Item</b></td><td style="text-align:center"><b>Qty</b></td><td class="d-price"><b>Subtotal</b></td></tr>';
            (inv.items || []).forEach(function(it) {
                html += '<tr><td class="d-name">' + it.item_name + '</td>';
                html += '<td style="text-align:center;color:#888">' + it.quantity + '</td>';
                html += '<td class="d-price">' + parseFloat(it.subtotal).toFixed(3) + '</td></tr>';
            });
            html += '</table>';
            html += '<div class="detail-total">Total: ' + parseFloat(inv.total).toFixed(3) + ' KD</div>';
            html += '<div style="font-size:12px;color:#888;margin-top:6px">';
            html += 'Cash: ' + parseFloat(inv.cash_paid).toFixed(3) + ' KD &nbsp;|&nbsp; Change: ' + parseFloat(inv.change_due).toFixed(3) + ' KD';
            html += '<br>Date: ' + inv.created_at;
            html += '</div>';
            document.getElementById('detail-content').innerHTML = html;
        }
    };
    xhr.send();
}

function closeDetail() {
    document.getElementById('detail-overlay').className = '';
}

function deleteInvoice(id, invoiceNum) {
    showConfirm('Delete Invoice', 'Delete invoice <b style="color:#f39c12">' + invoiceNum + '</b>?<br><span style="color:#e74c3c">This cannot be undone.</span>', 'Yes, Delete', '&#128465;', function() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'api/delete_invoice.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var res = JSON.parse(xhr.responseText);
                if (res.success) {
                    var row = document.getElementById('inv-row-' + id);
                    if (row) row.remove();
                    allInvoices = allInvoices.filter(function(i) { return i.id != id; });
                    loadInvoices(currentPage);
                } else {
                    showConfirm('Error', 'Delete failed: ' + (res.error || 'Unknown error'), 'OK', '&#10060;', function(){});
                }
            }
        };
        xhr.send(JSON.stringify({id: id}));
    });
}

loadInvoices(1);

// Export to Excel (CSV)
function exportToExcel() {
    if (!allInvoices || allInvoices.length === 0) {
        alert('No invoices to export');
        return;
    }
    
    var csv = 'Invoice No,Date,User,Total (KD),Payment,Ref,Change (KD)\n';
    var totalAmount = 0;
    
    for (var i = 0; i < allInvoices.length; i++) {
        var inv = allInvoices[i];
        csv += inv.invoice_number + ',';
        csv += inv.created_at + ',';
        csv += (inv.user_name || 'Unknown') + ',';
        csv += parseFloat(inv.total).toFixed(3) + ',';
        csv += (inv.payment_mode || 'Cash') + ',';
        csv += (inv.payment_reference || '') + ',';
        csv += parseFloat(inv.change_due).toFixed(3) + '\n';
        totalAmount += parseFloat(inv.total);
    }
    
    csv += '\nTotal,' + allInvoices.length + ' invoices,' + totalAmount.toFixed(3) + ' KD\n';
    
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    var url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'invoices_export_' + new Date().toISOString().slice(0,10) + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Export to PDF (using window.print() as PDF)
function exportToPDF() {
    // Load all invoices (not just current page) for full export
    var params = 'page=1&per_page=9999';
    var from = document.getElementById('from-date').value;
    var to = document.getElementById('to-date').value;
    if (from) params += '&from=' + encodeURIComponent(from);
    if (to) params += '&to=' + encodeURIComponent(to);
    var paymentFilter = document.getElementById('payment-filter');
    if (paymentFilter && paymentFilter.value) {
        params += '&payment=' + encodeURIComponent(paymentFilter.value);
    }
    var userFilter = document.getElementById('user-filter');
    if (userFilter && userFilter.value) {
        params += '&user_id=' + encodeURIComponent(userFilter.value);
    }
    
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/get_invoices.php?' + params, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var data = JSON.parse(xhr.responseText);
            var allInvoicesFull = data.invoices || [];
            
            // Generate PDF content
            var content = generatePDFContent(allInvoicesFull, data);
            
            // Open in new window for print to PDF
            var win = window.open('', '_blank');
            win.document.write(content);
            win.document.close();
            setTimeout(function() { win.print(); }, 500);
        }
    };
    xhr.send();
}

function generatePDFContent(invoices, data) {
    var totalAmount = 0;
    var rows = '';
    
    for (var i = 0; i < invoices.length; i++) {
        var inv = invoices[i];
        totalAmount += parseFloat(inv.total);
        rows += '<tr>';
        rows += '<td>' + inv.invoice_number + '</td>';
        rows += '<td>' + inv.created_at + '</td>';
        rows += '<td>' + (inv.user_name || 'Unknown') + '</td>';
        rows += '<td>' + parseFloat(inv.total).toFixed(3) + '</td>';
        rows += '<td>' + (inv.payment_mode || 'Cash') + '</td>';
        rows += '<td>' + (inv.payment_reference || '-') + '</td>';
        rows += '<td>' + parseFloat(inv.change_due).toFixed(3) + '</td>';
        rows += '</tr>';
    }
    
    var from = document.getElementById('from-date').value || 'All';
    var to = document.getElementById('to-date').value || 'All';
    var paymentFilter = document.getElementById('payment-filter');
    var paymentName = paymentFilter && paymentFilter.value ? paymentFilter.options[paymentFilter.selectedIndex].text : 'All';
    var userFilter = document.getElementById('user-filter');
    var userName = userFilter && userFilter.value ? userFilter.options[userFilter.selectedIndex].text : 'All';
    
    var html = '<!DOCTYPE html><html><head><title>Invoice Report</title>';
    html += '<style>';
    html += 'body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }';
    html += 'h1 { text-align: center; margin-bottom: 20px; }';
    html += '.summary { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }';
    html += '.summary div { background: #f5f5f5; padding: 10px; border-radius: 5px; }';
    html += '.summary b { font-size: 16px; }';
    html += 'table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }';
    html += 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
    html += 'th { background: #f0f0f0; font-weight: bold; }';
    html += '.total-row { font-weight: bold; background: #e9ecef; }';
    html += '</style>';
    html += '</head><body>';
    html += '<h1><?php echo htmlspecialchars($company['company_name_en']); ?> - Invoice Report</h1>';
    html += '<div class="summary">';
    html += '<div><b>' + invoices.length + '</b> Invoices</div>';
    html += '<div><b>' + totalAmount.toFixed(3) + '</b> KD Total</div>';
    html += '<div><b>Period:</b> ' + from + ' to ' + to + '</div>';
    html += '<div><b>Payment:</b> ' + paymentName + '</div>';
    html += '<div><b>User:</b> ' + userName + '</div>';
    html += '</div>';
    html += '<table>';
    html += '<thead><tr><th>Invoice No</th><th>Date</th><th>User</th><th>Total (KD)</th><th>Payment</th><th>Ref</th><th>Change (KD)</th></tr></thead>';
    html += '<tbody>' + rows + '</tbody>';
    html += '<tfoot><tr class="total-row"><td colspan="3">Total</td><td>' + totalAmount.toFixed(3) + ' KD</td><td>-</td><td>-</td><td>-</td></tr></tfoot>';
    html += '</table>';
    html += '</body></html>';
    
    return html;
}
</script>
<?php include 'includes/confirm_modal.php'; ?>
</body>
</html>
