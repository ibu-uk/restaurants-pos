<?php
require_once 'auth.php';
require_login();
require_once 'db/connect.php';

// Handle company settings save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_company') {
    $company_name_en = trim($_POST['company_name_en'] ?? '');
    $company_name_ar = trim($_POST['company_name_ar'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $invoice_footer = trim($_POST['invoice_footer'] ?? '');
    $logo_on_receipt = isset($_POST['logo_on_receipt']) ? 1 : 0;
    
    // Handle logo upload or removal
    $logo_path = $_POST['existing_logo'] ?? '';
    if (isset($_POST['remove_logo']) && $_POST['remove_logo'] === '1') {
        // Delete existing logo file
        if ($logo_path && file_exists($logo_path)) {
            unlink($logo_path);
        }
        $logo_path = '';
    } elseif (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        // Upload new logo
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['logo']['size'] <= 2097152) {
            // Delete old logo file if exists
            if ($logo_path && file_exists($logo_path)) {
                unlink($logo_path);
            }
            $new_name = 'company_logo.' . $ext;
            move_uploaded_file($_FILES['logo']['tmp_name'], 'uploads/' . $new_name);
            $logo_path = 'uploads/' . $new_name;
        }
    }
    
    // Check if company settings already exists
    $exists = $conn->query("SELECT id FROM company_settings WHERE id = 1")->fetch_assoc();
    
    if ($exists) {
        $stmt = $conn->prepare("UPDATE company_settings SET company_name_en = ?, company_name_ar = ?, address = ?, phone = ?, email = ?, logo_path = ?, invoice_footer = ?, logo_on_receipt = ? WHERE id = 1");
        $stmt->bind_param('sssssssi', $company_name_en, $company_name_ar, $address, $phone, $email, $logo_path, $invoice_footer, $logo_on_receipt);
    } else {
        $stmt = $conn->prepare("INSERT INTO company_settings (id, company_name_en, company_name_ar, address, phone, email, logo_path, invoice_footer, logo_on_receipt) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssssi', $company_name_en, $company_name_ar, $address, $phone, $email, $logo_path, $invoice_footer, $logo_on_receipt);
    }
    $stmt->execute();
    if ($stmt->error) {
        echo "Error: " . $stmt->error;
        exit;
    }
    $stmt->close();
    
    header('Location: settings.php?saved=1');
    exit;
}

// Get current company settings
$company = $conn->query("SELECT * FROM company_settings WHERE id = 1")->fetch_assoc();
if (!$company) {
    $company = [
        'company_name_en' => 'BURGE AL SALHIYA',
        'company_name_ar' => 'برج الصالحية',
        'address' => '',
        'phone' => '',
        'email' => '',
        'logo_path' => '',
        'invoice_footer' => 'Thank you for your visit!',
        'logo_on_receipt' => 1
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>Settings - <?php echo htmlspecialchars($company['company_name_en']); ?></title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
html, body { font-family:Tahoma,Arial,sans-serif; background:#f5f7fa; color:#2c3e50; font-size:14px; min-height:100%; }

#header {
    background:linear-gradient(135deg, #8ab4f8, #7aa0e8);
    padding:10px 20px; display:flex; align-items:center; justify-content:space-between;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}
#header h1 { font-size:18px; color:#fff; }
#header a { color:#fff; text-decoration:none; background:rgba(255,255,255,0.2); padding:6px 16px; border-radius:4px; font-size:13px; border:1px solid rgba(255,255,255,0.3); }

#content { padding:24px; max-width:1100px; margin:0 auto; }
.info-box {
    background:linear-gradient(135deg, #e8f4fd, #f0f7ff); border:1px solid #d0e3f0;
    border-radius:10px; padding:14px 18px; margin-bottom:20px; color:#2c5282; font-size:13px;
    display:flex; align-items:center; gap:10px; box-shadow:0 2px 6px rgba(0,0,0,0.04);
}
.info-box::before { content:'&#9432;'; font-size:18px; }
.add-box {
    background:#fff; border:1px solid #e8eaed; border-radius:12px; padding:18px;
    margin-bottom:18px; display:grid; grid-template-columns:1fr 1fr 1fr 120px 100px;
    gap:12px; align-items:end; box-shadow:0 2px 8px rgba(0,0,0,0.06);
    transition:box-shadow 0.2s;
}
.add-box:hover { box-shadow:0 4px 16px rgba(0,0,0,0.1); }
.add-box label {
    display:block; font-size:11px; color:#5a6c7d; font-weight:600;
    margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;
}
.add-box input, .add-box select, .name-input {
    width:100%; padding:10px 12px; background:#fafbfc; border:1px solid #dfe4ea;
    color:#2c3e50; border-radius:8px; font-size:13px; font-family:Tahoma,Arial,sans-serif;
    transition:all 0.2s;
}
.add-box input:focus, .add-box select:focus, .name-input:focus {
    outline:none; border-color:#8ab4f8; background:#fff; box-shadow:0 0 0 3px rgba(138,180,248,0.1);
}
.name-input {
    width:100%; padding:8px 10px; background:#fafbfc; border:1px solid #dfe4ea;
    color:#2c3e50; border-radius:8px; font-size:13px; font-family:Tahoma,Arial,sans-serif;
    transition:all 0.2s;
}
.name-input:focus {
    outline:none; border-color:#8ab4f8; background:#fff; box-shadow:0 0 0 3px rgba(138,180,248,0.1);
}

.cat-section { margin-bottom:28px; }
.cat-title {
    background:linear-gradient(135deg, #8ab4f8, #7aa0e8);
    color:#fff; padding:12px 18px; border-radius:10px 10px 0 0;
    font-size:15px; font-weight:600; display:flex; align-items:center; gap:10px;
    box-shadow:0 2px 6px rgba(138,180,248,0.2);
}
.cat-title .cat-ar { font-size:13px; opacity:0.95; direction:rtl; }
.cat-img-preview { width:38px; height:38px; border-radius:8px; object-fit:cover; background:#f8f9fa; border:2px solid #e8eaed; flex-shrink:0; }
.cat-file { width:150px; font-size:11px; color:#7f8c8d; }

table { width:100%; border-collapse:collapse; background:#fff; border-radius:0 0 10px 10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
thead th { background:#f8f9fa; padding:12px 14px; text-align:left; font-size:12px; color:#5a6c7d; font-weight:600; border-bottom:2px solid #e8eaed; }
tbody tr { border-bottom:1px solid #f0f2f5; transition:background 0.15s; }
tbody tr:hover { background:#f8f9fa; }
tbody td { padding:10px 14px; font-size:13px; }
.td-ar { color:#7f8c8d; direction:rtl; font-size:13px; }

.price-input {
    width:90px; padding:8px 10px; background:#fafbfc; border:1px solid #dfe4ea;
    color:#e67e22; border-radius:8px; font-size:14px; font-family:Tahoma,Arial,sans-serif;
    text-align:right; font-weight:bold; transition:all 0.2s;
}
.price-input:focus { outline:none; border-color:#8ab4f8; background:#fff; box-shadow:0 0 0 3px rgba(138,180,248,0.1); }
.name-input.ar { direction:rtl; }

.save-btn {
    padding:8px 16px; background:linear-gradient(135deg,#27ae60,#219a52); color:#fff; border:none;
    border-radius:8px; cursor:pointer; font-size:12px; font-weight:600;
    font-family:Tahoma,Arial,sans-serif; transition:all 0.2s; box-shadow:0 2px 6px rgba(39,174,96,0.2);
}
.save-btn:hover { background:linear-gradient(135deg,#2ecc71,#27ae60); transform:translateY(-1px); box-shadow:0 4px 10px rgba(39,174,96,0.3); }
.save-btn.saving { background:linear-gradient(135deg,#e67e22,#d35400); }
.save-btn.saved  { background:linear-gradient(135deg,#1a8a3a,#16a085); }
.del-btn {
    padding:8px 14px; background:linear-gradient(135deg,#e74c3c,#c0392b); color:#fff; border:none;
    border-radius:8px; cursor:pointer; font-size:12px; font-weight:600;
    font-family:Tahoma,Arial,sans-serif; transition:all 0.2s; box-shadow:0 2px 6px rgba(231,76,60,0.2);
}
.del-btn:hover { background:linear-gradient(135deg,#c0392b,#a93226); transform:translateY(-1px); box-shadow:0 4px 10px rgba(231,76,60,0.3); }

.pizza-prices { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
.pizza-prices label { font-size:11px; color:#7f8c8d; }

#toast {
    position:fixed; bottom:20px; left:50%; transform:translateX(-50%) translateY(60px);
    background:#27ae60; color:#fff; padding:10px 24px; border-radius:8px;
    font-size:14px; font-weight:bold; z-index:9999; opacity:0; transition:all 0.3s; box-shadow:0 4px 12px rgba(0,0,0,0.15);
}
#toast.show { opacity:1; transform:translateX(-50%) translateY(0); }
#toast.error { background:#e74c3c; }

/* ===== COMPANY PROFILE SECTION ===== */
.company-section {
    background:#fff; border:1px solid #e8eaed; border-radius:12px; padding:24px;
    margin-bottom:24px; box-shadow:0 2px 8px rgba(0,0,0,0.06); transition:box-shadow 0.2s;
}
.company-section:hover { box-shadow:0 4px 16px rgba(0,0,0,0.1); }
.company-section h2 {
    color:#8ab4f8; font-size:18px; margin-bottom:20px; border-bottom:2px solid #f0f2f5;
    padding-bottom:12px; font-weight:600; display:flex; align-items:center; gap:8px;
}
.company-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.company-grid.full { grid-template-columns:1fr; }
.company-grid > div { display:flex; flex-direction:column; }
.company-grid label {
    color:#5a6c7d; font-size:12px; font-weight:600; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.5px;
}
.company-grid input {
    padding:10px 12px; border:1px solid #dfe4ea; border-radius:8px;
    font-size:14px; background:#fafbfc; transition:all 0.2s;
}
.company-grid input:focus {
    outline:none; border-color:#8ab4f8; background:#fff; box-shadow:0 0 0 3px rgba(138,180,248,0.1);
}
.logo-preview {
    width:100px; height:100px; border:2px dashed #dfe4ea; border-radius:10px;
    object-fit:contain; background:#fafbfc; display:block; margin-top:8px; padding:8px;
}
.logo-upload { margin-top:8px; }
.company-section button[type="submit"] {
    background:linear-gradient(135deg,#8ab4f8,#7aa0e8); color:#fff; border:none;
    padding:12px 28px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;
    transition:all 0.2s; box-shadow:0 2px 8px rgba(138,180,248,0.3);
}
.company-section button[type="submit"]:hover {
    background:linear-gradient(135deg,#7aa0e8,#6a90d8); transform:translateY(-1px);
    box-shadow:0 4px 12px rgba(138,180,248,0.4);
}

::-webkit-scrollbar { width:6px; } ::-webkit-scrollbar-track { background:#f1f1f1; } ::-webkit-scrollbar-thumb { background:#bdc3c7; border-radius:3px; }

</style>
</head>
<body>
<div id="header">
  <h1>&#9881; Settings</h1>
  <div>
    <?php if (is_admin()): ?><a href="users.php">&#128101; Users</a><?php endif; ?>
    <a href="index.php">&#8592; Back to POS</a>
    <a href="logout.php" onclick="showConfirm('Logout','Are you sure you want to logout?','Yes, Logout','\uD83D\uDEAA',function(){ window.location.href='logout.php'; }); return false;">Logout</a>
  </div>
</div>
<div id="content">
  <?php if (isset($_GET['saved'])): ?>
    <div class="info-box" style="background:#d4edda; color:#155724; border-color:#c3e6cb;">&#10004; Company settings saved successfully!</div>
  <?php endif; ?>

  <?php if (is_admin()): ?>
  <!-- Company Profile Section (Admin Only) -->
  <div class="company-section">
    <h2>&#127970; Company Profile</h2>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="save_company">
      <input type="hidden" name="existing_logo" value="<?php echo htmlspecialchars($company['logo_path']); ?>">
      <div class="company-grid">
        <div>
          <label>Company Name (English)</label>
          <input type="text" name="company_name_en" value="<?php echo htmlspecialchars($company['company_name_en']); ?>" required>
        </div>
        <div>
          <label>Company Name (Arabic)</label>
          <input type="text" name="company_name_ar" value="<?php echo htmlspecialchars($company['company_name_ar']); ?>" dir="rtl" required>
        </div>
      </div>
      <div class="company-grid">
        <div>
          <label>Address</label>
          <input type="text" name="address" value="<?php echo htmlspecialchars($company['address']); ?>">
        </div>
        <div>
          <label>Phone</label>
          <input type="text" name="phone" value="<?php echo htmlspecialchars($company['phone']); ?>">
        </div>
      </div>
      <div class="company-grid">
        <div>
          <label>Email</label>
          <input type="email" name="email" value="<?php echo htmlspecialchars($company['email']); ?>">
        </div>
        <div>
          <label>Logo</label>
          <input type="file" name="logo" accept="image/*">
          <?php if ($company['logo_path']): ?>
            <img src="<?php echo htmlspecialchars($company['logo_path']); ?>" class="logo-preview" alt="Logo">
            <div style="margin-top:8px;">
              <label style="font-size:12px;color:#7f8c8d;font-weight:normal;">
                <input type="checkbox" name="remove_logo" value="1"> Remove current logo
              </label>
            </div>
            <div style="margin-top:6px;">
              <label style="font-size:12px;color:#7f8c8d;font-weight:normal;">
                <input type="checkbox" name="logo_on_receipt" value="1" <?php echo (intval($company['logo_on_receipt'] ?? 1) === 1) ? 'checked' : ''; ?>> Show logo on receipt
              </label>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="company-grid full">
        <div>
          <label>Invoice Footer Text</label>
          <input type="text" name="invoice_footer" value="<?php echo htmlspecialchars($company['invoice_footer']); ?>">
        </div>
      </div>
      <button type="submit" class="save-btn" style="margin-top:14px; padding:10px 24px; font-size:14px;">Save Company Profile</button>
    </form>
  </div>
  <?php endif; ?>

  <!-- Menu Settings Section -->
  <div class="add-box">
    <div><label>Parent Category</label><select id="new-cat-parent"></select></div>
    <div><label>Category Name EN</label><input type="text" id="new-cat-name-en"></div>
    <div><label>Category Name AR</label><input type="text" id="new-cat-name-ar" dir="rtl"></div>
    <div></div>
    <button class="save-btn" onclick="addCategory()">Add Category</button>
  </div>
  <div class="add-box">
    <div><label>Category</label><select id="new-category"></select></div>
    <div><label>Item Name EN</label><input type="text" id="new-name-en"></div>
    <div><label>Item Name AR</label><input type="text" id="new-name-ar" dir="rtl"></div>
    <div>
      <label>Price Type</label>
      <select id="new-price-type" onchange="togglePriceType()">
        <option value="single">Single Price</option>
        <option value="size">Size-based (S/M/L)</option>
      </select>
    </div>
    <div id="single-price-field"><label>Price KD</label><input type="number" id="new-price" step="0.001" min="0"></div>
    <div id="size-price-fields" style="display:none;"><label>Small KD</label><input type="number" id="new-price-small" step="0.001" min="0"></div>
    <div id="size-price-fields-m" style="display:none;"><label>Medium KD</label><input type="number" id="new-price-medium" step="0.001" min="0"></div>
    <div id="size-price-fields-l" style="display:none;"><label>Large KD</label><input type="number" id="new-price-large" step="0.001" min="0"></div>
    <button class="save-btn" onclick="addItem()">Add Item</button>
  </div>
  <div id="menu-settings">Loading menu...</div>
</div>
<div id="toast"></div>
<?php include 'includes/confirm_modal.php'; ?>

<script>
var menuData = [];

function loadMenu() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'api/menu.php?settings=1', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            menuData = JSON.parse(xhr.responseText);
            renderCategorySelect();
            renderSettings();
        }
    };
    xhr.send();
}

function esc(v) {
    return String(v == null ? '' : v).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function renderCategorySelect() {
    var html = '';
    var cats = flattenCategories();
    for (var i = 0; i < cats.length; i++) {
        html += '<option value="' + cats[i].id + '">' + esc(cats[i].label) + '</option>';
    }
    document.getElementById('new-category').innerHTML = html;
    document.getElementById('new-cat-parent').innerHTML = '<option value="">Main Category</option>' + html;
}

function flattenCategories(list, prefix, out) {
    list = list || menuData;
    prefix = prefix || '';
    out = out || [];
    for (var i = 0; i < list.length; i++) {
        out.push({id: list[i].id, parent_id: list[i].parent_id, label: prefix + list[i].name_en + ' - ' + list[i].name_ar});
        flattenCategories(list[i].children || [], prefix + '-- ', out);
    }
    return out;
}

function categoryOptions(selectedId, excludeId) {
    var cats = flattenCategories();
    var html = '<option value="">Main Category</option>';
    for (var i = 0; i < cats.length; i++) {
        if (String(cats[i].id) === String(excludeId)) continue;
        html += '<option value="' + cats[i].id + '"' + (String(cats[i].id) === String(selectedId) ? ' selected' : '') + '>' + esc(cats[i].label) + '</option>';
    }
    return html;
}

function renderSettings() {
    var html = '';
    for (var c = 0; c < menuData.length; c++) {
        html += renderCategorySection(menuData[c], 0);
    }
    document.getElementById('menu-settings').innerHTML = html;
}

function renderCategorySection(cat, level) {
        var html = '';
        html += '<div class="cat-section" style="margin-left:' + (level * 22) + 'px">';
        html += '<div class="cat-title">';
        html += cat.image_path ? '<img class="cat-img-preview" src="' + esc(cat.image_path) + '">' : '<div class="cat-img-preview"></div>';
        html += '<input class="name-input" type="text" value="' + esc(cat.name_en) + '" id="cne_' + cat.id + '">';
        html += '<input class="name-input ar" type="text" value="' + esc(cat.name_ar) + '" id="cna_' + cat.id + '">';
        html += '<select class="name-input" id="cparent_' + cat.id + '">' + categoryOptions(cat.parent_id, cat.id) + '</select>';
        html += '<input class="cat-file" type="file" accept="image/*" id="cimg_' + cat.id + '">';
        html += '<button class="save-btn" onclick="uploadCategoryImage(' + cat.id + ')">Upload Image</button>';
        html += '<button class="save-btn" id="cbtn-' + cat.id + '" onclick="saveCategory(' + cat.id + ')">Save Category</button>';
        html += '<button class="del-btn" onclick="deleteCategory(' + cat.id + ', \'' + esc(cat.name_en) + '\')" title="Delete Category">&#128465; Delete</button>';
        html += '</div>';
        html += '<table><thead><tr><th>Image</th><th>Category</th><th>Item (EN)</th><th>Item (AR)</th>';
        
        // Check if ANY item in this category has size-based pricing
        var hasSizes = false;
        for (var hs = 0; hs < cat.items.length; hs++) {
            if (cat.items[hs].price_small !== null) { hasSizes = true; break; }
        }
        if (hasSizes) {
            html += '<th>Small Price</th><th>Medium Price</th><th>Large Price</th><th>Action</th>';
        } else {
            html += '<th>Price (KD)</th><th>Action</th>';
        }
        html += '</tr></thead><tbody>';

        for (var i = 0; i < cat.items.length; i++) {
            var item = cat.items[i];
            html += '<tr id="row-' + item.id + '">';
            html += '<td style="text-align:center;width:90px;vertical-align:middle">';
            if (item.image_path) {
                html += '<img src="' + esc(item.image_path) + '" style="width:50px;height:50px;object-fit:contain;border-radius:6px;border:1px solid #e8eaed;display:block;margin:0 auto 4px;">';
            } else {
                html += '<div style="width:50px;height:50px;border:1px dashed #dfe4ea;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;color:#bdc3c7;font-size:10px;margin:0 auto 4px;">No img</div>';
            }
            html += '<input type="file" accept="image/*" id="iimg_' + item.id + '" style="width:80px;font-size:11px;" onchange="uploadItemImage(' + item.id + ')">';
            html += '</td>';
            html += '<td><select class="name-input" id="ic_' + item.id + '">' + categoryOptions(item.category_id, '') + '</select></td>';
            html += '<td><input class="name-input" type="text" value="' + esc(item.name_en) + '" id="ne_' + item.id + '"></td>';
            html += '<td class="td-ar"><input class="name-input ar" type="text" value="' + esc(item.name_ar) + '" id="na_' + item.id + '"></td>';

            if (item.price_small !== null) {
                // Size-based pricing
                html += '<td><input class="price-input" type="number" step="0.001" min="0" value="' + parseFloat(item.price_small).toFixed(3) + '" id="ps_' + item.id + '"></td>';
                html += '<td><input class="price-input" type="number" step="0.001" min="0" value="' + parseFloat(item.price_medium).toFixed(3) + '" id="pm_' + item.id + '"></td>';
                html += '<td><input class="price-input" type="number" step="0.001" min="0" value="' + parseFloat(item.price_large).toFixed(3) + '" id="pl_' + item.id + '"></td>';
                html += '<td style="white-space:nowrap"><button class="save-btn" id="sbtn-' + item.id + '" onclick="saveSizePrices(' + item.id + ')">Save</button> <button class="del-btn" onclick="deleteItem(' + item.id + ', \'' + esc(item.name_en) + '\')" title="Delete Item">&#128465;</button></td>';
            } else if (hasSizes) {
                // Single price item inside a mixed (size) category - span across the 3 size columns
                html += '<td colspan="3"><input class="price-input" type="number" step="0.001" min="0" value="' + parseFloat(item.price).toFixed(3) + '" id="p_' + item.id + '"></td>';
                html += '<td style="white-space:nowrap"><button class="save-btn" id="sbtn-' + item.id + '" onclick="saveItem(' + item.id + ')">Save</button> <button class="save-btn" style="background:#e67e22;" onclick="convertToSizeBased(' + item.id + ', ' + parseFloat(item.price).toFixed(3) + ')">Convert to Size-based</button> <button class="del-btn" onclick="deleteItem(' + item.id + ', \'' + esc(item.name_en) + '\')" title="Delete Item">&#128465;</button></td>';
            } else {
                // Single price
                html += '<td><input class="price-input" type="number" step="0.001" min="0" value="' + parseFloat(item.price).toFixed(3) + '" id="p_' + item.id + '"></td>';
                html += '<td style="white-space:nowrap"><button class="save-btn" id="sbtn-' + item.id + '" onclick="saveItem(' + item.id + ')">Save</button> <button class="save-btn" style="background:#e67e22;" onclick="convertToSizeBased(' + item.id + ', ' + parseFloat(item.price).toFixed(3) + ')">Convert to Size-based</button> <button class="del-btn" onclick="deleteItem(' + item.id + ', \'' + esc(item.name_en) + '\')" title="Delete Item">&#128465;</button></td>';
            }
            html += '</tr>';
        }
        html += '</tbody></table></div>';
        for (var k = 0; k < (cat.children || []).length; k++) {
            html += renderCategorySection(cat.children[k], level + 1);
        }
        return html;
}

function savePrice(id) {
    var val = parseFloat(document.getElementById('p_' + id).value);
    if (isNaN(val) || val < 0) { showToast('Invalid price value', true); return; }
    doSave(id, {id: id, field: 'price', value: val});
}

function saveItem(id) {
    var categoryId = parseInt(document.getElementById('ic_' + id).value, 10);
    var nameEn = document.getElementById('ne_' + id).value.trim();
    var nameAr = document.getElementById('na_' + id).value.trim();
    var val = parseFloat(document.getElementById('p_' + id).value);
    if (!categoryId || nameEn === '' || nameAr === '') { showToast('Category and item names are required', true); return; }
    if (isNaN(val) || val < 0) { showToast('Invalid price value', true); return; }
    doSave(id, {action: 'update_item', id: id, category_id: categoryId, name_en: nameEn, name_ar: nameAr, price: val});
}

function saveCategory(id) {
    var nameEn = document.getElementById('cne_' + id).value.trim();
    var nameAr = document.getElementById('cna_' + id).value.trim();
    var parentId = document.getElementById('cparent_' + id).value;
    var btn = document.getElementById('cbtn-' + id);
    if (nameEn === '' || nameAr === '') { showToast('Category names are required', true); return; }
    btn.textContent = 'Saving...'; btn.className = 'save-btn saving'; btn.disabled = true;
    sendUpdate({action: 'update_category', id: id, parent_id: parentId, name_en: nameEn, name_ar: nameAr}, function(ok) {
        if (ok) {
            btn.textContent = '✓ Saved'; btn.className = 'save-btn saved';
            showToast('Category updated!');
            loadMenu();
        } else {
            btn.textContent = 'Error!'; btn.className = 'save-btn';
            showToast('Failed to save category', true);
        }
        setTimeout(function() { btn.textContent = 'Save Category'; btn.className = 'save-btn'; btn.disabled = false; }, 1500);
    });
}

function addCategory() {
    var parentId = document.getElementById('new-cat-parent').value;
    var nameEn = document.getElementById('new-cat-name-en').value.trim();
    var nameAr = document.getElementById('new-cat-name-ar').value.trim();
    if (nameEn === '' || nameAr === '') { showToast('Category names are required', true); return; }
    sendUpdate({action: 'add_category', parent_id: parentId, name_en: nameEn, name_ar: nameAr}, function(ok) {
        if (ok) {
            document.getElementById('new-cat-name-en').value = '';
            document.getElementById('new-cat-name-ar').value = '';
            document.getElementById('new-cat-parent').value = '';
            showToast('Category added successfully!');
            loadMenu();
        } else {
            showToast('Failed to add category', true);
        }
    });
}

function uploadCategoryImage(id) {
    var input = document.getElementById('cimg_' + id);
    if (!input.files || !input.files[0]) { showToast('Please choose an image first', true); return; }
    var fd = new FormData();
    fd.append('category_id', id);
    fd.append('image', input.files[0]);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/upload_category_image.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success === true) {
                    showToast('Category image uploaded!');
                    loadMenu();
                } else {
                    showToast(res.error || 'Failed to upload image', true);
                }
            } catch(e) {
                showToast('Failed to upload image', true);
            }
        }
    };
    xhr.send(fd);
}

function uploadItemImage(id) {
    var input = document.getElementById('iimg_' + id);
    if (!input.files || !input.files[0]) { showToast('Please choose an image first', true); return; }
    var fd = new FormData();
    fd.append('item_id', id);
    fd.append('image', input.files[0]);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', window.location.pathname.replace(/\/[^\/]*$/, '') + '/api/upload_item_image.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (res.success === true) {
                    showToast('Item image uploaded!');
                    loadMenu();
                } else {
                    showToast(res.error || 'Failed to upload image', true);
                }
            } catch(e) {
                showToast('Failed to upload image', true);
            }
        }
    };
    xhr.send(fd);
}

function togglePriceType() {
    var type = document.getElementById('new-price-type').value;
    var isSize = type === 'size';
    document.getElementById('single-price-field').style.display = isSize ? 'none' : '';
    document.getElementById('size-price-fields').style.display = isSize ? '' : 'none';
    document.getElementById('size-price-fields-m').style.display = isSize ? '' : 'none';
    document.getElementById('size-price-fields-l').style.display = isSize ? '' : 'none';
}

function addItem() {
    var categoryId = parseInt(document.getElementById('new-category').value, 10);
    var nameEn = document.getElementById('new-name-en').value.trim();
    var nameAr = document.getElementById('new-name-ar').value.trim();
    var priceType = document.getElementById('new-price-type').value;
    if (!categoryId || nameEn === '' || nameAr === '') { showToast('Category and names are required', true); return; }

    if (priceType === 'size') {
        var s = parseFloat(document.getElementById('new-price-small').value);
        var m = parseFloat(document.getElementById('new-price-medium').value);
        var l = parseFloat(document.getElementById('new-price-large').value);
        if (isNaN(s) || isNaN(m) || isNaN(l) || s < 0 || m < 0 || l < 0) { showToast('Please enter all three size prices', true); return; }
        sendUpdate({action: 'add_size_item', category_id: categoryId, name_en: nameEn, name_ar: nameAr, price_small: s, price_medium: m, price_large: l}, function(ok) {
            if (ok) {
                document.getElementById('new-name-en').value = '';
                document.getElementById('new-name-ar').value = '';
                document.getElementById('new-price-small').value = '';
                document.getElementById('new-price-medium').value = '';
                document.getElementById('new-price-large').value = '';
                showToast('Size-based item added successfully!');
                loadMenu();
            } else {
                showToast('Failed to add item', true);
            }
        });
    } else {
        var price = parseFloat(document.getElementById('new-price').value);
        if (isNaN(price) || price < 0) { showToast('Invalid price value', true); return; }
        sendUpdate({action: 'add_item', category_id: categoryId, name_en: nameEn, name_ar: nameAr, price: price}, function(ok) {
            if (ok) {
                document.getElementById('new-name-en').value = '';
                document.getElementById('new-name-ar').value = '';
                document.getElementById('new-price').value = '';
                showToast('Item added successfully!');
                loadMenu();
            } else {
                showToast('Failed to add item', true);
            }
        });
    }
}

function saveSizePrices(id) {
    var categoryId = parseInt(document.getElementById('ic_' + id).value, 10);
    var nameEn = document.getElementById('ne_' + id).value.trim();
    var nameAr = document.getElementById('na_' + id).value.trim();
    var s = parseFloat(document.getElementById('ps_' + id).value);
    var m = parseFloat(document.getElementById('pm_' + id).value);
    var l = parseFloat(document.getElementById('pl_' + id).value);
    if (!categoryId || nameEn === '' || nameAr === '') { showToast('Category and item names are required', true); return; }
    if (isNaN(s) || isNaN(m) || isNaN(l)) { showToast('Invalid price value', true); return; }

    var btn = document.getElementById('sbtn-' + id);
    btn.textContent = 'Saving...'; btn.className = 'save-btn saving'; btn.disabled = true;

    sendUpdate({action: 'update_size_item', id: id, category_id: categoryId, name_en: nameEn, name_ar: nameAr, price_small: s, price_medium: m, price_large: l}, function(ok) {
        if (ok) {
            btn.textContent = '✓ Saved'; btn.className = 'save-btn saved';
            showToast('Item updated!');
            loadMenu();
        } else {
            btn.textContent = 'Error!'; btn.className = 'save-btn';
            showToast('Failed to save item', true);
        }
        setTimeout(function() { btn.textContent = 'Save'; btn.className = 'save-btn'; btn.disabled = false; }, 1500);
    });
}

function convertToSizeBased(id, basePrice) {
    var msg = 'Convert this item to size-based pricing?<br><br>' +
              '<strong>Small:</strong> ' + (basePrice * 0.85).toFixed(3) + ' KD (15% less)<br>' +
              '<strong>Medium:</strong> ' + basePrice.toFixed(3) + ' KD (same as original)<br>' +
              '<strong>Large:</strong> ' + (basePrice * 1.15).toFixed(3) + ' KD (15% more)<br><br>' +
              'You can edit these prices after conversion.';

    showConfirm('Convert to Size-based', msg, 'Yes, Convert', function() {
        sendUpdate({action: 'convert_to_size_based', id: id, base_price: basePrice}, function(ok) {
            if (ok) {
                showToast('Item converted to size-based pricing!');
                loadMenu();
            } else {
                showToast('Failed to convert item', true);
            }
        });
    });
}

function doSave(id, payload) {
    var btn = document.getElementById('sbtn-' + id);
    btn.textContent = 'Saving...'; btn.className = 'save-btn saving'; btn.disabled = true;
    sendUpdate(payload, function(ok) {
        if (ok) {
            btn.textContent = '✓ Saved'; btn.className = 'save-btn saved';
            showToast('Price updated successfully!');
        } else {
            btn.textContent = 'Error!'; btn.className = 'save-btn';
            showToast('Failed to save price', true);
        }
        setTimeout(function() { btn.textContent = 'Save'; btn.className = 'save-btn'; btn.disabled = false; }, 1500);
    });
}

function deleteItem(id, name) {
    showConfirm('Delete Item', 'Delete item <b style="color:#f39c12">' + name + '</b>?<br>This will remove it from the menu.', function() {
        sendUpdate({action: 'delete_item', id: id}, function(ok) {
            if (ok) {
                var row = document.getElementById('row-' + id);
                if (row) row.remove();
                showToast('Item deleted!');
            } else {
                showToast('Failed to delete item', true);
            }
        });
    });
}

function deleteCategory(id, name) {
    showConfirm('Delete Category', 'Delete category <b style="color:#f39c12">' + name + '</b>?<br><span style="color:#e74c3c">All items and subcategories will also be removed.<br>This cannot be undone.</span>', function() {
        sendUpdate({action: 'delete_category', id: id}, function(ok) {
            if (ok) {
                showToast('Category deleted!');
                loadMenu();
            } else {
                showToast('Failed to delete category', true);
            }
        });
    });
}

function sendUpdate(payload, cb) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'api/menu.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var res = JSON.parse(xhr.responseText);
                if (cb) cb(res.success === true);
            } catch(e) { if (cb) cb(false); }
        }
    };
    xhr.send(JSON.stringify(payload));
}

var toastTimeout;
function showToast(msg, isError) {
    var el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'show' + (isError ? ' error' : '');
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(function() { el.className = ''; }, 2500);
}

loadMenu();
</script>
</body>
</html>
