<?php
require_once 'db/connect.php';
require_once 'auth.php';
require_login();
$company = get_company_settings();
$currentUser = current_user();

// Fetch pending pre-orders
$whereUser = is_admin() ? '' : ' AND i.user_id = ' . intval($currentUser['id']);
$preorders = $conn->query("SELECT i.id, i.invoice_number, i.customer_name, i.customer_phone, i.total, i.created_at, i.user_name, i.status
    FROM invoices i
    WHERE i.order_type = 'pre_order' AND i.status = 'open' $whereUser
    ORDER BY i.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Pre-Orders - <?php echo htmlspecialchars($company['company_name_en']); ?></title>
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
.section { background:#fff; border:1px solid #dee2e6; border-radius:10px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05); }
.section-title { background:#e9ecef; color:#495057; padding:10px 14px; font-weight:bold; }
.empty { padding:40px; color:#95a5a6; text-align:center; font-size:15px; }
table { width:100%; border-collapse:collapse; }
th { text-align:left; color:#495057; font-size:12px; padding:10px 12px; border-bottom:2px solid #dee2e6; background:#f8f9fa; }
td { padding:10px 12px; border-bottom:1px solid #e9ecef; font-size:13px; }
.amount { color:#e67e22; font-weight:bold; }
.badge { padding:3px 8px; border-radius:12px; font-size:11px; }
.badge.pending { background:#fff3cd; color:#856404; }
.badge.paid { background:#d4edda; color:#155724; }
.badge.cancelled { background:#f8d7da; color:#721c24; }
.btn-action { background:#3498db; color:#fff; border:none; padding:5px 12px; border-radius:4px; cursor:pointer; font-size:12px; margin-right:4px; }
.btn-action:hover { background:#2980b9; }
.btn-print { background:#27ae60; color:#fff; border:none; padding:5px 12px; border-radius:4px; cursor:pointer; font-size:12px; }
.btn-print:hover { background:#1e8449; }
.btn-cancel { background:#e74c3c; color:#fff; border:none; padding:5px 12px; border-radius:4px; cursor:pointer; font-size:12px; }
.btn-cancel:hover { background:#c0392b; }
</style>
</head>
<body>
<div id="header">
    <h1>&#128203; Pre-Orders</h1>
    <div>
        <a href="index.php">&#8592; Back to POS</a>
        <a href="dashboard.php">&#128200; Dashboard</a>
        <a href="invoices.php">&#128196; Invoices</a>
    </div>
</div>
<div id="content">
    <div class="page-title">Pending Pre-Orders</div>
    <div class="page-sub">Customer orders waiting to be fulfilled and paid.</div>

    <div class="section">
        <div class="section-title">&#9201; Pending Orders</div>
        <?php if ($preorders && $preorders->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $preorders->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['invoice_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name'] ?: 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_phone'] ?: 'N/A'); ?></td>
                    <td class="amount"><?php echo number_format($row['total'], 3); ?> KD</td>
                    <td><span class="badge <?php echo $row['status'] === 'open' ? 'pending' : 'paid'; ?>"><?php echo $row['status'] === 'open' ? 'UNPAID' : htmlspecialchars($row['status'] ?: 'N/A'); ?></span></td>
                    <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($row['created_at']))); ?></td>
                    <td><?php echo htmlspecialchars((!empty($row['user_name']) && $row['user_name'] !== '0') ? $row['user_name'] : 'Unknown'); ?></td>
                    <td>
                        <button class="btn-action" onclick="loadToPos(<?php echo intval($row['id']); ?>)">&#128722; Load to POS</button>
                        <button class="btn-print" onclick="printReceipt(<?php echo intval($row['id']); ?>)">&#128424; Receipt</button>
                        <button class="btn-cancel" onclick="cancelOrder(<?php echo intval($row['id']); ?>, this)">&#10060; Cancel</button>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty">No pending pre-orders.</div>
        <?php endif; ?>
    </div>
</div>

<script>
function loadToPos(invoiceId) {
    showConfirm('Load to POS', 'Load this pre-order into the POS to complete payment?', 'Load', '&#128722;', function() {
        window.location.href = 'index.php?load_preorder=' + invoiceId;
    });
}
function printReceipt(invoiceId) {
    window.open('receipt.php?id=' + invoiceId, '_blank');
}
function cancelOrder(invoiceId, btn) {
    showConfirm('Cancel Pre-Order', 'Cancel this pre-order?<br><span style="color:#e74c3c">This cannot be undone.</span>', 'Yes, Cancel', '&#10060;', function() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'api/cancel_preorder.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        btn.closest('tr').remove();
                    } else {
                        showConfirm('Error', 'Cancel failed: ' + (res.error || 'Unknown error'), 'OK', '&#10060;', function(){});
                    }
                } catch(e) { showConfirm('Error', 'Server error', 'OK', '&#10060;', function(){}); }
            }
        };
        xhr.send(JSON.stringify({invoice_id: invoiceId}));
    });
}
</script>
<?php include 'includes/confirm_modal.php'; ?>
</body>
</html>
