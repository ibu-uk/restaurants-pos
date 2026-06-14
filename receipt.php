<?php
require_once 'db/connect.php';
require_once 'auth.php';
require_login();
$company = get_company_settings();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$invoice = null;
$items_list = [];

if ($id > 0) {
    $extra = is_admin() ? '' : ' AND user_id = ' . intval(current_user()['id']);
    $inv_res = $conn->query("SELECT * FROM invoices WHERE id = $id $extra");
    if ($inv_res) $invoice = $inv_res->fetch_assoc();

    if ($invoice) {
        $items_res = $conn->query("SELECT * FROM invoice_items WHERE invoice_id = $id ORDER BY id");
        if ($items_res) {
            while ($row = $items_res->fetch_assoc()) $items_list[] = $row;
        }
    }
}

// An unpaid pre-order: still open and of type pre_order (no payment collected yet)
$is_unpaid_preorder = $invoice
    && isset($invoice['order_type']) && $invoice['order_type'] === 'pre_order'
    && isset($invoice['status']) && $invoice['status'] === 'open';

// For delivery / pre-orders: fetch the customer's address from the customers table
$customer_address = '';
if ($invoice && !empty($invoice['customer_id'])) {
    $cid = intval($invoice['customer_id']);
    $cres = $conn->query("SELECT address FROM customers WHERE id = $cid LIMIT 1");
    if ($cres && ($crow = $cres->fetch_assoc())) {
        $customer_address = $crow['address'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Receipt - Burge Al Salhiya</title>
<style>
/* ===== SCREEN STYLES ===== */
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'Courier New', Courier, monospace; background:#f0f0f0; display:flex; justify-content:center; padding:20px; }

.page-wrap { display:flex; flex-direction:column; align-items:center; gap:12px; max-width:400px; width:100%; }

.action-bar {
    display:flex; gap:10px; width:100%;
}
.action-bar button {
    flex:1; padding:10px; border:none; border-radius:6px; cursor:pointer;
    font-size:14px; font-weight:bold; font-family:Tahoma,Arial,sans-serif;
}
.btn-print   { background:#2980b9; color:#fff; }
.btn-print:hover { background:#3498db; }
.btn-new     { background:#27ae60; color:#fff; }
.btn-new:hover { background:#2ecc71; }
.btn-invoices { background:#7f8c8d; color:#fff; }
.btn-invoices:hover { background:#95a5a6; }

/* ===== RECEIPT ===== */
.receipt {
    background:#fff; width:100%; max-width:380px;
    padding:20px 16px; border:1px solid #ccc;
    box-shadow:2px 2px 8px rgba(0,0,0,0.15);
}

.receipt-header { text-align:center; border-bottom:2px dashed #333; padding-bottom:12px; margin-bottom:12px; }
.receipt-header .logo { font-size:18px; font-weight:bold; letter-spacing:1px; color:#000; }
.receipt-header .logo-ar { font-size:15px; direction:rtl; margin-top:2px; font-weight:bold; color:#000; }
.receipt-header .contact { font-size:12px; color:#000; margin-top:6px; font-weight:bold; }

.inv-info { font-size:11px; color:#000; margin-bottom:10px; }
.inv-info div { display:flex; justify-content:space-between; padding:3px 0; }
.bi-label { font-weight:bold; color:#000; }
.bi-label .ar { direction:rtl; font-weight:bold; color:#000; }

.receipt-items { border-top:1px dashed #999; border-bottom:1px dashed #999; padding:8px 0; margin-bottom:8px; }
.ri-header { display:grid; grid-template-columns: 1fr 35px 60px 65px; column-gap:6px; font-size:10px; font-weight:bold; color:#000; padding-bottom:4px; border-bottom:1px solid #eee; margin-bottom:4px; }
.ri-header .col-item  { text-align:left; }
.ri-header .col-qty   { text-align:center; }
.ri-header .col-price { text-align:right; }
.ri-header .col-sub   { text-align:right; }

.ri-row { display:grid; grid-template-columns: 1fr 35px 60px 65px; column-gap:6px; font-size:12px; padding:3px 0; align-items:start; }
.ri-row .col-item  { line-height:1.3; min-width:0; overflow-wrap:anywhere; font-weight:bold; color:#000; }
.ri-row .col-qty   { text-align:center; color:#000; font-weight:bold; }
.ri-row .col-price { text-align:right; color:#000; font-weight:bold; }
.ri-row .col-sub   { text-align:right; font-weight:bold; color:#000; }
.item-ar { direction:rtl; color:#000; font-size:11px; font-weight:bold; }

.receipt-totals { font-size:13px; color:#000; }
.receipt-totals .t-row { display:flex; justify-content:space-between; padding:3px 0; font-weight:bold; color:#000; }
.receipt-totals .t-row.grand { font-size:16px; font-weight:bold; border-top:2px solid #333; margin-top:6px; padding-top:6px; color:#000; }
.receipt-totals .t-row.change { color:#000; font-weight:bold; }
.receipt-totals .ar { direction:rtl; font-weight:bold; color:#000; }

.receipt-footer { text-align:center; border-top:2px dashed #333; padding-top:12px; margin-top:12px; font-size:11px; color:#000; font-weight:bold; }
.receipt-footer .thank-you { font-size:14px; font-weight:bold; color:#000; margin-bottom:4px; }

.additions-note { background:#f9f9e4; border:1px solid #ddd; padding:6px 8px; text-align:center; font-size:11px; color:#777; margin-top:8px; }

/* ===== DELIVERY DETAILS (pre-orders) ===== */
.delivery-box { border:2px solid #000; border-radius:4px; padding:8px 10px; margin-bottom:10px; font-size:12px; color:#000; }
.delivery-title { font-weight:bold; text-align:center; border-bottom:1px dashed #333; padding-bottom:4px; margin-bottom:6px; font-size:12px; }
.delivery-box .dl-row { padding:3px 0; line-height:1.4; overflow-wrap:anywhere; }
.delivery-box .dl-label { font-weight:bold; }
.delivery-box .dl-addr { font-weight:bold; }
.unpaid-note { text-align:center; font-weight:bold; font-size:14px; color:#000; border:2px solid #000; border-radius:4px; padding:6px; margin-top:8px; letter-spacing:1px; }
.copy-cut { text-align:center; color:#000; font-size:12px; margin:14px 0; letter-spacing:2px; }

/* ===== PRINT STYLES ===== */
@media print {
    body { background:#fff; padding:0; margin:0; }
    .page-wrap { padding:0; margin:0; }
    .action-bar { display:none !important; }
    .receipt { border:none; box-shadow:none; max-width:100%; padding:8px; }
    @page { margin:5mm; }
}
</style>
</head>
<body>

<div class="page-wrap">

  <!-- Action Buttons (hidden on print) -->
  <div class="action-bar">
    <button class="btn-print" onclick="window.print()">&#128424; Print Receipt</button>
  </div>

  <!-- Receipt -->
  <div class="receipt">

    <div class="receipt-header">
      <?php if ($company['logo_path']): ?>
        <img src="<?php echo htmlspecialchars($company['logo_path']); ?>" alt="Logo" style="max-width:80px; max-height:50px; margin-bottom:8px;">
      <?php endif; ?>
      <div class="logo"><?php echo htmlspecialchars($company['company_name_en']); ?></div>
      <div class="logo-ar"><?php echo htmlspecialchars($company['company_name_ar']); ?></div>
      <?php if ($company['phone'] || $company['address']): ?>
        <div class="contact">
          <?php if ($company['phone']): ?>Tel: <?php echo htmlspecialchars($company['phone']); ?><?php endif; ?>
          <?php if ($company['phone'] && $company['address']): ?> &nbsp;|&nbsp; <?php endif; ?>
          <?php if ($company['address']): ?>&#9670; <?php echo htmlspecialchars($company['address']); ?><?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($invoice): ?>
    <div class="inv-info">
      <div><span class="bi-label">Invoice # / <span class="ar">رقم الفاتورة</span></span><span><?php echo htmlspecialchars($invoice['invoice_number']); ?></span></div>
      <div><span class="bi-label">Date / <span class="ar">التاريخ</span></span><span><?php echo date('d/m/Y  H:i', strtotime($invoice['created_at'])); ?></span></div>
      <?php if (!empty($invoice['table_name'])): ?>
      <div><span class="bi-label">Table / <span class="ar">الطاولة</span></span><span><?php echo htmlspecialchars($invoice['table_name']); ?></span></div>
      <?php endif; ?>
      <?php if (!$is_unpaid_preorder): ?>
      <div><span class="bi-label">Payment / <span class="ar">طريقة الدفع</span></span><span><?php echo htmlspecialchars($invoice['payment_mode'] ?? 'Cash'); ?></span></div>
      <?php endif; ?>
      <?php if (!empty($invoice['payment_reference'])): ?>
      <div><span class="bi-label">Ref / <span class="ar">المرجع</span></span><span><?php echo htmlspecialchars($invoice['payment_reference']); ?></span></div>
      <?php endif; ?>
    </div>

    <?php if (!empty($invoice['customer_name']) || !empty($invoice['customer_phone']) || $customer_address !== ''): ?>
    <div class="delivery-box">
      <div class="delivery-title">&#128666; DELIVERY DETAILS / تفاصيل التوصيل</div>
      <?php if (!empty($invoice['customer_name'])): ?>
      <div class="dl-row"><span class="dl-label">Name / الاسم:</span> <?php echo htmlspecialchars($invoice['customer_name']); ?></div>
      <?php endif; ?>
      <?php if (!empty($invoice['customer_phone'])): ?>
      <div class="dl-row"><span class="dl-label">Mobile / الهاتف:</span> <?php echo htmlspecialchars($invoice['customer_phone']); ?></div>
      <?php endif; ?>
      <?php if ($customer_address !== ''): ?>
      <div class="dl-row"><span class="dl-label">Address / العنوان:</span> <span class="dl-addr"><?php echo nl2br(htmlspecialchars($customer_address)); ?></span></div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="receipt-items">
      <div class="ri-header">
        <span class="col-item">Item / الصنف</span>
        <span class="col-qty">Qty<br>الكمية</span>
        <span class="col-price">Price<br>السعر</span>
        <span class="col-sub">Total<br>المجموع</span>
      </div>
      <?php foreach ($items_list as $item): ?>
      <div class="ri-row">
        <span class="col-item">
          <?php
            $display_name_ar = isset($item['item_name_ar']) ? $item['item_name_ar'] : '';
            if ($display_name_ar === '') {
                $base_item_name = preg_replace('/\s+\((Small|Medium|Large)\)$/', '', $item['item_name']);
                $lookup = $conn->prepare("SELECT name_ar FROM items WHERE name_en = ? LIMIT 1");
                $lookup->bind_param('s', $base_item_name);
                $lookup->execute();
                $lookup_res = $lookup->get_result();
                if ($lookup_res && $lookup_res->num_rows > 0) {
                    $display_name_ar = $lookup_res->fetch_assoc()['name_ar'];
                }
                $lookup->close();
            }
          ?>
          <?php echo htmlspecialchars($item['item_name']); ?><?php if ($display_name_ar !== ''): ?> / <span class="item-ar"><?php echo htmlspecialchars($display_name_ar); ?></span><?php endif; ?>
        </span>
        <span class="col-qty"><?php echo $item['quantity']; ?></span>
        <span class="col-price"><?php echo number_format($item['price'], 3); ?></span>
        <span class="col-sub"><?php echo number_format($item['subtotal'], 3); ?></span>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="receipt-totals">
      <div class="t-row grand">
        <span>TOTAL / <span class="ar">الإجمالي</span></span>
        <span><?php echo number_format($invoice['total'], 3); ?> KD</span>
      </div>
      <?php if (!$is_unpaid_preorder): ?>
      <div class="t-row">
        <span>Cash Paid / <span class="ar">المبلغ المدفوع</span></span>
        <span><?php echo number_format($invoice['cash_paid'], 3); ?> KD</span>
      </div>
      <div class="t-row change">
        <span>Change / <span class="ar">الباقي</span></span>
        <span><?php echo number_format($invoice['change_due'], 3); ?> KD</span>
      </div>
      <?php endif; ?>
    </div>

    <?php else: ?>
    <div style="text-align:center; padding:20px; color:#999;">Invoice not found.</div>
    <?php endif; ?>

    <div class="receipt-footer">
      <div class="thank-you"><?php echo htmlspecialchars($company['invoice_footer']); ?></div>
      <?php if ($company['email']): ?><div><?php echo htmlspecialchars($company['email']); ?></div><?php endif; ?>
    </div>

  </div><!-- /receipt -->

</div>

<script>
// Auto-print when opened from POS
<?php if (isset($_GET['autoprint']) && $_GET['autoprint'] == '1'): ?>
window.onload = function() { window.print(); };
<?php endif; ?>
</script>

</body>
</html>
