<?php
require_once 'db/connect.php';
require_once 'auth.php';
require_login();
$company = get_company_settings();
$today = date('Y-m-d');

// Date range filter (defaults to today)
$fromDate = (!empty($_GET['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['from'])) ? $_GET['from'] : $today;
$toDate   = (!empty($_GET['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['to']))   ? $_GET['to']   : $today;
$isToday  = ($fromDate === $today && $toDate === $today);
$periodLabel = $isToday ? "Today's" : "Period";

$rangeStart = $fromDate . ' 00:00:00';
$rangeEnd   = $toDate . ' 23:59:59';
$whereUser = is_admin() ? '' : ' AND user_id = ' . intval(current_user()['id']);
$whereUser .= " AND COALESCE(status, 'paid') = 'paid'";
$whereToday = "created_at BETWEEN '$rangeStart' AND '$rangeEnd'";
$today_summary = $conn->query("SELECT COUNT(*) as invoice_count, COALESCE(SUM(total), 0) as total_sales, COALESCE(SUM(cash_paid), 0) as total_paid FROM invoices WHERE $whereToday $whereUser")->fetch_assoc();
$refund_summary = $conn->query("SELECT COUNT(*) as refund_count, COALESCE(SUM(ABS(total)), 0) as refund_total FROM invoices WHERE $whereToday AND payment_mode = 'Refund' $whereUser")->fetch_assoc();
$cash_summary  = $conn->query("SELECT COUNT(*) as invoice_count, COALESCE(SUM(total), 0) as total_sales FROM invoices WHERE $whereToday AND payment_mode = 'Cash' $whereUser")->fetch_assoc();
$knet_summary  = $conn->query("SELECT COUNT(*) as invoice_count, COALESCE(SUM(total), 0) as total_sales FROM invoices WHERE $whereToday AND payment_mode = 'KNET' $whereUser")->fetch_assoc();
$user_summary  = $conn->query("SELECT COALESCE(user_name, 'Unknown') as user_name, COUNT(*) as invoice_count, COALESCE(SUM(total), 0) as total_sales FROM invoices WHERE $whereToday $whereUser GROUP BY user_id, user_name ORDER BY total_sales DESC");
$latest        = $conn->query("SELECT * FROM invoices WHERE $whereToday $whereUser ORDER BY created_at DESC LIMIT 10");
$whereUserNoRefund = $whereUser . " AND payment_mode != 'Refund'";
$whereUserItems = $whereUser . " AND i.payment_mode != 'Refund'";

// Daily sales summary (per date breakdown by payment mode) for the selected range
$daily_summary_res = $conn->query("SELECT DATE(created_at) as sale_date,
        COUNT(*) as invoice_count,
        COALESCE(SUM(total), 0) as total_sales,
        COALESCE(SUM(CASE WHEN payment_mode = 'Cash'    THEN total ELSE 0 END), 0) as cash_total,
        COALESCE(SUM(CASE WHEN payment_mode = 'KNET'    THEN total ELSE 0 END), 0) as knet_total,
        COALESCE(SUM(CASE WHEN payment_mode = 'Talabat' THEN total ELSE 0 END), 0) as talabat_total,
        COALESCE(SUM(CASE WHEN payment_mode = 'Keeta'   THEN total ELSE 0 END), 0) as keeta_total
    FROM invoices WHERE $whereToday $whereUser
    GROUP BY DATE(created_at)
    ORDER BY sale_date DESC");
$daily_summary_arr = [];
if ($daily_summary_res) {
    while ($r = $daily_summary_res->fetch_assoc()) { $daily_summary_arr[] = $r; }
}
// Optional item filter
$itemFilter = isset($_GET['item']) ? trim($_GET['item']) : '';
$itemFilterSql = '';
if ($itemFilter !== '') {
    $safeItem = $conn->real_escape_string($itemFilter);
    // Match exact name AND size variants like "Pizza Fatayir (Small)"
    $likeItem = $conn->real_escape_string($itemFilter) . ' (%';
    $itemFilterSql = " AND (ii.item_name = '$safeItem' OR ii.item_name LIKE '$likeItem')";
}
$items_sold_res = $conn->query("SELECT ii.item_name, ii.item_name_ar, SUM(ii.quantity) as qty_sold, SUM(ii.subtotal) as revenue
    FROM invoice_items ii
    INNER JOIN invoices i ON i.id = ii.invoice_id
    WHERE i.$whereToday $whereUserItems $itemFilterSql
    GROUP BY ii.item_name, ii.item_name_ar
    ORDER BY qty_sold DESC");
$items_sold_arr = [];
$max_qty = 0;
if ($items_sold_res) {
    while ($r = $items_sold_res->fetch_assoc()) {
        $items_sold_arr[] = $r;
        if (intval($r['qty_sold']) > $max_qty) $max_qty = intval($r['qty_sold']);
    }
}

// List of all item names (for the dropdown)
$item_names = $conn->query("SELECT name_en FROM items WHERE is_active = 1 ORDER BY name_en");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Dashboard - <?php echo htmlspecialchars($company['company_name_en']); ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
html, body { min-height:100%; font-family:Tahoma,Arial,sans-serif; background:#f5f7fa; color:#2c3e50; font-size:14px; }
#header { background:linear-gradient(135deg, #8ab4f8, #7aa0e8); padding:10px 20px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
#header h1 { font-size:18px; color:#fff; }
#header a { color:#fff; text-decoration:none; background:rgba(255,255,255,0.2); padding:6px 16px; border-radius:4px; font-size:13px; border:1px solid rgba(255,255,255,0.3); margin-left:6px; }
#header a:hover { background:rgba(255,255,255,0.35); }
#content { padding:22px; max-width:1100px; margin:0 auto; }
.date-line { color:#7f8c8d; margin-bottom:18px; font-size:13px; }
.cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(190px,1fr)); gap:16px; margin-bottom:22px; }
.card { background:#fff; border:1px solid #dee2e6; border-radius:10px; padding:18px; box-shadow:0 1px 3px rgba(0,0,0,0.05); }
.card .label { color:#7f8c8d; font-size:12px; margin-bottom:8px; }
.card .value { color:#e67e22; font-size:28px; font-weight:bold; }
.card.cash .value { color:#27ae60; }
.card.knet .value { color:#3498db; }
.section { background:#fff; border:1px solid #dee2e6; border-radius:10px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.05); }
.section-title { background:#e9ecef; color:#495057; padding:10px 14px; font-weight:bold; }
/* ===== TOP MOVING ITEMS ===== */
.movers { padding:16px; display:flex; flex-direction:column; gap:12px; }
.mover-card { display:flex; align-items:center; gap:14px; padding:12px 14px; border:1px solid #eef0f3; border-radius:12px; background:linear-gradient(135deg,#ffffff,#fbfcfe); transition:all 0.2s; }
.mover-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.08); transform:translateX(3px); border-color:#dbe5fb; }
.mover-rank { font-size:26px; min-width:42px; text-align:center; font-weight:bold; color:#7f8c8d; }
.mover-body { flex:1; min-width:0; }
.mover-name { font-size:14px; font-weight:700; color:#2c3e50; margin-bottom:8px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.mover-bar-track { height:10px; background:#eef1f6; border-radius:6px; overflow:hidden; }
.mover-bar { height:100%; border-radius:6px; background:linear-gradient(90deg,#f39c12,#e67e22); transition:width 0.6s ease; }
.mover-stats { text-align:right; min-width:90px; }
.mover-qty { font-size:20px; font-weight:bold; color:#e67e22; line-height:1; }
.mover-qty span { font-size:11px; color:#95a5a6; font-weight:normal; }
.mover-rev { font-size:12px; color:#27ae60; font-weight:bold; margin-top:4px; }
@media (max-width:600px){ .mover-rank{font-size:20px;min-width:32px;} .mover-stats{min-width:70px;} }
table { width:100%; border-collapse:collapse; }
th { text-align:left; color:#495057; font-size:12px; padding:10px 12px; border-bottom:2px solid #dee2e6; background:#f8f9fa; }
td { padding:10px 12px; border-bottom:1px solid #e9ecef; font-size:13px; }
.amount { color:#e67e22; font-weight:bold; }
.badge { padding:3px 8px; border-radius:12px; background:#e9ecef; font-size:11px; }
.badge.cash { background:#d4edda; color:#155724; }
.badge.knet { background:#d1ecf1; color:#0c5460; }
.btn-view { background:#3498db; color:#fff; border:none; padding:4px 10px; border-radius:4px; text-decoration:none; display:inline-block; }
.btn-view:hover { background:#2980b9; }
.empty { padding:24px; color:#95a5a6; text-align:center; }
</style>
</head>
<body>
<div id="header">
  <h1>&#128200; Sales Dashboard</h1>
  <div>
    <a href="invoices.php">&#128196; Invoices</a>
    <a href="tables.php">&#127860; Tables</a>
    <a href="index.php">&#8592; Back to POS</a>
    <?php if (is_admin()): ?><a href="users.php">&#128101; Users</a><?php endif; ?>
    <a href="logout.php" onclick="showConfirm('Logout','Are you sure you want to logout?','Yes, Logout','\uD83D\uDEAA',function(){ window.location.href='logout.php'; }); return false;">Logout</a>
  </div>
</div>
<div id="content">
  <form method="GET" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px;margin-bottom:18px;background:#fff;padding:14px 16px;border:1px solid #dee2e6;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
    <div style="display:flex;flex-direction:column;gap:4px;">
      <label style="font-size:11px;color:#7f8c8d;font-weight:600;">From Date</label>
      <input type="date" name="from" value="<?php echo htmlspecialchars($fromDate); ?>" style="padding:8px 12px;border:1px solid #ced4da;border-radius:6px;font-size:14px;">
    </div>
    <div style="display:flex;flex-direction:column;gap:4px;">
      <label style="font-size:11px;color:#7f8c8d;font-weight:600;">To Date</label>
      <input type="date" name="to" value="<?php echo htmlspecialchars($toDate); ?>" style="padding:8px 12px;border:1px solid #ced4da;border-radius:6px;font-size:14px;">
    </div>
    <div style="display:flex;flex-direction:column;gap:4px;">
      <label style="font-size:11px;color:#7f8c8d;font-weight:600;">Item</label>
      <select name="item" style="padding:8px 12px;border:1px solid #ced4da;border-radius:6px;font-size:14px;min-width:160px;">
        <option value="">All Items</option>
        <?php if ($item_names): while ($itn = $item_names->fetch_assoc()): ?>
          <option value="<?php echo htmlspecialchars($itn['name_en']); ?>" <?php echo ($itemFilter === $itn['name_en']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($itn['name_en']); ?></option>
        <?php endwhile; endif; ?>
      </select>
    </div>
    <button type="submit" style="padding:9px 18px;background:linear-gradient(135deg,#8ab4f8,#7aa0e8);color:#fff;border:none;border-radius:6px;font-size:13px;font-weight:bold;cursor:pointer;">&#128269; Filter</button>
    <a href="dashboard.php" style="padding:9px 18px;background:#fff;color:#7f8c8d;border:1px solid #dee2e6;border-radius:6px;font-size:13px;text-decoration:none;">Today</a>
    <span style="margin-left:auto;color:#7f8c8d;font-size:13px;">
      <?php if ($isToday): ?>Showing: <b>Today (<?php echo htmlspecialchars(date('d/m/Y')); ?>)</b>
      <?php elseif ($fromDate === $toDate): ?>Showing: <b><?php echo htmlspecialchars(date('d/m/Y', strtotime($fromDate))); ?></b>
      <?php else: ?>Showing: <b><?php echo htmlspecialchars(date('d/m/Y', strtotime($fromDate))); ?> → <?php echo htmlspecialchars(date('d/m/Y', strtotime($toDate))); ?></b><?php endif; ?>
    </span>
  </form>
  <div class="cards">
    <div class="card"><div class="label"><?php echo $periodLabel; ?> Total Sale</div><div class="value"><?php echo number_format($today_summary['total_sales'], 3); ?> KD</div></div>
    <div class="card"><div class="label"><?php echo $periodLabel; ?> Invoices</div><div class="value"><?php echo intval($today_summary['invoice_count']); ?></div></div>
    <div class="card cash"><div class="label">Cash Sale</div><div class="value"><?php echo number_format($cash_summary['total_sales'], 3); ?> KD</div></div>
    <div class="card knet"><div class="label">KNET Sale</div><div class="value"><?php echo number_format($knet_summary['total_sales'], 3); ?> KD</div></div>
    <div class="card" style="border-left:4px solid #e74c3c;"><div class="label"><?php echo $periodLabel; ?> Refunds</div><div class="value" style="color:#e74c3c;"><?php echo intval($refund_summary['refund_count']); ?> <span style="font-size:14px;color:#7f8c8d;">(<?php echo number_format($refund_summary['refund_total'], 3); ?> KD)</span></div></div>
  </div>
  <!-- DAILY SALES SUMMARY REPORT (Print / PDF / Excel) -->
  <div class="section" style="margin-bottom:22px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
      <div class="section-title" style="margin:0;">&#128202; Daily Sales Summary <?php echo $isToday ? 'Today' : 'in Period'; ?></div>
      <?php if (count($daily_summary_arr) > 0): ?>
      <div style="display:flex;gap:8px;padding-right:10px;flex-wrap:wrap;">
        <button onclick="printDailyReport()" style="padding:7px 14px;background:linear-gradient(135deg,#8ab4f8,#7aa0e8);color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer;">&#128424; Print (A4)</button>
        <button onclick="printDailyThermal()" style="padding:7px 14px;background:linear-gradient(135deg,#34495e,#2c3e50);color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer;">&#129534; Print (80mm)</button>
        <button onclick="exportDailyPDF()" style="padding:7px 14px;background:linear-gradient(135deg,#e74c3c,#c0392b);color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer;">&#128196; PDF</button>
        <button onclick="exportDailyExcel()" style="padding:7px 14px;background:linear-gradient(135deg,#27ae60,#1e8449);color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer;">&#128202; Excel</button>
      </div>
      <?php endif; ?>
    </div>
    <?php if (count($daily_summary_arr) > 0): ?>
    <table id="daily-summary-table">
      <thead><tr><th>Date</th><th>Invoices</th><th>Cash</th><th>KNET</th><th>Talabat</th><th>Keeta</th><th>Total Sale</th></tr></thead>
      <tbody>
      <?php
        $sum_inv = 0; $sum_cash = 0; $sum_knet = 0; $sum_tal = 0; $sum_keeta = 0; $sum_total = 0;
        foreach ($daily_summary_arr as $d):
          $sum_inv   += intval($d['invoice_count']);
          $sum_cash  += floatval($d['cash_total']);
          $sum_knet  += floatval($d['knet_total']);
          $sum_tal   += floatval($d['talabat_total']);
          $sum_keeta += floatval($d['keeta_total']);
          $sum_total += floatval($d['total_sales']);
      ?>
        <tr>
          <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($d['sale_date']))); ?></td>
          <td><?php echo intval($d['invoice_count']); ?></td>
          <td><?php echo number_format($d['cash_total'], 3); ?></td>
          <td><?php echo number_format($d['knet_total'], 3); ?></td>
          <td><?php echo number_format($d['talabat_total'], 3); ?></td>
          <td><?php echo number_format($d['keeta_total'], 3); ?></td>
          <td class="amount"><?php echo number_format($d['total_sales'], 3); ?> KD</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr style="font-weight:bold;background:#f8f9fa;">
          <td>TOTAL</td>
          <td><?php echo $sum_inv; ?></td>
          <td><?php echo number_format($sum_cash, 3); ?></td>
          <td><?php echo number_format($sum_knet, 3); ?></td>
          <td><?php echo number_format($sum_tal, 3); ?></td>
          <td><?php echo number_format($sum_keeta, 3); ?></td>
          <td class="amount"><?php echo number_format($sum_total, 3); ?> KD</td>
        </tr>
      </tfoot>
    </table>
    <?php else: ?>
    <div class="empty">No sales in the selected period.</div>
    <?php endif; ?>
  </div>

  <div class="section">
    <div class="section-title"><?php echo $periodLabel; ?> Sales By User</div>
    <?php if ($user_summary && $user_summary->num_rows > 0): ?>
    <table>
      <thead><tr><th>User</th><th>Invoices</th><th>Total Sales</th></tr></thead>
      <tbody>
      <?php while ($u = $user_summary->fetch_assoc()): ?>
        <tr><td><?php echo htmlspecialchars($u['user_name']); ?></td><td><?php echo intval($u['invoice_count']); ?></td><td class="amount"><?php echo number_format($u['total_sales'], 3); ?> KD</td></tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="empty">No user sales today.</div>
    <?php endif; ?>
  </div>
  <?php if (count($items_sold_arr) > 0): ?>
  <!-- TOP MOVING ITEMS (visual) -->
  <div class="section" style="margin-top:18px;">
    <div class="section-title">&#128293; Top Moving Items <?php echo $isToday ? 'Today' : 'in Period'; ?></div>
    <div class="movers">
      <?php
        $medals = ['&#129351;', '&#129352;', '&#129353;'];
        $rank = 0;
        foreach (array_slice($items_sold_arr, 0, 5) as $mv):
            $pct = $max_qty > 0 ? round((intval($mv['qty_sold']) / $max_qty) * 100) : 0;
            $badge = isset($medals[$rank]) ? $medals[$rank] : ('#' . ($rank + 1));
      ?>
      <div class="mover-card">
        <div class="mover-rank"><?php echo $badge; ?></div>
        <div class="mover-body">
          <div class="mover-name"><?php echo htmlspecialchars($mv['item_name']); ?><?php if (!empty($mv['item_name_ar'])): ?> <span style="color:#95a5a6;direction:rtl;font-size:12px;">/ <?php echo htmlspecialchars($mv['item_name_ar']); ?></span><?php endif; ?></div>
          <div class="mover-bar-track"><div class="mover-bar" style="width:<?php echo $pct; ?>%;"></div></div>
        </div>
        <div class="mover-stats">
          <div class="mover-qty"><?php echo intval($mv['qty_sold']); ?> <span>sold</span></div>
          <div class="mover-rev"><?php echo number_format($mv['revenue'], 3); ?> KD</div>
        </div>
      </div>
      <?php $rank++; endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="section" style="margin-top:18px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
      <div class="section-title" style="margin:0;"><?php echo $itemFilter !== '' ? '"' . htmlspecialchars($itemFilter) . '" Sold' : 'Items Sold'; ?> <?php echo $isToday ? 'Today' : 'in Period'; ?></div>
      <?php if (count($items_sold_arr) > 0): ?>
      <div style="display:flex;gap:8px;">
        <button onclick="printItemsReport()" style="padding:7px 14px;background:linear-gradient(135deg,#8ab4f8,#7aa0e8);color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer;">&#128424; Print</button>
        <button onclick="exportItemsPDF()" style="padding:7px 14px;background:linear-gradient(135deg,#e74c3c,#c0392b);color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer;">&#128196; PDF</button>
        <button onclick="exportItemsExcel()" style="padding:7px 14px;background:linear-gradient(135deg,#27ae60,#1e8449);color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer;">&#128202; Excel</button>
      </div>
      <?php endif; ?>
    </div>
    <?php if (count($items_sold_arr) > 0): ?>
    <table id="items-sold-table">
      <thead><tr><th>Item</th><th>Qty Sold</th><th>Revenue</th></tr></thead>
      <tbody>
      <?php
        $grand_qty = 0; $grand_rev = 0;
        foreach ($items_sold_arr as $it):
          $grand_qty += intval($it['qty_sold']);
          $grand_rev += floatval($it['revenue']);
      ?>
        <tr>
          <td><?php echo htmlspecialchars($it['item_name']); ?><?php if (!empty($it['item_name_ar'])): ?> <span style="color:#95a5a6;direction:rtl;">/ <?php echo htmlspecialchars($it['item_name_ar']); ?></span><?php endif; ?></td>
          <td><b><?php echo intval($it['qty_sold']); ?></b></td>
          <td class="amount"><?php echo number_format($it['revenue'], 3); ?> KD</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr style="font-weight:bold;background:#f8f9fa;">
          <td>TOTAL</td>
          <td><?php echo $grand_qty; ?></td>
          <td class="amount"><?php echo number_format($grand_rev, 3); ?> KD</td>
        </tr>
      </tfoot>
    </table>
    <?php else: ?>
    <div class="empty">No items sold today.</div>
    <?php endif; ?>
  </div>
  <div class="section" style="margin-top:18px;">
    <div class="section-title">Latest Receipts <?php echo $isToday ? 'Today' : 'in Period'; ?></div>
    <?php if ($latest && $latest->num_rows > 0): ?>
    <table>
      <thead><tr><th>Invoice</th><th>Time</th><th>User</th><th>Payment</th><th>Total</th><th>Action</th></tr></thead>
      <tbody>
      <?php while ($row = $latest->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['invoice_number']); ?></td>
          <td><?php echo htmlspecialchars(date('H:i', strtotime($row['created_at']))); ?></td>
          <td><?php echo htmlspecialchars($row['user_name'] ?: 'Unknown'); ?></td>
          <td><span class="badge <?php echo strtolower($row['payment_mode']); ?>"><?php echo htmlspecialchars($row['payment_mode']); ?></span></td>
          <td class="amount"><?php echo number_format($row['total'], 3); ?> KD</td>
          <td><a class="btn-view" href="receipt.php?id=<?php echo intval($row['id']); ?>" target="_blank">Print</a></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
    <div class="empty">No sales saved today.</div>
    <?php endif; ?>
  </div>
</div>
<?php include 'includes/confirm_modal.php'; ?>
<script>
var REPORT_TITLE = <?php echo json_encode($itemFilter !== '' ? ('"' . $itemFilter . '" Sold') : 'Items Sold Report'); ?>;
var COMPANY_NAME = <?php echo json_encode($company['company_name_en']); ?>;
var COMPANY_NAME_AR = <?php echo json_encode($company['company_name_ar']); ?>;
var PERIOD_TEXT = <?php echo json_encode($isToday ? ('Today (' . date('d/m/Y') . ')') : (date('d/m/Y', strtotime($fromDate)) . ' to ' . date('d/m/Y', strtotime($toDate)))); ?>;
var COMPANY_PHONE = <?php echo json_encode($company['phone'] ?? ''); ?>;
var PRINTED_AT = <?php echo json_encode(date('d/m/Y H:i')); ?>;

function buildReportHTML() {
    var table = document.getElementById('items-sold-table');
    if (!table) return '';
    var html = '';
    html += '<div style="text-align:center;margin-bottom:10px;">';
    html += '<h2 style="margin:0;">' + COMPANY_NAME + '</h2>';
    html += '<div style="direction:rtl;color:#555;">' + COMPANY_NAME_AR + '</div>';
    html += '<h3 style="margin:8px 0 2px;">' + REPORT_TITLE + '</h3>';
    html += '<div style="color:#555;font-size:13px;">Period: ' + PERIOD_TEXT + '</div>';
    html += '</div>';
    html += '<table style="width:100%;border-collapse:collapse;font-size:13px;">' + table.innerHTML + '</table>';
    return html;
}

function printItemsReport() {
    var w = window.open('', '_blank');
    w.document.write('<html><head><title>' + REPORT_TITLE + '</title>');
    w.document.write('<style>body{font-family:Tahoma,Arial,sans-serif;padding:20px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ccc;padding:8px 10px;text-align:left;}thead th{background:#f0f0f0;}tfoot td{background:#f8f8f8;font-weight:bold;}</style>');
    w.document.write('</head><body>');
    w.document.write(buildReportHTML());
    w.document.write('</body></html>');
    w.document.close();
    w.focus();
    setTimeout(function(){ w.print(); }, 300);
}

function exportItemsPDF() {
    // Same as print - user chooses "Save as PDF" in the print dialog
    printItemsReport();
}

function exportItemsExcel() {
    var table = document.getElementById('items-sold-table');
    if (!table) return;
    var csv = 'Item,Qty Sold,Revenue (KD)\n';
    var rows = table.querySelectorAll('tbody tr');
    for (var i = 0; i < rows.length; i++) {
        var cells = rows[i].querySelectorAll('td');
        var name = cells[0].textContent.replace(/\s+/g, ' ').replace(/,/g, ';').trim();
        var qty = cells[1].textContent.trim();
        var rev = cells[2].textContent.replace(' KD', '').trim();
        csv += '"' + name + '",' + qty + ',' + rev + '\n';
    }
    // Total row
    var foot = table.querySelector('tfoot tr');
    if (foot) {
        var fc = foot.querySelectorAll('td');
        csv += 'TOTAL,' + fc[1].textContent.trim() + ',' + fc[2].textContent.replace(' KD', '').trim() + '\n';
    }
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'items_sold_<?php echo $fromDate; ?>_to_<?php echo $toDate; ?>.csv';
    link.click();
}

/* ===== DAILY SALES SUMMARY REPORT (Date / Cash / KNET / Talabat / Keeta / Total) ===== */
var DAILY_TITLE = 'Daily Sales Summary Report';

function buildDailyReportHTML() {
    var table = document.getElementById('daily-summary-table');
    if (!table) return '';
    var html = '';
    html += '<div style="text-align:center;margin-bottom:10px;">';
    html += '<h2 style="margin:0;">' + COMPANY_NAME + '</h2>';
    html += '<div style="direction:rtl;color:#555;">' + COMPANY_NAME_AR + '</div>';
    html += '<h3 style="margin:8px 0 2px;">' + DAILY_TITLE + '</h3>';
    html += '<div style="color:#555;font-size:13px;">Period: ' + PERIOD_TEXT + '</div>';
    html += '</div>';
    html += '<table style="width:100%;border-collapse:collapse;font-size:13px;">' + table.innerHTML + '</table>';
    return html;
}

function printDailyReport() {
    var w = window.open('', '_blank');
    w.document.write('<html><head><title>' + DAILY_TITLE + '</title>');
    w.document.write('<style>body{font-family:Tahoma,Arial,sans-serif;padding:20px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ccc;padding:8px 10px;text-align:left;}thead th{background:#f0f0f0;}tfoot td{background:#f8f8f8;font-weight:bold;}</style>');
    w.document.write('</head><body>');
    w.document.write(buildDailyReportHTML());
    w.document.write('</body></html>');
    w.document.close();
    w.focus();
    setTimeout(function(){ w.print(); }, 300);
}

function exportDailyPDF() {
    // Same as print - user chooses "Save as PDF" in the print dialog
    printDailyReport();
}

function exportDailyExcel() {
    var table = document.getElementById('daily-summary-table');
    if (!table) return;
    var csv = 'Date,Invoices,Cash (KD),KNET (KD),Talabat (KD),Keeta (KD),Total Sale (KD)\n';
    var rows = table.querySelectorAll('tbody tr');
    for (var i = 0; i < rows.length; i++) {
        var c = rows[i].querySelectorAll('td');
        csv += c[0].textContent.trim() + ',' +
               c[1].textContent.trim() + ',' +
               c[2].textContent.trim() + ',' +
               c[3].textContent.trim() + ',' +
               c[4].textContent.trim() + ',' +
               c[5].textContent.trim() + ',' +
               c[6].textContent.replace(' KD', '').trim() + '\n';
    }
    var foot = table.querySelector('tfoot tr');
    if (foot) {
        var fc = foot.querySelectorAll('td');
        csv += 'TOTAL,' + fc[1].textContent.trim() + ',' + fc[2].textContent.trim() + ',' +
               fc[3].textContent.trim() + ',' + fc[4].textContent.trim() + ',' +
               fc[5].textContent.trim() + ',' + fc[6].textContent.replace(' KD', '').trim() + '\n';
    }
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'daily_sales_<?php echo $fromDate; ?>_to_<?php echo $toDate; ?>.csv';
    link.click();
}

/* ===== THERMAL (80mm) PRINT for Daily Sales Summary ===== */
function printDailyThermal() {
    var table = document.getElementById('daily-summary-table');
    if (!table) return;

    function line(label, value, bold) {
        return '<div class="t-row' + (bold ? ' bold' : '') + '"><span>' + label + '</span><span>' + value + '</span></div>';
    }

    var body = '';
    var rows = table.querySelectorAll('tbody tr');
    for (var i = 0; i < rows.length; i++) {
        var c = rows[i].querySelectorAll('td');
        body += '<div class="day-block">';
        body += '<div class="day-date">' + c[0].textContent.trim() + '</div>';
        body += line('Invoices', c[1].textContent.trim(), false);
        body += line('Cash', c[2].textContent.trim(), false);
        body += line('KNET', c[3].textContent.trim(), false);
        body += line('Talabat', c[4].textContent.trim(), false);
        body += line('Keeta', c[5].textContent.trim(), false);
        body += line('Total', c[6].textContent.replace('KD', '').trim() + ' KD', true);
        body += '</div>';
    }

    var grand = '';
    var foot = table.querySelector('tfoot tr');
    if (foot) {
        var fc = foot.querySelectorAll('td');
        grand += '<div class="grand-block">';
        grand += '<div class="day-date">GRAND TOTAL</div>';
        grand += line('Invoices', fc[1].textContent.trim(), false);
        grand += line('Cash', fc[2].textContent.trim(), false);
        grand += line('KNET', fc[3].textContent.trim(), false);
        grand += line('Talabat', fc[4].textContent.trim(), false);
        grand += line('Keeta', fc[5].textContent.trim(), false);
        grand += line('TOTAL', fc[6].textContent.replace('KD', '').trim() + ' KD', true);
        grand += '</div>';
    }

    var w = window.open('', '_blank');
    w.document.write('<html><head><title>Daily Sales Summary</title>');
    w.document.write('<style>'
        + '@page { size:80mm auto; margin:0; }'
        + '* { margin:0; padding:0; box-sizing:border-box; }'
        + 'body { width:80mm; font-family:"Courier New",Courier,monospace; color:#000; padding:4mm 3mm; }'
        + '.header { text-align:center; border-bottom:2px dashed #000; padding-bottom:6px; margin-bottom:6px; }'
        + '.header .name { font-size:15px; font-weight:bold; }'
        + '.header .name-ar { font-size:13px; direction:rtl; font-weight:bold; }'
        + '.header .sub { font-size:11px; margin-top:3px; }'
        + '.title { text-align:center; font-size:13px; font-weight:bold; margin:6px 0 2px; }'
        + '.period { text-align:center; font-size:11px; margin-bottom:8px; }'
        + '.day-block { border-bottom:1px dashed #999; padding:4px 0 6px; margin-bottom:4px; }'
        + '.grand-block { border-top:2px dashed #000; padding-top:6px; margin-top:4px; }'
        + '.day-date { font-size:12px; font-weight:bold; margin-bottom:3px; }'
        + '.t-row { display:flex; justify-content:space-between; font-size:12px; padding:2px 0; }'
        + '.t-row.bold { font-weight:bold; font-size:13px; }'
        + '.footer { text-align:center; border-top:2px dashed #000; margin-top:8px; padding-top:6px; font-size:10px; }'
        + '</style>');
    w.document.write('</head><body>');
    w.document.write('<div class="header"><div class="name">' + COMPANY_NAME + '</div>'
        + '<div class="name-ar">' + COMPANY_NAME_AR + '</div>'
        + (COMPANY_PHONE ? '<div class="sub">Tel: ' + COMPANY_PHONE + '</div>' : '') + '</div>');
    w.document.write('<div class="title">Daily Sales Summary</div>');
    w.document.write('<div class="period">' + PERIOD_TEXT + '</div>');
    w.document.write(body);
    w.document.write(grand);
    w.document.write('<div class="footer">Printed: ' + PRINTED_AT + '</div>');
    w.document.write('</body></html>');
    w.document.close();
    w.focus();
    setTimeout(function(){ w.print(); }, 300);
}
</script>
</body>
</html>
<?php $conn->close(); ?>
