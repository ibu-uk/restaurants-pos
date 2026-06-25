<?php
require_once 'db/connect.php';
require_once 'auth.php';
require_login();
$company = get_company_settings();

// Get order data from URL parameters
$items_json = isset($_GET['items']) ? $_GET['items'] : '';
$subtotal = isset($_GET['subtotal']) ? floatval($_GET['subtotal']) : 0;
$discount = isset($_GET['discount']) ? floatval($_GET['discount']) : 0;
$total = isset($_GET['total']) ? floatval($_GET['total']) : 0;
$table_name = isset($_GET['table_name']) ? $_GET['table_name'] : '';

$items_list = [];
if ($items_json) {
    $items_list = json_decode(urldecode($items_json), true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Bill Preview - <?php echo htmlspecialchars($company['company_name_en']); ?></title>
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
.btn-close    { background:#e74c3c; color:#fff; }
.btn-close:hover { background:#c0392b; }

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
.receipt-totals .ar { direction:rtl; font-weight:bold; color:#000; }

.preview-note { text-align:center; font-weight:bold; font-size:14px; color:#e67e22; border:2px solid #e67e22; border-radius:4px; padding:6px; margin-top:8px; letter-spacing:1px; }

.receipt-footer { text-align:center; border-top:2px dashed #333; padding-top:12px; margin-top:12px; font-size:11px; color:#000; font-weight:bold; }
.receipt-footer .thank-you { font-size:14px; font-weight:bold; color:#000; margin-bottom:4px; }

/* ===== PRINT STYLES ===== */
@media print {
    body { background:#fff; padding:0; margin:0; }
    .page-wrap { padding:0; margin:0; }
    .action-bar { display:none !important; }
    .receipt { border:none; box-shadow:none; max-width:100%; padding:8px; }
    .preview-note { display:none !important; }
    @page { margin:5mm; }
}
</style>
</head>
<body>

<div class="page-wrap">

  <!-- Action Buttons (hidden on print) -->
  <div class="action-bar">
    <button class="btn-print" onclick="window.print()">&#128424; Print</button>
    <button class="btn-close" onclick="window.close()">Close</button>
  </div>

  <!-- Receipt -->
  <div class="receipt">

    <div class="receipt-header">
      <?php if ($company['logo_path'] && intval($company['logo_on_receipt'] ?? 1) === 1): ?>
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

    <div class="inv-info">
      <div><span class="bi-label">Date / <span class="ar">التاريخ</span></span><span><?php echo date('d/m/Y  H:i'); ?></span></div>
      <?php if ($table_name): ?>
      <div><span class="bi-label">Table / <span class="ar">الطاولة</span></span><span><?php echo htmlspecialchars($table_name); ?></span></div>
      <?php endif; ?>
    </div>

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
          <?php echo htmlspecialchars($item['name']); ?>
          <?php if (!empty($item['name_ar'])): ?> / <span class="item-ar"><?php echo htmlspecialchars($item['name_ar']); ?></span><?php endif; ?>
          <?php if (!empty($item['size'])): ?> <span style="font-size:10px;color:#7f8c8d;">(<?php echo htmlspecialchars($item['size']); ?>)</span><?php endif; ?>
        </span>
        <span class="col-qty"><?php echo $item['qty']; ?></span>
        <span class="col-price"><?php echo number_format($item['price'], 3); ?></span>
        <span class="col-sub"><?php echo number_format($item['price'] * $item['qty'], 3); ?></span>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="receipt-totals">
      <?php if ($subtotal > 0): ?>
      <div class="t-row">
        <span>Subtotal / <span class="ar">المجموع الفرعي</span></span>
        <span><?php echo number_format($subtotal, 3); ?> KD</span>
      </div>
      <?php endif; ?>
      <?php if ($discount > 0): ?>
      <div class="t-row" style="color:#e74c3c;">
        <span>Discount / <span class="ar">خصم</span></span>
        <span>-<?php echo number_format($discount, 3); ?> KD</span>
      </div>
      <?php endif; ?>
      <div class="t-row grand">
        <span>TOTAL / <span class="ar">الإجمالي</span></span>
        <span><?php echo number_format($total, 3); ?> KD</span>
      </div>
    </div>

    <div class="preview-note">*** PREVIEW - NOT PAID ***</div>

    <div class="receipt-footer">
      <div class="thank-you"><?php echo htmlspecialchars($company['invoice_footer']); ?></div>
      <?php if ($company['email']): ?><div><?php echo htmlspecialchars($company['email']); ?></div><?php endif; ?>
    </div>

  </div><!-- /receipt -->

</div>

</body>
</html>
