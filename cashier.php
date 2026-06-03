<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>แคชเชียร์ · แซ่บกลางซอย</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<style>
body{font-family:'Sarabun',sans-serif;background:#F7F3EF;}
.btn-red{background:linear-gradient(135deg,#FF5546,#F23A2B,#C41E0E);}
/* iOS safe area */
.safe-bottom{padding-bottom:max(16px,env(safe-area-inset-bottom));}
/* Bottom sheet */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,0.5);backdrop-filter:blur(3px);z-index:50;}
.sheet{position:fixed;left:50%;bottom:0;transform:translateX(-50%);z-index:51;background:#fff;
  border-radius:28px 28px 0 0;box-shadow:0 -16px 40px rgba(0,0,0,0.22);
  max-height:92vh;overflow-y:auto;width:100%;max-width:640px;}
/* Zone tab active */
.zone-tab{flex-shrink:0;height:36px;display:flex;align-items:center;gap:6px;
  border-radius:99px;padding:0 16px;font-size:12px;font-weight:500;
  background:#fff;color:#7C5B47;border:1px solid #F0E0D4;white-space:nowrap;transition:all .15s;}
.zone-tab.active{background:linear-gradient(135deg,#FF5546,#F23A2B,#C41E0E);
  color:#fff;border-color:transparent;box-shadow:0 4px 12px rgba(225,39,23,0.28);}
/* Table card animations */
.table-card{transition:transform .1s,box-shadow .1s;}
.table-card:active{transform:scale(0.97);}
/* Scrollbar hide */
.no-scroll::-webkit-scrollbar{display:none;}
.no-scroll{scrollbar-width:none;}
/* QR modal */
.qr-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);z-index:60;display:flex;align-items:center;justify-content:center;padding:20px;}
.qr-box{background:#fff;border-radius:28px;padding:28px 24px 24px;max-width:340px;width:100%;text-align:center;box-shadow:0 24px 60px rgba(0,0,0,0.3);}
/* Cashier menu picker panel (slides in from right) */
.picker-panel{position:fixed;top:0;right:0;height:100%;width:min(100%,480px);z-index:61;
  background:#fff;box-shadow:-20px 0 50px rgba(0,0,0,0.18);
  display:flex;flex-direction:column;
  transform:translateX(100%);transition:transform .3s cubic-bezier(.4,0,.2,1);}
.picker-panel.open{transform:translateX(0);}
/* Print: show only QR content */
@media print{
  body > *:not(#print-area){display:none!important;}
  #print-area{display:block!important;position:static;padding:20px;}
  #print-area canvas,#print-area img{max-width:200px!important;}
}
</style>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet"/>
</head>
<body class="min-h-screen">

<!-- ───── Navbar ───── -->
<nav class="sticky top-0 z-40 border-b border-black/8 bg-white/85 backdrop-blur-md px-4 py-3 flex items-center justify-between gap-2">
  <div class="flex items-center gap-2.5 min-w-0">
    <a href="index.php" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#F7F3EF] text-[#4A3728]">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
    </a>
    <div class="min-w-0">
      <h1 class="text-[16px] font-bold text-[#2C1713] leading-tight">แคชเชียร์</h1>
      <p id="nav-sub" class="text-[11px] text-[#9D7F6A] truncate">กำลังโหลด...</p>
    </div>
  </div>
  <div class="flex items-center gap-2 shrink-0">
    <button id="refreshBtn" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#F7F3EF] text-[#4A3728]">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
    </button>
    <a href="kitchen.php" class="flex h-9 items-center gap-1.5 rounded-xl bg-[#F7F3EF] px-3 text-[12px] font-medium text-[#4A3728]">
      🍳 ครัว
    </a>
  </div>
</nav>

<!-- ───── Status summary pills ───── -->
<div id="status-bar" class="px-4 pt-3 pb-1 flex gap-2 overflow-x-auto no-scroll"></div>

<!-- ───── Zone tabs ───── -->
<div class="sticky top-[61px] z-30 bg-[#F7F3EF]/92 backdrop-blur-md px-4 pt-2 pb-2.5 border-b border-black/5">
  <div class="flex gap-2 overflow-x-auto no-scroll" id="zone-tabs">
    <button data-zone="all" class="zone-tab active">ทั้งหมด</button>
    <?php foreach(['A','B','C','D'] as $z): ?>
    <button data-zone="<?= $z ?>" class="zone-tab">โซน <?= $z ?></button>
    <?php endforeach; ?>
  </div>
</div>

<!-- ───── Table grid ───── -->
<main class="p-4 pb-8">
  <div id="table-grid" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
    <div class="col-span-full py-10 text-center text-[13px] text-[#9D7F6A]">กำลังโหลดโต๊ะ...</div>
  </div>
</main>

<!-- ───── Sheet overlay ───── -->
<div id="overlay" class="overlay hidden" onclick="closeSheet()"></div>
<div id="sheet" class="sheet hidden">
  <div class="flex justify-center pt-3 pb-1">
    <div class="h-1 w-10 rounded-full bg-[#E8D6C6]"></div>
  </div>
  <div id="sheet-content" class="px-5 pb-5 pt-2 safe-bottom"></div>
</div>

<!-- ───── QR Code Modal ───── -->
<div id="qr-overlay" class="qr-overlay hidden">
  <div class="qr-box">
    <div class="flex items-center justify-between mb-4">
      <div class="text-left">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-[#9D7F6A]">QR Code สำหรับ</p>
        <p id="qr-table-label" class="text-[22px] font-extrabold text-[#2C1713]">โต๊ะ A1</p>
      </div>
      <button onclick="closeQR()" class="flex h-9 w-9 items-center justify-center rounded-full bg-[#F7EFE7] text-[#5A4338]">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <!-- QR code renders here -->
    <div id="qr-canvas-wrap" class="flex justify-center mb-4 rounded-[18px] bg-[#FFF9F5] p-4 ring-1 ring-[#F0E0D4]">
      <div id="qr-canvas"></div>
    </div>

    <!-- URL text -->
    <p id="qr-url-text" class="text-[11px] text-[#9D7F6A] break-all mb-1 px-2"></p>
    <p class="text-[10px] text-[#C8A48B] mb-5">ลูกค้าสแกน QR นี้เพื่อสั่งอาหาร</p>

    <!-- Actions -->
    <div class="flex gap-2">
      <button onclick="printQR()" class="flex-1 flex items-center justify-center gap-1.5 rounded-[18px] border border-[#F0E0D4] bg-[#F7F3EF] py-3 text-[13px] font-semibold text-[#2C1713]">
        🖨 พิมพ์
      </button>
      <button onclick="closeQR()" class="flex-[1.5] rounded-[18px] py-3 text-[13px] font-semibold text-white btn-red shadow-[0_8px_20px_rgba(225,39,23,0.25)]">
        ปิด
      </button>
    </div>
  </div>
</div>

<!-- Hidden print area -->
<div id="print-area" style="display:none"></div>

<!-- ───── Cashier Menu Picker Panel ───── -->
<div id="picker-overlay" class="overlay hidden" style="z-index:60;" onclick="closePicker()"></div>
<div id="picker-panel" class="picker-panel">
  <!-- Header -->
  <div class="flex shrink-0 items-center gap-3 border-b border-[#F0E0D4] bg-white px-5 py-4">
    <button onclick="closePicker()" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#F7F3EF] text-[#4A3728]">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
    </button>
    <div class="flex-1 min-w-0">
      <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[#9D7F6A]">เพิ่มรายการ · โต๊ะ <span id="picker-table-label"></span></p>
      <h2 class="text-[15px] font-semibold text-[#2C1713]">เลือกเมนูส่งครัว</h2>
    </div>
  </div>
  <!-- Search + categories -->
  <div class="shrink-0 space-y-3 border-b border-[#F0E0D4] bg-white px-5 pb-3 pt-3">
    <div class="flex h-10 items-center gap-2.5 rounded-2xl bg-[#F7F3EF] px-3.5">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-[#9D7F6A]"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      <input id="picker-search" type="text" placeholder="ค้นหาเมนู..." class="flex-1 bg-transparent text-[13px] text-[#2C1713] placeholder:text-[#C4A98A] outline-none"/>
    </div>
    <div id="picker-cats" class="flex gap-2 overflow-x-auto no-scroll pb-0.5"></div>
  </div>
  <!-- Menu item list -->
  <div id="picker-items" class="flex-1 overflow-y-auto px-5 py-4 space-y-2"></div>
  <!-- Footer: cart summary + submit -->
  <div class="shrink-0 border-t border-[#F0E0D4] bg-white px-5 py-4">
    <div id="picker-cart-info" class="hidden mb-3 rounded-[16px] bg-[#FFF9F5] px-4 py-3 ring-1 ring-[#F0E0D4]">
      <div id="picker-cart-items" class="space-y-1 text-[12px] text-[#2C1713] mb-2"></div>
      <div class="flex justify-between items-center pt-2 border-t border-dashed border-[#F0E0D4]">
        <span class="text-[12px] font-semibold text-[#2C1713]">รวม</span>
        <span id="picker-cart-total" class="text-[14px] font-bold text-[#E12717] tabular-nums"></span>
      </div>
    </div>
    <button id="picker-submit-btn" onclick="pickerSubmit()" disabled
      class="w-full rounded-[18px] py-3.5 text-[14px] font-bold text-white btn-red opacity-40 shadow-[0_10px_22px_rgba(225,39,23,0.28)] transition-opacity">
      ส่งออเดอร์ให้ครัว
    </button>
  </div>
</div>

<script>
const VAT_RATE = 0.07;
let tables=[], orders=[], menuItems=[], settings={};
let activeZone='all', selectedTableId=null;

function fmtMoney(n){ return '฿'+Number(n).toLocaleString('th-TH'); }
function escHtml(s){ return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

/* ─── Picker state ─── */
let pickerTid=null, pickerActiveCat='ทั้งหมด', pickerSearch='';
let pickerCart={}; // {menuId:{item,qty,note}}

/* ─── Status colours ─── */
const S = {
  available:{ label:'ว่าง',     dot:'bg-emerald-400', card:'bg-white border border-emerald-100',
              pill:'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200', textColor:'text-emerald-600' },
  active:   { label:'มีลูกค้า', dot:'bg-amber-400',   card:'bg-amber-50 border border-amber-100',
              pill:'bg-amber-50 text-amber-700 ring-1 ring-amber-200',       textColor:'text-amber-600'  },
  preparing:{ label:'กำลังทำ',  dot:'bg-orange-400',  card:'bg-orange-50 border border-orange-100',
              pill:'bg-orange-50 text-orange-700 ring-1 ring-orange-200',    textColor:'text-orange-600' },
  billing:  { label:'รอชำระ',  dot:'bg-violet-400',  card:'bg-violet-50 border border-violet-100',
              pill:'bg-violet-50 text-violet-700 ring-1 ring-violet-200',    textColor:'text-violet-600' },
};

/* ─── Load all data ─── */
function loadAll(){
  return Promise.all([
    $.getJSON('api/tables.php'),
    $.getJSON('api/orders.php'),
    $.getJSON('api/menu.php'),
    $.getJSON('api/settings.php'),
  ]).then(function([t,o,m,s]){
    tables=t; orders=o; menuItems=m; settings=s;
    const busy=t.filter(x=>x.status!=='available').length;
    $('#nav-sub').text(`${busy}/${t.length} โต๊ะกำลังใช้ · ${s.restaurantName||'แซ่บกลางซอย'}`);
    renderStatusBar();
    renderGrid();
    if(selectedTableId){ showSheet(selectedTableId); }
  });
}

/* ─── Status summary bar ─── */
function renderStatusBar(){
  const counts={available:0,active:0,preparing:0,billing:0};
  tables.forEach(t=>{ if(counts[t.status]!==undefined) counts[t.status]++; });
  $('#status-bar').html(Object.entries(counts).filter(([,v])=>v>0).map(([k,v])=>`
    <div class="flex shrink-0 items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-semibold ${S[k].pill}">
      <span class="h-1.5 w-1.5 rounded-full ${S[k].dot}"></span>${S[k].label} ${v}
    </div>`).join(''));
}

/* ─── Table grid ─── */
function renderGrid(){
  const list = activeZone==='all' ? tables : tables.filter(t=>t.zone===activeZone);
  if(!list.length){
    $('#table-grid').html('<div class="col-span-full py-10 text-center text-[#9D7F6A]">ไม่มีโต๊ะในโซนนี้</div>');
  } else {
    $('#table-grid').html(list.map(t=>{
      const s=S[t.status]||S.available;
      const tableOrds=orders.filter(o=>o.tableId===t.id);
      const amt=tableOrds.reduce((sum,o)=>sum+o.items.reduce((ss,i)=>ss+i.price*i.quantity,0),0);
      const vat=Math.round(amt*VAT_RATE);
      return `<button type="button" data-tid="${t.id}" onclick="showSheet('${t.id}')"
        class="table-card rounded-[20px] p-4 text-left ${s.card} shadow-[0_4px_16px_rgba(44,23,19,0.07)] cursor-pointer">
        <div class="flex items-start justify-between gap-1.5 mb-2.5">
          <span class="text-[24px] font-extrabold text-[#2C1713] leading-none">${t.id}</span>
          <span class="flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ${s.pill}">
            <span class="h-1.5 w-1.5 rounded-full ${s.dot}"></span>${s.label}
          </span>
        </div>
        <p class="text-[11px] text-[#9D7F6A]">โซน ${t.zone} · ${t.seats} ที่${t.guests?' · '+t.guests+' คน':''}</p>
        ${t.openedAt?`<p class="text-[10px] text-[#C8A48B] tabular-nums mt-0.5">เปิด ${t.openedAt}</p>`:''}
        ${amt>0?`<p class="text-[13px] font-bold text-[#E12717] tabular-nums mt-1.5">${fmtMoney(amt+vat)}</p>`:''}
      </button>`;
    }).join(''));
  }
  /* Zone tab styling */
  $('#zone-tabs .zone-tab').each(function(){
    if($(this).data('zone')===activeZone) $(this).addClass('active');
    else $(this).removeClass('active');
  });
}

/* ─── Table detail sheet ─── */
function showSheet(tid){
  const t=tables.find(x=>x.id===tid); if(!t) return;
  selectedTableId=tid;
  const s=S[t.status]||S.available;
  const tableOrds=orders.filter(o=>o.tableId===tid);
  const allItems=[]; tableOrds.forEach(o=>o.items.forEach(i=>allItems.push({...i,orderId:o.id})));
  const subtotal=allItems.reduce((sum,i)=>sum+i.price*i.quantity,0);
  const vat=Math.round(subtotal*VAT_RATE); const total=subtotal+vat;

  const itemsHtml = allItems.length
    ? `<div class="divide-y divide-[#F7EFE7]">${allItems.map(i=>`
        <div class="item-row flex items-center gap-2 py-2.5"
             data-oid="${escHtml(i.orderId)}" data-mid="${escHtml(i.menuId)}">
          <div class="min-w-0 flex-1">
            <p class="text-[13px] font-medium text-[#2C1713] truncate">${escHtml(i.name)}</p>
            ${i.note?`<p class="text-[11px] text-[#9D7F6A] truncate">${escHtml(i.note)}</p>`:''}
          </div>
          <div class="flex items-center gap-1.5 shrink-0">
            <button type="button" class="ci-del flex h-7 w-7 items-center justify-center rounded-full text-[#C8A48B] hover:bg-red-50 hover:text-red-400 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <div class="flex items-center gap-0.5 rounded-full bg-[#F7EFE7] p-0.5">
              <button type="button" class="ci-dec flex h-6 w-6 items-center justify-center rounded-full bg-white text-[#5A4338] text-[14px] font-bold shadow-sm leading-none">−</button>
              <span class="item-qty min-w-[18px] text-center text-[12px] font-bold text-[#2C1713] tabular-nums">${i.quantity}</span>
              <button type="button" class="ci-inc flex h-6 w-6 items-center justify-center rounded-full bg-white text-[#5A4338] text-[14px] font-bold shadow-sm leading-none">+</button>
            </div>
            <span class="text-[12px] font-semibold text-[#2C1713] tabular-nums w-14 text-right">${fmtMoney(i.price*i.quantity)}</span>
          </div>
        </div>`).join('')}</div>`
    : '<p class="py-5 text-center text-[13px] text-[#9D7F6A]">ยังไม่มีออเดอร์</p>';

  const billHtml = subtotal>0 ? `
    <div class="mt-4 rounded-[18px] bg-[#FFF9F5] p-4 ring-1 ring-[#F0E0D4] space-y-1.5">
      <div class="flex justify-between text-[12px] text-[#9D7F6A]"><span>ยอดอาหาร</span><span class="tabular-nums">${fmtMoney(subtotal)}</span></div>
      <div class="flex justify-between text-[12px] text-[#9D7F6A]"><span>VAT 7%</span><span class="tabular-nums">${fmtMoney(vat)}</span></div>
      <div class="flex justify-between pt-1.5 border-t border-dashed border-[#E8D6C6]">
        <span class="text-[14px] font-bold text-[#2C1713]">รวมต้องชำระ</span>
        <span class="text-[18px] font-extrabold text-[#E12717] tabular-nums">${fmtMoney(total)}</span>
      </div>
    </div>` : '';

  /* Actions */
  let actionsHtml='';
  if(t.status==='available'){
    actionsHtml=`<div class="mt-5">
      <label class="text-[12px] text-[#9D7F6A] mb-2 block">จำนวนลูกค้า (คน)</label>
      <div class="flex gap-2">
        <input id="guests-input" type="number" min="1" max="20" value="2"
          class="w-24 rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[15px] font-bold text-center text-[#2C1713] outline-none focus:border-[#E12717]"/>
        <button onclick="openTable('${tid}')"
          class="flex-1 rounded-[18px] py-3 text-[14px] font-bold text-white btn-red shadow-[0_10px_22px_rgba(225,39,23,0.28)]">
          เปิดโต๊ะ
        </button>
      </div>
    </div>`;
  } else if(t.status==='active'||t.status==='preparing'){
    actionsHtml=`<div class="mt-5 flex gap-2">
      <button onclick="setStatus('${tid}','billing')"
        class="flex-1 rounded-[18px] border border-[#E8D6C6] bg-white py-3 text-[13px] font-semibold text-[#2C1713]">
        รอชำระ
      </button>
      <button onclick="showBillConfirm('${tid}')"
        class="flex-[1.6] rounded-[18px] py-3 text-[14px] font-bold text-white btn-red shadow-[0_10px_22px_rgba(225,39,23,0.28)]">
        ปิดบิล · ${fmtMoney(total)}
      </button>
    </div>`;
  } else if(t.status==='billing'){
    actionsHtml=`<div class="mt-5">
      <button onclick="showBillConfirm('${tid}')"
        class="w-full rounded-[18px] py-3.5 text-[14px] font-bold text-white btn-red shadow-[0_10px_22px_rgba(225,39,23,0.28)]">
        ยืนยันปิดบิล · ${fmtMoney(total)}
      </button>
    </div>`;
  }

  $('#sheet-content').html(`
    <div class="flex items-start justify-between mb-4">
      <div>
        <div class="flex items-center gap-2.5 flex-wrap">
          <h2 class="text-[26px] font-extrabold text-[#2C1713]">โต๊ะ ${t.id}</h2>
          <span class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ${s.pill}">
            <span class="h-2 w-2 rounded-full ${s.dot}"></span>${s.label}
          </span>
        </div>
        <p class="text-[12px] text-[#9D7F6A] mt-0.5">
          โซน ${t.zone} · ${t.seats} ที่นั่ง
          ${t.guests?' · '+t.guests+' คน':''}
          ${t.openedAt?' · เปิด '+t.openedAt:''}
        </p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        ${t.status!=='available'?`<button onclick="showMenuPicker('${t.id}')"
          class="flex items-center gap-1.5 rounded-full btn-red px-3 py-1.5 text-[12px] font-semibold text-white shadow-[0_4px_12px_rgba(225,39,23,0.28)]">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          เพิ่มรายการ
        </button>`:''}
        <button onclick="showQR('${t.id}')"
          class="flex items-center gap-1.5 rounded-full bg-[#FFF3EC] px-3 py-1.5 text-[12px] font-semibold text-[#E12717] ring-1 ring-[#F0E0D4]">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/></svg>
          QR
        </button>
        <button onclick="closeSheet()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#F7EFE7] text-[#5A4338]">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>
    <div>${itemsHtml}</div>
    ${billHtml}
    ${actionsHtml}
  `);

  $('#overlay').removeClass('hidden');
  $('#sheet').removeClass('hidden');
}

function closeSheet(){ $('#overlay,#sheet').addClass('hidden'); selectedTableId=null; }

function openTable(tid){
  const g=parseInt($('#guests-input').val())||1;
  $.ajax({url:'api/table.php?id='+encodeURIComponent(tid),method:'PATCH',
    contentType:'application/json',data:JSON.stringify({action:'open',guests:g}),
    success:()=>loadAll().then(()=>showSheet(tid)),
    error:()=>alert('เปิดโต๊ะไม่สำเร็จ')
  });
}

function setStatus(tid,status){
  $.ajax({url:'api/table.php?id='+encodeURIComponent(tid),method:'PATCH',
    contentType:'application/json',data:JSON.stringify({status}),
    success:()=>loadAll().then(()=>showSheet(tid)),
    error:()=>alert('เปลี่ยนสถานะไม่สำเร็จ')
  });
}

/* ─── Bill confirm — 3-screen flow ─── */
let pendingCloseTid=null;

/* Screen 1: ใบเสร็จสรุป + เลือกวิธีชำระ */
function showBillConfirm(tid){
  pendingCloseTid=tid;
  const t=tables.find(x=>x.id===tid); if(!t) return;
  const tableOrds=orders.filter(o=>o.tableId===tid);
  const allItems=[]; tableOrds.forEach(o=>o.items.forEach(i=>allItems.push(i)));
  const sub=allItems.reduce((s,i)=>s+i.price*i.quantity,0);
  const vat=Math.round(sub*VAT_RATE); const total=sub+vat;

  const itemsHtml=allItems.length
    ? allItems.map(i=>`
        <div class="flex items-start justify-between gap-2 py-1.5 border-b border-dashed border-[#F0E0D4] last:border-0">
          <div class="min-w-0 flex-1 text-[12px] text-[#2C1713]">
            ${escHtml(i.name)}${i.note?` <span class="text-[10px] text-[#9D7F6A]">(${escHtml(i.note)})</span>`:''}
          </div>
          <span class="tabular-nums text-[12px] text-[#7C5B47] shrink-0">×${i.quantity} = ${fmtMoney(i.price*i.quantity)}</span>
        </div>`).join('')
    : '<p class="py-3 text-center text-[12px] text-[#9D7F6A]">ไม่มีรายการ</p>';

  $('#sheet-content').html(`
    <div class="flex items-center justify-between mb-4">
      <div>
        <p class="text-[18px] font-bold text-[#2C1713]">ใบเสร็จ</p>
        <p class="text-[12px] text-[#9D7F6A]">โต๊ะ ${tid}${t.guests?' · '+t.guests+' คน':''}</p>
      </div>
      <div class="flex items-center gap-2">
        <button onclick="printBillReceipt('${tid}')"
          class="flex items-center gap-1.5 rounded-full border border-[#E8D6C6] bg-white px-3 py-1.5 text-[11px] font-semibold text-[#5A4338] hover:bg-[#F7F3EF]">
          🖨 พิมพ์
        </button>
        <button onclick="showSheet('${tid}')" class="flex h-9 w-9 items-center justify-center rounded-full bg-[#F7EFE7] text-[#5A4338]">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>

    <!-- รายการอาหาร -->
    <div class="mb-3 max-h-[180px] overflow-y-auto rounded-[16px] bg-[#FFF9F5] px-4 py-3 ring-1 ring-[#F0E0D4]">
      ${itemsHtml}
    </div>

    <!-- ยอดรวม -->
    <div class="mb-5 rounded-[18px] bg-gradient-to-br from-[#FFF5F0] to-[#FFE9DE] p-4 ring-1 ring-[#F2D1BD]/50">
      <div class="flex justify-between text-[12px] text-[#7C5B47]"><span>ยอดอาหาร</span><span class="tabular-nums">${fmtMoney(sub)}</span></div>
      <div class="flex justify-between text-[12px] text-[#7C5B47] mt-1"><span>VAT 7%</span><span class="tabular-nums">${fmtMoney(vat)}</span></div>
      <div class="flex items-end justify-between mt-3 pt-2.5 border-t border-[#EFD9CC]">
        <span class="text-[13px] font-semibold text-[#2C1713]">รวมต้องชำระ</span>
        <span class="text-[26px] font-extrabold text-[#E12717] tabular-nums leading-none">${fmtMoney(total)}</span>
      </div>
    </div>

    <!-- เลือกวิธีชำระ -->
    <p class="text-[11px] font-semibold uppercase tracking-wide text-[#A98671] mb-3">เลือกวิธีชำระ</p>
    <div class="grid grid-cols-2 gap-3">
      <button onclick="showQrPayment('${tid}',${total})"
        class="flex flex-col items-center gap-2 rounded-[18px] border-2 border-[#E12717] bg-gradient-to-br from-white to-[#FFF5F0] p-4 transition-transform active:scale-[0.97] shadow-[0_4px_14px_rgba(225,39,23,0.12)]">
        <span class="text-[30px]">📱</span>
        <span class="text-[13px] font-bold text-[#2C1713]">สแกน QR จ่าย</span>
        <span class="text-[10px] text-[#9D7F6A]">พร้อมเพย์ / โอนเงิน</span>
      </button>
      <button onclick="showCashPayment('${tid}',${total})"
        class="flex flex-col items-center gap-2 rounded-[18px] border-2 border-[#F0E0D4] bg-white p-4 transition-transform active:scale-[0.97] hover:border-emerald-300">
        <span class="text-[30px]">💵</span>
        <span class="text-[13px] font-bold text-[#2C1713]">จ่ายเงินสด</span>
        <span class="text-[10px] text-[#9D7F6A]">รับเงิน / ทอนเงิน</span>
      </button>
    </div>
  `);
}

/* Screen 2a: QR พร้อมเพย์ */
function showQrPayment(tid, total){
  const qrUrl=settings.promptPayQr||'';
  const rName=escHtml(settings.restaurantName||'แซ่บกลางซอย');

  $('#sheet-content').html(`
    <div class="flex items-center gap-3 mb-5">
      <button onclick="showBillConfirm('${tid}')" class="flex h-9 w-9 items-center justify-center rounded-full bg-[#F7EFE7] text-[#5A4338]">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
      </button>
      <div>
        <p class="text-[16px] font-bold text-[#2C1713]">สแกน QR จ่าย</p>
        <p class="text-[11px] text-[#9D7F6A]">โต๊ะ ${tid} · พร้อมเพย์</p>
      </div>
    </div>

    ${qrUrl?`
      <div class="flex flex-col items-center rounded-[20px] bg-[#FFF9F5] px-5 pt-5 pb-4 ring-1 ring-[#F0E0D4] mb-4">
        <p class="text-[11px] text-[#9D7F6A] mb-4">แสดง QR นี้ให้ลูกค้าสแกนพร้อมเพย์</p>
        <div class="rounded-2xl bg-white p-2 shadow-[0_4px_16px_rgba(44,23,19,0.1)]">
          <img src="${qrUrl}" alt="QR พร้อมเพย์"
            class="block rounded-xl" style="width:200px;height:200px;object-fit:contain;"
            onerror="this.closest('div').innerHTML='<p style=\\'color:#e12717;font-size:12px;padding:12px\\'>โหลดรูปไม่ได้ — ตรวจสอบ URL</p>'"/>
        </div>
        <p class="mt-4 text-[26px] font-extrabold text-[#E12717] tabular-nums">${fmtMoney(total)}</p>
        <p class="mt-1.5 text-[11px] text-[#9D7F6A] text-center">กรุณากรอกยอด <b>${fmtMoney(total)}</b> ในแอปธนาคาร</p>
        <p class="mt-0.5 text-[11px] text-[#C4A98A]">${rName}</p>
      </div>
    `:`
      <div class="rounded-[16px] border border-amber-200 bg-amber-50 p-4 mb-4 text-center">
        <p class="text-[13px] font-semibold text-amber-800">⚠️ ยังไม่ได้ตั้งค่ารูป QR พร้อมเพย์</p>
        <p class="mt-1 text-[11px] text-amber-700">ไปที่ Dashboard → ตั้งค่า → รูป QR Code พร้อมเพย์</p>
      </div>
      <div class="flex flex-col items-center rounded-[20px] bg-[#FFF9F5] p-5 ring-1 ring-[#F0E0D4] mb-4 text-center">
        <p class="text-[12px] text-[#9D7F6A] mb-2">ยอดต้องโอน</p>
        <p class="text-[28px] font-extrabold text-[#E12717] tabular-nums">${fmtMoney(total)}</p>
      </div>
    `}

    <button onclick="doCloseBill('transfer',null)"
      class="w-full rounded-[18px] py-4 text-[14px] font-bold text-white btn-red shadow-[0_12px_24px_rgba(225,39,23,0.28)] transition-transform active:scale-[0.98]">
      ✅ ยืนยันรับเงินแล้ว · ปิดบิล
    </button>
  `);
}

/* Screen 2b: เงินสด */
function showCashPayment(tid, total){
  // Quick cash amounts: exact + rounded banknotes
  const bills=[50,100,500,1000];
  const qAmts=[...new Set([total,...bills.map(b=>Math.ceil(total/b)*b)])].sort((a,b)=>a-b).slice(0,5);
  const quickHtml=qAmts.map(v=>`
    <button type="button" onclick="setCashRecv(${v},${total})"
      class="rounded-full border border-[#E8D6C6] bg-white px-3.5 py-1.5 text-[12px] font-semibold text-[#5A4338] hover:bg-[#F7F3EF] transition-colors">
      ฿${v.toLocaleString()}
    </button>`).join('');

  $('#sheet-content').html(`
    <div class="flex items-center gap-3 mb-5">
      <button onclick="showBillConfirm('${tid}')" class="flex h-9 w-9 items-center justify-center rounded-full bg-[#F7EFE7] text-[#5A4338]">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
      </button>
      <div>
        <p class="text-[16px] font-bold text-[#2C1713]">จ่ายเงินสด</p>
        <p class="text-[11px] text-[#9D7F6A]">โต๊ะ ${tid}</p>
      </div>
    </div>

    <div class="rounded-[18px] bg-gradient-to-br from-[#FFF5F0] to-[#FFE9DE] p-5 ring-1 ring-[#F2D1BD]/50 mb-5 text-center">
      <p class="text-[12px] text-[#7C5B47] mb-1">ยอดต้องชำระ</p>
      <p class="text-[32px] font-extrabold text-[#E12717] tabular-nums">${fmtMoney(total)}</p>
    </div>

    <div class="mb-3">
      <label class="text-[12px] font-medium text-[#9D7F6A] mb-1.5 block">รับเงินมา (บาท)</label>
      <input id="cash-recv" type="number" min="0" placeholder="${total}" inputmode="numeric"
        class="w-full rounded-xl border border-[#F0E0D4] px-4 py-3.5 text-[22px] font-bold text-center text-[#2C1713] outline-none focus:border-[#E12717]"/>
    </div>

    <!-- เงินทอน / ขาด -->
    <div id="change-box" class="hidden mb-4 flex items-center justify-between rounded-xl px-4 py-2.5 ring-1">
      <span id="change-label" class="text-[13px] font-medium"></span>
      <span id="change-val" class="text-[18px] font-extrabold tabular-nums"></span>
    </div>

    <!-- ธนบัตรด่วน -->
    <div class="flex flex-wrap gap-2 mb-5">${quickHtml}</div>

    <button id="cash-confirm-btn" onclick="confirmCashPayment(${total})" disabled
      class="w-full rounded-[18px] py-4 text-[14px] font-bold text-white btn-red opacity-40 shadow-[0_12px_24px_rgba(225,39,23,0.28)] transition-all">
      ✅ ยืนยันรับเงินแล้ว · ปิดบิล
    </button>
  `);

  $('#cash-recv').on('input',function(){ updateChange(parseFloat($(this).val())||0, total); });
  setTimeout(()=>$('#cash-recv').focus(),100);
}

function setCashRecv(amount, total){
  $('#cash-recv').val(amount);
  updateChange(amount, total);
}

function updateChange(recv, total){
  const change=recv-total;
  const box=$('#change-box'), lbl=$('#change-label'), val=$('#change-val');
  if(recv<=0){
    box.addClass('hidden');
    $('#cash-confirm-btn').prop('disabled',true).addClass('opacity-40');
    return;
  }
  if(change>=0){
    box.removeClass('hidden').attr('class','mb-4 flex items-center justify-between rounded-xl px-4 py-2.5 ring-1 bg-emerald-50 ring-emerald-200');
    lbl.attr('class','text-[13px] font-medium text-emerald-700').text('เงินทอน');
    val.attr('class','text-[18px] font-extrabold tabular-nums text-emerald-700').text(fmtMoney(change));
    $('#cash-confirm-btn').prop('disabled',false).removeClass('opacity-40');
  } else {
    box.removeClass('hidden').attr('class','mb-4 flex items-center justify-between rounded-xl px-4 py-2.5 ring-1 bg-red-50 ring-red-200');
    lbl.attr('class','text-[13px] font-medium text-red-700').text('รับเงินไม่พอ');
    val.attr('class','text-[18px] font-extrabold tabular-nums text-red-700').text(fmtMoney(-change));
    $('#cash-confirm-btn').prop('disabled',true).addClass('opacity-40');
  }
}

function confirmCashPayment(total){
  const recv=parseFloat($('#cash-recv').val())||0;
  if(recv<total){ alert('รับเงินน้อยกว่ายอดต้องชำระ'); return; }
  doCloseBill('cash', recv);
}

function doCloseBill(pm, cashReceived){
  $.ajax({url:'api/table.php?id='+encodeURIComponent(pendingCloseTid),method:'PATCH',
    contentType:'application/json',
    data:JSON.stringify({action:'close',paymentMethod:pm,cashReceived:cashReceived}),
    success:function(){ closeSheet(); loadAll(); },
    error:function(){ alert('ปิดบิลไม่สำเร็จ'); }
  });
}

/* ─── Print receipt ─── */
function printBillReceipt(tid){
  const t=tables.find(x=>x.id===tid); if(!t) return;
  const tableOrds=orders.filter(o=>o.tableId===tid);
  const allItems=[]; tableOrds.forEach(o=>o.items.forEach(i=>allItems.push(i)));
  const sub=allItems.reduce((s,i)=>s+i.price*i.quantity,0);
  const vat=Math.round(sub*VAT_RATE); const total=sub+vat;
  const qrUrl=settings.promptPayQr||'';
  const now=new Date().toLocaleString('th-TH',{timeZone:'Asia/Bangkok',hour:'2-digit',minute:'2-digit',day:'2-digit',month:'short'});
  const rName=settings.restaurantName||'แซ่บกลางซอย';

  const rows=allItems.map(i=>`<tr>
    <td style="padding:3px 0;font-size:13px;line-height:1.4">${i.name}${i.note?'<br><span style="font-size:11px;color:#888">('+i.note+')</span>':''}</td>
    <td style="text-align:center;padding:3px 4px;font-size:13px">×${i.quantity}</td>
    <td style="text-align:right;padding:3px 0;font-size:13px;white-space:nowrap">฿${(i.price*i.quantity).toLocaleString()}</td>
  </tr>`).join('');

  const win=window.open('','_blank','width=320,height=600');
  win.document.write(`<!DOCTYPE html><html lang="th"><head>
    <meta charset="UTF-8"/><title>ใบเสร็จ โต๊ะ ${tid}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700;900&display=swap" rel="stylesheet"/>
    <style>
      *{margin:0;padding:0;box-sizing:border-box;}
      @page{size:80mm auto;margin:0;}
      body{font-family:'Sarabun',sans-serif;background:#fff;color:#2C1713;width:80mm;padding:4mm 3mm;margin:0 auto;}
      .center{text-align:center;}
      .logo{font-size:5mm;font-weight:900;color:#2C1713;}
      .sub{font-size:3mm;color:#9D7F6A;margin-top:0.5mm;}
      .info{font-size:3mm;color:#5A4338;margin:2mm 0;text-align:center;}
      .dash{border:none;border-top:0.3mm dashed #C4A98A;margin:2mm 0;}
      table{width:100%;border-collapse:collapse;}
      td{font-size:3.2mm;vertical-align:top;line-height:1.5;}
      td:nth-child(2){text-align:center;padding:0 1mm;white-space:nowrap;}
      td:nth-child(3){text-align:right;white-space:nowrap;}
      .tot{display:flex;justify-content:space-between;font-size:3mm;color:#7C5B47;padding:0.5mm 0;}
      .grand{display:flex;justify-content:space-between;align-items:baseline;border-top:0.5mm solid #2C1713;margin-top:2mm;padding-top:2mm;}
      .grand .lbl{font-size:3.5mm;font-weight:700;}
      .grand .amt{font-size:6mm;font-weight:900;color:#E12717;}
      .qr-sec{text-align:center;margin-top:3mm;padding-top:2.5mm;border-top:0.3mm dashed #C4A98A;}
      .qr-sec .hint{font-size:2.8mm;color:#9D7F6A;margin-bottom:2mm;}
      .qr-sec img{width:62mm;height:62mm;object-fit:contain;border-radius:2mm;}
      .qr-amt{font-size:4.5mm;font-weight:900;color:#E12717;margin-top:2mm;}
      .qr-note{font-size:2.5mm;color:#9D7F6A;margin-top:1mm;}
      .footer{text-align:center;font-size:2.8mm;color:#C4A98A;margin-top:3mm;padding-top:2.5mm;border-top:0.3mm dashed #C4A98A;}
    </style>
  </head><body>
    <div class="center">
      <p class="logo">🔥 ${rName}</p>
      ${settings.cuisine?`<p class="sub">${settings.cuisine}</p>`:''}
    </div>
    <p class="info">โต๊ะ ${tid}${t.guests?' · '+t.guests+' คน':''} · ${now}</p>
    <hr class="dash"/>
    <table><tbody>${rows}</tbody></table>
    <hr class="dash"/>
    <div class="tot"><span>ยอดอาหาร</span><span>฿${sub.toLocaleString()}</span></div>
    <div class="tot"><span>VAT 7%</span><span>฿${vat.toLocaleString()}</span></div>
    <div class="grand"><span class="lbl">รวมต้องชำระ</span><span class="amt">฿${total.toLocaleString()}</span></div>
    ${qrUrl?`
    <div class="qr-sec">
      <p class="hint">📱 สแกน QR พร้อมเพย์เพื่อชำระเงิน</p>
      <img src="${qrUrl}" alt="QR พร้อมเพย์"/>
      <p class="qr-amt">฿${total.toLocaleString()}</p>
      <p class="qr-note">กรุณากรอกยอดในแอปธนาคาร</p>
    </div>`:''}
    <p class="footer">ขอบคุณที่ใช้บริการครับ/ค่ะ 🙏</p>
    <script>window.onload=function(){ ${qrUrl?'setTimeout(function(){window.print();},400);':'window.print();'} };<\/script>
  </body></html>`);
  win.document.close();
}

/* ─── QR Code ─── */
let currentQrTid = null;

function showQR(tid){
  currentQrTid = tid;
  // Build menu URL dynamically from current host
  const base = window.location.origin + window.location.pathname.replace('cashier.php','');
  const menuUrl = base + 'menu.php?table=' + encodeURIComponent(tid);

  $('#qr-table-label').text('โต๊ะ ' + tid);
  $('#qr-url-text').text(menuUrl);

  // Clear previous QR
  $('#qr-canvas').html('');

  // Generate QR code
  new QRCode(document.getElementById('qr-canvas'), {
    text: menuUrl,
    width: 200,
    height: 200,
    colorDark: '#2C1713',
    colorLight: '#FFF9F5',
    correctLevel: QRCode.CorrectLevel.H,
  });

  $('#qr-overlay').removeClass('hidden');
}

function closeQR(){
  $('#qr-overlay').addClass('hidden');
  currentQrTid = null;
}

function printQR(){
  const tid = currentQrTid;
  const base = window.location.origin + window.location.pathname.replace('cashier.php','');
  const menuUrl = base + 'menu.php?table=' + encodeURIComponent(tid);

  // Build a printable page (80mm thermal paper)
  const win = window.open('', '_blank', 'width=320,height=420');
  const canvas = document.querySelector('#qr-canvas canvas');
  const imgSrc = canvas ? canvas.toDataURL('image/png') : '';
  const rName = settings.restaurantName || 'แซ่บกลางซอย';
  win.document.write(`<!DOCTYPE html><html lang="th"><head>
    <meta charset="UTF-8"/>
    <title>QR โต๊ะ ${tid}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700;900&display=swap" rel="stylesheet"/>
    <style>
      *{margin:0;padding:0;box-sizing:border-box;}
      @page{size:80mm auto;margin:0;}
      body{font-family:'Sarabun',sans-serif;background:#fff;width:80mm;padding:5mm 3mm 6mm;margin:0 auto;text-align:center;}
      .logo{font-size:5mm;font-weight:900;color:#2C1713;margin-bottom:1mm;}
      .sub{font-size:3mm;color:#9D7F6A;margin-bottom:5mm;}
      .qr-wrap{border:0.5mm solid #F0E0D4;border-radius:4mm;padding:3mm;background:#FFF9F5;display:inline-block;margin-bottom:4mm;}
      .qr-wrap img{width:62mm;height:62mm;display:block;}
      .table-num{font-size:13mm;font-weight:900;color:#E12717;line-height:1;margin-bottom:1mm;}
      .instruction{font-size:3.5mm;color:#5A4338;margin-bottom:3mm;}
      .url{font-size:2.3mm;color:#C8A48B;word-break:break-all;margin-top:2mm;}
    </style>
  </head><body>
    <p class="logo">🔥 ${rName}</p>
    <p class="sub">สแกน QR เพื่อสั่งอาหาร</p>
    <div class="qr-wrap">
      ${imgSrc?`<img src="${imgSrc}" alt="QR Code"/>`:`<p style="width:62mm;height:62mm;display:flex;align-items:center;justify-content:center;color:#E12717;font-size:3mm">QR Code</p>`}
    </div>
    <p class="table-num">โต๊ะ ${tid}</p>
    <p class="instruction">📱 สแกน QR นี้เพื่อดูเมนูและสั่งอาหาร</p>
    <p class="url">${menuUrl}</p>
    <script>window.onload=function(){ setTimeout(function(){ window.print(); },500); };<\/script>
  </body></html>`);
  win.document.close();
  win.focus();
}

/* ─── Events ─── */
$('#zone-tabs').on('click','.zone-tab',function(){
  activeZone=$(this).data('zone'); renderGrid();
});
$('#refreshBtn').on('click',loadAll);

// Close QR when clicking outside
$('#qr-overlay').on('click',function(e){
  if($(e.target).is('#qr-overlay')) closeQR();
});

/* ─── Item edit controls (event delegation on #sheet) ─── */
$('#sheet').on('click','.ci-del',function(){
  const row=$(this).closest('.item-row');
  cashierDeleteItem(row.data('oid'), row.data('mid'));
});
$('#sheet').on('click','.ci-dec',function(){
  const row=$(this).closest('.item-row');
  const cur=parseInt(row.find('.item-qty').text())||1;
  cashierAdjustQty(row.data('oid'), row.data('mid'), cur-1);
});
$('#sheet').on('click','.ci-inc',function(){
  const row=$(this).closest('.item-row');
  const cur=parseInt(row.find('.item-qty').text())||1;
  cashierAdjustQty(row.data('oid'), row.data('mid'), cur+1);
});

/* ─── Picker events ─── */
$('#picker-search').on('input',function(){ pickerSearch=$(this).val().trim(); renderPickerMenu(); });
$('#picker-panel').on('click','.picker-cat-btn',function(){
  pickerActiveCat=$(this).data('pcat'); renderPickerCats(); renderPickerMenu();
});
$('#picker-panel').on('click','.picker-add',function(){
  const mid=$(this).data('pid'), item=menuItems.find(m=>m.id===mid); if(!item) return;
  if(!pickerCart[mid]) pickerCart[mid]={item,qty:0,note:''};
  pickerCart[mid].qty=Math.min(99,pickerCart[mid].qty+1);
  renderPickerMenu(); renderPickerFooter();
});
$('#picker-panel').on('click','.picker-inc',function(){
  const mid=$(this).data('pid');
  if(pickerCart[mid]){ pickerCart[mid].qty=Math.min(99,pickerCart[mid].qty+1); renderPickerMenu(); renderPickerFooter(); }
});
$('#picker-panel').on('click','.picker-dec',function(){
  const mid=$(this).data('pid');
  if(!pickerCart[mid]) return;
  pickerCart[mid].qty=Math.max(0,pickerCart[mid].qty-1);
  if(pickerCart[mid].qty===0) delete pickerCart[mid];
  renderPickerMenu(); renderPickerFooter();
});
$('#picker-panel').on('input','.picker-note',function(){
  const mid=$(this).data('pid');
  if(pickerCart[mid]) pickerCart[mid].note=$(this).val().trim();
  renderPickerFooter();
});

/* ─── Cashier item edit functions ─── */
function cashierAdjustQty(orderId, menuId, newQty){
  if(newQty<=0){ cashierDeleteItem(orderId,menuId); return; }
  $.ajax({url:'api/order_item.php',method:'PATCH',contentType:'application/json',
    data:JSON.stringify({orderId,menuId,quantity:newQty}),
    success:()=>loadAll().then(()=>{ if(selectedTableId) showSheet(selectedTableId); }),
    error:()=>alert('แก้ไขไม่สำเร็จ')
  });
}
function cashierDeleteItem(orderId, menuId){
  $.ajax({url:'api/order_item.php',method:'DELETE',contentType:'application/json',
    data:JSON.stringify({orderId,menuId}),
    success:()=>loadAll().then(()=>{ if(selectedTableId) showSheet(selectedTableId); }),
    error:()=>alert('ลบไม่สำเร็จ')
  });
}

/* ─── Menu Picker functions ─── */
function showMenuPicker(tid){
  pickerTid=tid; pickerActiveCat='ทั้งหมด'; pickerSearch=''; pickerCart={};
  $('#picker-table-label').text(tid);
  $('#picker-search').val('');
  renderPickerCats(); renderPickerMenu(); renderPickerFooter();
  $('#picker-overlay').removeClass('hidden');
  $('#picker-panel').addClass('open');
}
function closePicker(){
  $('#picker-overlay').addClass('hidden');
  $('#picker-panel').removeClass('open');
  pickerTid=null;
}

function renderPickerCats(){
  const cats=['ทั้งหมด',...new Set(menuItems.map(m=>m.category))];
  $('#picker-cats').html(cats.map(c=>`
    <button type="button" data-pcat="${escHtml(c)}"
      class="picker-cat-btn shrink-0 rounded-full px-3.5 py-1.5 text-[11px] font-medium transition-colors
        ${c===pickerActiveCat?'btn-red text-white shadow-[0_4px_10px_rgba(225,39,23,0.2)]':'bg-white text-[#7C5B47] ring-1 ring-[#F0E0D4]'}">
      ${escHtml(c)}
    </button>`).join(''));
}

function renderPickerMenu(){
  const q=pickerSearch.toLowerCase();
  const filtered=menuItems.filter(m=>{
    if(pickerActiveCat!=='ทั้งหมด' && m.category!==pickerActiveCat) return false;
    if(q && !(m.name+' '+m.category).toLowerCase().includes(q)) return false;
    return true;
  });
  if(!filtered.length){
    $('#picker-items').html('<p class="py-10 text-center text-[12px] text-[#9D7F6A]">ไม่พบเมนู</p>');
    return;
  }
  const tagMap={เผ็ด:'bg-red-50 text-red-600',ฮิต:'bg-green-50 text-green-700',โปร:'bg-amber-50 text-amber-700'};
  $('#picker-items').html(filtered.map(m=>{
    const entry=pickerCart[m.id], qty=entry?entry.qty:0;
    const tagHtml=m.tag?`<span class="shrink-0 rounded-full px-1.5 py-0.5 text-[9px] font-bold ${tagMap[m.tag]||''}">${escHtml(m.tag)}</span>`:'';
    return `<div class="rounded-[18px] bg-white ring-1 ring-[#F0E0D4] overflow-hidden">
      <div class="flex items-center gap-3 px-4 py-3">
        <img src="${escHtml(m.image)||'https://placehold.co/48x48/F7EFE7/9D7F6A?text=🍽'}"
          alt="${escHtml(m.name)}" class="h-12 w-12 shrink-0 rounded-xl object-cover bg-[#F7EFE7]"
          onerror="this.src='https://placehold.co/48x48/F7EFE7/9D7F6A?text=🍽'"/>
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-1.5">
            <p class="text-[13px] font-semibold text-[#2C1713] truncate">${escHtml(m.name)}</p>
            ${tagHtml}
          </div>
          <p class="text-[11px] text-[#9D7F6A]">${escHtml(m.category)}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <span class="text-[13px] font-semibold text-[#E12717] tabular-nums">฿${m.price.toLocaleString('th-TH')}</span>
          ${qty>0?`
            <div class="flex items-center gap-0.5 rounded-full bg-[#F7EFE7] p-0.5">
              <button type="button" data-pid="${escHtml(m.id)}" class="picker-dec flex h-7 w-7 items-center justify-center rounded-full bg-white text-[#5A4338] text-[14px] font-bold shadow-sm leading-none">−</button>
              <span class="min-w-[18px] text-center text-[12px] font-bold tabular-nums">${qty}</span>
              <button type="button" data-pid="${escHtml(m.id)}" class="picker-inc flex h-7 w-7 items-center justify-center rounded-full bg-white text-[#5A4338] text-[14px] font-bold shadow-sm leading-none">+</button>
            </div>
          `:`
            <button type="button" data-pid="${escHtml(m.id)}"
              class="picker-add flex h-8 w-8 items-center justify-center rounded-full btn-red text-white shadow-[0_4px_10px_rgba(225,39,23,0.25)]">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </button>
          `}
        </div>
      </div>
      ${qty>0?`<div class="px-4 pb-3">
        <input type="text" data-pid="${escHtml(m.id)}" placeholder="โน้ตพิเศษ เช่น เผ็ดน้อย ไม่ใส่ผักชี..."
          class="picker-note w-full rounded-xl border border-[#F0E0D4] bg-[#FFF9F5] px-3.5 py-2 text-[12px] text-[#2C1713] placeholder:text-[#C4A98A] outline-none focus:border-[#E12717]"
          value="${escHtml(entry?.note||'')}"/>
      </div>`:''}
    </div>`;
  }).join(''));
}

function renderPickerFooter(){
  const items=Object.values(pickerCart).filter(e=>e.qty>0);
  if(!items.length){
    $('#picker-cart-info').addClass('hidden');
    $('#picker-submit-btn').prop('disabled',true).addClass('opacity-40');
    return;
  }
  const total=items.reduce((s,e)=>s+e.item.price*e.qty,0);
  $('#picker-cart-items').html(items.map(e=>`
    <div class="flex justify-between gap-2">
      <span class="truncate">${escHtml(e.item.name)}${e.note?` <span class="text-[#9D7F6A]">(${escHtml(e.note)})</span>`:''}</span>
      <span class="tabular-nums shrink-0">×${e.qty}</span>
    </div>`).join(''));
  $('#picker-cart-total').text(fmtMoney(total));
  $('#picker-cart-info').removeClass('hidden');
  $('#picker-submit-btn').prop('disabled',false).removeClass('opacity-40');
}

function pickerSubmit(){
  if(!pickerTid) return;
  const items=Object.values(pickerCart).filter(e=>e.qty>0).map(e=>({
    menuId:e.item.id, name:e.item.name, price:e.item.price,
    quantity:e.qty, note:e.note||null,
  }));
  if(!items.length) return;
  const btn=$('#picker-submit-btn');
  btn.prop('disabled',true).text('กำลังส่ง...');
  $.ajax({url:'api/orders.php',method:'POST',contentType:'application/json',
    data:JSON.stringify({tableId:pickerTid,items}),
    success:function(){
      const tid=pickerTid;
      closePicker();
      loadAll().then(()=>showSheet(tid));
    },
    error:function(){
      btn.prop('disabled',false).text('ส่งออเดอร์ให้ครัว');
      alert('ส่งออเดอร์ไม่สำเร็จ');
    }
  });
}

/* ─── Init + auto-poll every 30s ─── */
loadAll();
setInterval(loadAll,30000);
</script>
</body>
</html>
