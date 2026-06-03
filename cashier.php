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

<script>
const VAT_RATE = 0.07;
let tables=[], orders=[], menuItems=[], settings={};
let activeZone='all', selectedTableId=null;

function fmtMoney(n){ return '฿'+Number(n).toLocaleString('th-TH'); }

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
  const allItems=[]; tableOrds.forEach(o=>o.items.forEach(i=>allItems.push(i)));
  const subtotal=allItems.reduce((sum,i)=>sum+i.price*i.quantity,0);
  const vat=Math.round(subtotal*VAT_RATE); const total=subtotal+vat;

  const itemsHtml = allItems.length
    ? allItems.map(i=>`<div class="flex items-start justify-between gap-2 py-2 border-b border-[#F7EFE7] last:border-0">
        <div class="min-w-0">
          <p class="text-[13px] font-medium text-[#2C1713]">${i.name}</p>
          ${i.note?`<p class="text-[11px] text-[#9D7F6A]">${i.note}</p>`:''}
        </div>
        <div class="text-right shrink-0">
          <p class="text-[13px] font-semibold text-[#2C1713] tabular-nums">${fmtMoney(i.price*i.quantity)}</p>
          <p class="text-[10px] text-[#9D7F6A] tabular-nums">${fmtMoney(i.price)}×${i.quantity}</p>
        </div>
      </div>`).join('')
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

/* ─── Bill confirm screen ─── */
let pendingCloseTid=null;
function showBillConfirm(tid){
  pendingCloseTid=tid;
  const tableOrds=orders.filter(o=>o.tableId===tid);
  const allItems=[]; tableOrds.forEach(o=>o.items.forEach(i=>allItems.push(i)));
  const sub=allItems.reduce((s,i)=>s+i.price*i.quantity,0);
  const vat=Math.round(sub*VAT_RATE); const total=sub+vat;

  $('#sheet-content').html(`
    <div class="flex items-center justify-between mb-4">
      <div>
        <p class="text-[18px] font-bold text-[#2C1713]">ยืนยันปิดบิล</p>
        <p class="text-[12px] text-[#9D7F6A]">โต๊ะ ${tid}</p>
      </div>
      <button onclick="showSheet('${tid}')" class="flex h-9 w-9 items-center justify-center rounded-full bg-[#F7EFE7] text-[#5A4338]">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <!-- Amount card -->
    <div class="rounded-[20px] bg-gradient-to-br from-[#FFF5F0] to-[#FFE9DE] p-4 ring-1 ring-[#F2D1BD]/50 mb-5">
      <div class="flex justify-between text-[12px] text-[#7C5B47]"><span>ยอดอาหาร</span><span class="tabular-nums">${fmtMoney(sub)}</span></div>
      <div class="flex justify-between text-[12px] text-[#7C5B47] mt-1"><span>VAT 7%</span><span class="tabular-nums">${fmtMoney(vat)}</span></div>
      <div class="flex items-end justify-between mt-3 pt-2.5 border-t border-[#EFD9CC]">
        <span class="text-[13px] font-semibold text-[#2C1713]">รวมต้องชำระ</span>
        <span class="text-[24px] font-extrabold text-[#E12717] tabular-nums leading-none">${fmtMoney(total)}</span>
      </div>
    </div>
    <!-- Payment method -->
    <p class="text-[11px] font-semibold uppercase tracking-wide text-[#A98671] mb-3">เลือกวิธีชำระ</p>
    <div class="grid grid-cols-2 gap-2.5 mb-5">
      <label class="relative cursor-pointer rounded-[18px] border-2 border-[#E12717] bg-gradient-to-br from-white to-[#FFF5F0] p-4">
        <input type="radio" name="pm" value="transfer" class="sr-only" checked/>
        <p class="text-[22px] mb-1">📱</p>
        <p class="text-[13px] font-bold text-[#2C1713]">พร้อมเพย์ / โอน</p>
        <p class="text-[10px] text-[#9D7F6A] mt-0.5">สแกนหรือโอนเงิน</p>
        <span class="absolute right-2.5 top-2.5 h-5 w-5 flex items-center justify-center rounded-full bg-[#E12717] text-white text-[10px]">✓</span>
      </label>
      <label class="relative cursor-pointer rounded-[18px] border-2 border-[#F0E0D4] bg-white p-4 cash-label">
        <input type="radio" name="pm" value="cash" class="sr-only"/>
        <p class="text-[22px] mb-1">💵</p>
        <p class="text-[13px] font-bold text-[#2C1713]">เงินสด</p>
        <p class="text-[10px] text-[#9D7F6A] mt-0.5">รับที่โต๊ะ</p>
        <span class="absolute right-2.5 top-2.5 h-5 w-5 hidden items-center justify-center rounded-full bg-[#E12717] text-white text-[10px] cash-check">✓</span>
      </label>
    </div>
    <!-- Cash received input (hidden) -->
    <div id="cash-input-row" class="hidden mb-4">
      <label class="text-[12px] text-[#9D7F6A] mb-1.5 block">รับเงินมา (บาท)</label>
      <input id="cash-recv" type="number" min="0" placeholder="${total}"
        class="w-full rounded-xl border border-[#F0E0D4] px-4 py-3 text-[16px] font-bold text-center text-[#2C1713] outline-none focus:border-[#E12717]"/>
      <p id="change-preview" class="mt-1.5 text-[12px] text-emerald-600 font-medium text-center hidden"></p>
    </div>
    <!-- Buttons -->
    <div class="flex gap-2">
      <button onclick="showSheet('${tid}')" class="flex-1 rounded-[18px] border border-[#E8D6C6] bg-white py-3.5 text-[13px] font-semibold text-[#2C1713]">ยกเลิก</button>
      <button onclick="doCloseBill()" class="flex-[1.6] rounded-[18px] py-3.5 text-[14px] font-bold text-white btn-red shadow-[0_12px_24px_rgba(225,39,23,0.28)]">
        ยืนยันปิดบิล →
      </button>
    </div>
  `);

  /* Payment radio logic */
  $('input[name=pm]').on('change',function(){
    const isCash=$(this).val()==='cash';
    $('#cash-input-row').toggleClass('hidden',!isCash);
    // border styling
    $('input[name=pm]').each(function(){
      const sel=$(this).is(':checked');
      $(this).closest('label').toggleClass('border-[#E12717]',sel).toggleClass('border-[#F0E0D4]',!sel);
      $(this).closest('label').find('.cash-check,.absolute.right-2\\.5').toggleClass('hidden',!sel).toggleClass('flex',sel);
    });
  });
  /* Change preview */
  $('#cash-recv').on('input',function(){
    const recv=parseFloat($(this).val())||0; const change=recv-total;
    if(recv>0){ $('#change-preview').text(change>=0?`เงินทอน ${fmtMoney(change)}`:'รับเงินไม่พอ').removeClass('hidden').toggleClass('text-emerald-600',change>=0).toggleClass('text-red-500',change<0); }
    else $('#change-preview').addClass('hidden');
  });
}

function doCloseBill(){
  const pm=$('input[name=pm]:checked').val()||'transfer';
  const cr=pm==='cash'?(parseFloat($('#cash-recv').val())||null):null;
  $.ajax({url:'api/table.php?id='+encodeURIComponent(pendingCloseTid),method:'PATCH',
    contentType:'application/json',
    data:JSON.stringify({action:'close',paymentMethod:pm,cashReceived:cr}),
    success:function(){ closeSheet(); loadAll(); },
    error:function(){ alert('ปิดบิลไม่สำเร็จ'); }
  });
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

  // Build a printable page
  const win = window.open('', '_blank', 'width=400,height=500');
  // Get QR canvas image
  const canvas = document.querySelector('#qr-canvas canvas');
  const imgSrc = canvas ? canvas.toDataURL('image/png') : '';
  win.document.write(`<!DOCTYPE html><html lang="th"><head>
    <meta charset="UTF-8"/>
    <title>QR โต๊ะ ${tid}</title>
    <style>
      *{margin:0;padding:0;box-sizing:border-box;}
      body{font-family:'Sarabun',sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;background:#fff;padding:32px 20px;text-align:center;}
      .logo{font-size:28px;font-weight:900;color:#2C1713;margin-bottom:4px;}
      .sub{font-size:13px;color:#9D7F6A;margin-bottom:24px;}
      .qr-wrap{border:2px solid #F0E0D4;border-radius:20px;padding:16px;background:#FFF9F5;margin-bottom:20px;}
      img{width:200px;height:200px;}
      .table-num{font-size:42px;font-weight:900;color:#E12717;margin-bottom:4px;}
      .label{font-size:14px;color:#7C5B47;margin-bottom:16px;}
      .url{font-size:10px;color:#C8A48B;word-break:break-all;max-width:260px;}
      .instruction{font-size:13px;color:#5A4338;margin-top:8px;}
      @media print{body{padding:16px;}}
    </style>
  </head><body>
    <p class="logo">🔥 แซ่บกลางซอย</p>
    <p class="sub">สแกน QR เพื่อสั่งอาหาร</p>
    <div class="qr-wrap">
      ${imgSrc ? `<img src="${imgSrc}" alt="QR Code"/>` : `<p style="color:#E12717;font-size:12px">QR Code</p>`}
    </div>
    <p class="table-num">โต๊ะ ${tid}</p>
    <p class="instruction">📱 สแกน QR นี้เพื่อดูเมนูและสั่งอาหาร</p>
    <p class="url" style="margin-top:12px">${menuUrl}</p>
  </body></html>`);
  win.document.close();
  win.focus();
  setTimeout(()=>{ win.print(); }, 600);
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

/* ─── Init + auto-poll every 30s ─── */
loadAll();
setInterval(loadAll,30000);
</script>
</body>
</html>
