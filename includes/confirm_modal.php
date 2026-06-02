<!-- ===== SHARED CONFIRM MODAL ===== -->
<style>
#confirm-overlay {
    display:none; position:fixed; top:0; left:0; right:0; bottom:0;
    background:rgba(0,0,0,0.5); z-index:99999; align-items:center; justify-content:center;
}
#confirm-overlay.show { display:flex; }
#confirm-box {
    background:#fff; border:1px solid #dee2e6; border-radius:14px;
    padding:30px 32px; max-width:390px; width:92%; box-shadow:0 10px 40px rgba(0,0,0,0.2);
    text-align:center; animation:confirmPop 0.18s ease;
}
@keyframes confirmPop { from { transform:scale(0.88); opacity:0; } to { transform:scale(1); opacity:1; } }
#confirm-icon { font-size:40px; margin-bottom:10px; }
#confirm-title { color:#e74c3c; font-size:18px; font-weight:bold; margin-bottom:10px; }
#confirm-msg { color:#495057; font-size:13px; line-height:1.7; margin-bottom:24px; }
.confirm-btns { display:flex; gap:12px; justify-content:center; }
.confirm-btn-ok {
    padding:10px 30px; background:linear-gradient(135deg,#8ab4f8,#7aa0e8);
    color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:bold;
    cursor:pointer; font-family:Tahoma,Arial,sans-serif; min-width:110px;
    transition:all 0.15s;
}
.confirm-btn-ok:hover { background:linear-gradient(135deg,#7aa0e8,#6a90d8); transform:translateY(-1px); }
.confirm-btn-cancel {
    padding:10px 30px; background:#fff; color:#495057;
    border:1px solid #ced4da; border-radius:8px; font-size:14px; font-weight:bold;
    cursor:pointer; font-family:Tahoma,Arial,sans-serif; min-width:110px;
    transition:all 0.15s;
}
.confirm-btn-cancel:hover { background:#f8f9fa; border-color:#adb5bd; }
</style>

<div id="confirm-overlay">
  <div id="confirm-box">
    <div id="confirm-icon">&#10067;</div>
    <div id="confirm-title">Confirm</div>
    <div id="confirm-msg"></div>
    <div class="confirm-btns">
      <button class="confirm-btn-ok" id="confirm-ok" onclick="var fn=_confirmCb; closeConfirm(); if(fn) fn();">Yes</button>
      <button class="confirm-btn-cancel" onclick="closeConfirm()">Cancel</button>
    </div>
  </div>
</div>

<script>
var _confirmCb = null;
function showConfirm(title, msg, okLabel, icon, cb) {
    if (typeof okLabel === 'function') { cb = okLabel; okLabel = 'Yes'; icon = '&#10067;'; }
    if (typeof icon === 'function') { cb = icon; icon = '&#10067;'; }
    document.getElementById('confirm-icon').innerHTML  = icon  || '&#10067;';
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-msg').innerHTML   = msg;
    document.getElementById('confirm-ok').innerHTML    = okLabel || 'Yes';
    document.getElementById('confirm-overlay').className = 'show';
    _confirmCb = cb;
    document.getElementById('confirm-ok').focus();
}
function closeConfirm() {
    document.getElementById('confirm-overlay').className = '';
    _confirmCb = null;
}
document.getElementById('confirm-overlay').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
});
</script>
