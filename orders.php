<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>ออเดอร์ · แซ่บกลางซอย</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>
body{font-family:'Sarabun',sans-serif;background:#FFF9F5;}
.btn-red{background:linear-gradient(135deg,#FF5546,#F23A2B,#C41E0E);}
/* iOS safe area */
.safe-bottom{padding-bottom:max(20px,env(safe-area-inset-bottom));}
.overlay{position:fixed;inset:0;background:rgba(0,0,0,0.55);backdrop-filter:blur(2px);z-index:50;}
.sheet{position:fixed;left:50%;bottom:0;transform:translateX(-50%);z-index:51;background:#fff;border-radius:32px 32px 0 0;box-shadow:0 -20px 50px rgba(0,0,0,0.28);max-height:90vh;overflow-y:auto;width:100%;max-width:600px;}
</style>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet"/>
</head>
<body class="mx-auto flex min-h-screen max-w-sm flex-col bg-[#FFF9F5]">

<!-- Header -->
<div class="sticky top-0 z-30 border-b border-[#F0E2D4]/60 bg-[#FFF9F5]/85 px-5 py-4 backdrop-blur-md">
  <div class="flex items-center gap-3">
    <a id="back-link" href="index.php" class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-[#2C1713] shadow-[0_4px_12px_rgba(44,23,19,0.06)] ring-1 ring-black/5 transition-transform active:scale-95">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
    </a>
    <div class="flex-1">
      <p id="table-label" class="text-[10px] font-medium uppercase tracking-[0.22em] text-[#B89A82]">กำลังโหลด...</p>
      <h1 class="text-[17px] font-semibold text-[#2C1713]">รายการที่สั่งแล้ว</h1>
    </div>
    <div id="item-count-badge" class="flex h-10 items-center justify-center rounded-2xl bg-white px-3 text-[12px] font-medium text-[#2C1713] shadow-[0_4px_12px_rgba(44,23,19,0.06)] ring-1 ring-black/5">—</div>
  </div>
</div>

<!-- Main content -->
<section class="px-5 pt-5" id="receipt-section">
  <div class="py-12 text-center text-[#9D7F6A]">กำลังโหลดออเดอร์...</div>
</section>

<!-- Billing requested banner -->
<section class="mx-5 mt-4 hidden" id="billing-banner">
  <div class="overflow-hidden rounded-[18px] bg-gradient-to-br from-[#FFF6E1] to-[#FFEFCC] p-4 ring-1 ring-[#F5D89A]/60">
    <div class="flex items-start gap-3">
      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/80 text-[#B97A12] ring-1 ring-[#F5D89A]/80">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/></svg>
      </div>
      <div class="flex-1">
        <p class="text-[13px] font-semibold text-[#7A5300]">ส่งคำขอปิดบิลแล้ว</p>
        <p class="mt-1 text-[11px] leading-5 text-[#8B6B35]">
          พนักงานจะเข้ามาที่โต๊ะเพื่อรับชำระด้วย <span id="payment-label" class="font-medium"></span> หากต้องการสั่งเพิ่ม สามารถยกเลิกคำขอได้ก่อน
        </p>
        <button onclick="cancelBilling()" class="mt-3 rounded-full bg-white/90 px-3 py-1.5 text-[11px] font-medium text-[#7A5300] ring-1 ring-[#F1D091]">
          ยกเลิกคำขอปิดบิล
        </button>
      </div>
    </div>
  </div>
</section>

<div class="h-36"></div>

<!-- Bottom actions -->
<div class="fixed inset-x-0 bottom-0 z-40">
  <div class="mx-auto max-w-sm">
    <div class="pointer-events-none h-6 bg-gradient-to-t from-[#FFF9F5] to-transparent"></div>
    <div class="bg-[#FFF9F5] px-5 pt-2 safe-bottom" id="bottom-actions">
      <!-- filled by JS -->
    </div>
  </div>
</div>

<!-- Payment sheet overlay -->
<div id="overlay" class="overlay hidden" onclick="closeSheet()"></div>
<div id="sheet" class="sheet hidden">
  <div class="flex justify-center pt-3"><div class="h-1 w-10 rounded-full bg-[#E8D6C6]"></div></div>
  <div id="sheet-content" class="px-5 pb-8 pt-3"></div>
</div>

<script>
const CART_KEY = 'zab_cart';
const TEAR_BG = `url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 14 12'><polygon points='0,0 14,0 14,4 7,12 0,4' fill='%23FCF7EF'/></svg>")`;
const POLL_MS = 10000;

let tableId = '';
let orders = [];
let billingRequested = false;
let selectedPayment = 'promptpay';
let pollTimer = null;

function fmtMoney(n){ return '฿'+Number(n).toLocaleString('th-TH'); }

// Read tableId from localStorage (set by menu.php)
function initTableId(){
  try{
    const d = JSON.parse(localStorage.getItem(CART_KEY)||'{}');
    tableId = d.tableId||'';
  }catch(e){}
  // Also support ?table= URL param
  const urlParams = new URLSearchParams(window.location.search);
  if(urlParams.get('table')) tableId = urlParams.get('table').toUpperCase();

  if(tableId){
    $('#table-label').text('โต๊ะ '+tableId);
    $('#back-link').attr('href','menu.php?table='+encodeURIComponent(tableId));
  }
}

function loadOrders(){
  if(!tableId) return;
  $.getJSON('api/orders.php?tableId='+encodeURIComponent(tableId), function(data){
    orders = data;
    renderReceipt();
    renderBottomActions();
  }).fail(function(){
    $('#receipt-section').html('<div class="py-8 text-center text-red-500">โหลดออเดอร์ไม่สำเร็จ</div>');
  });
}

function renderReceipt(){
  // Flatten all items from all orders
  const allItems = [];
  orders.forEach(function(o){
    o.items.forEach(function(item){
      // Mark as "served" if order is printed, else "queued/preparing"
      allItems.push({
        name: item.name,
        note: item.note||'',
        price: item.price,
        quantity: item.quantity,
        orderedAt: o.orderedAt,
        status: o.printed ? 'served' : 'queued',
      });
    });
  });

  const totalItems = allItems.reduce((s,i)=>s+i.quantity,0);
  $('#item-count-badge').text(totalItems+' ชิ้น');

  if(!allItems.length){
    $('#receipt-section').html(`
      <div class="rounded-[22px] border border-dashed border-[#E8D6C6] bg-white px-5 py-10 text-center">
        <p class="text-[14px] font-semibold text-[#2C1713]">ยังไม่มีออเดอร์</p>
        <p class="mt-1 text-[12px] text-[#9D7F6A]">กลับไปเลือกเมนูก่อนนะคะ</p>
        <a href="${tableId?'menu.php?table='+encodeURIComponent(tableId):'index.php'}"
          class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-[#2C1713] px-5 py-2.5 text-[12px] font-medium text-white">
          ดูเมนู →
        </a>
      </div>`);
    return;
  }

  // Group by status
  const sections = [
    {key:'queued',   label:'เพิ่งส่งเข้าครัว', icon:'🕐', color:'text-[#B97A12]'},
    {key:'preparing',label:'กำลังทำ',           icon:'👨‍🍳', color:'text-[#C53A2B]'},
    {key:'served',   label:'เสิร์ฟแล้ว',       icon:'✅', color:'text-[#5C8E25]'},
  ];

  const subtotal = allItems.reduce((s,i)=>s+i.price*i.quantity,0);
  const vat = Math.round(subtotal*0.07);
  const total = subtotal+vat;

  const now = new Date().toLocaleString('th-TH',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit',hour12:false});

  let sectionsHtml = '';
  sections.forEach(function(sec){
    const items = allItems.filter(i=>i.status===sec.key);
    if(!items.length) return;
    sectionsHtml += `<div>
      <div class="flex items-center gap-1.5 mb-2">
        <span class="text-[13px]">${sec.icon}</span>
        <span class="text-[10px] font-medium uppercase tracking-[0.16em] ${sec.color}">${sec.label}</span>
        <span class="text-[10px] text-[#C8A48B]">· ${items.length} รายการ</span>
      </div>
      <div class="space-y-3">
        ${items.map(item=>`<div>
          <div class="flex items-start justify-between gap-3">
            <p class="text-[13px] font-medium leading-snug text-[#2C1713]">${item.name}</p>
            <span class="shrink-0 text-[13px] font-semibold text-[#2C1713] tabular-nums">${fmtMoney(item.price*item.quantity)}</span>
          </div>
          ${item.note?`<p class="mt-0.5 text-[11px] leading-snug text-[#9D7F6A]">${item.note}</p>`:''}
          <p class="mt-1 text-[10px] text-[#9D7F6A] tabular-nums">
            ${fmtMoney(item.price)} × ${item.quantity}
            <span class="text-[#C8A48B]">· ส่งครัว ${item.orderedAt}</span>
          </p>
        </div>`).join('')}
      </div>
    </div>`;
  });

  $('#receipt-section').html(`
    <div style="filter:drop-shadow(0 10px 24px rgba(44,23,19,0.07))">
      <div class="bg-[#FCF7EF] px-6 pt-7 pb-5 rounded-t-[4px]">
        <div class="flex flex-col items-center">
          <img src="logo.png" alt="แซ่บกลางซอย" class="h-24 w-24 object-contain" onerror="this.style.display='none'"/>
        </div>
        <div class="mt-5 flex items-center gap-2.5">
          <div class="h-px flex-1 border-t border-dashed border-[#D9C4B0]"></div>
          <span class="text-[8px] text-[#C8A48B]">◆ ◆ ◆</span>
          <div class="h-px flex-1 border-t border-dashed border-[#D9C4B0]"></div>
        </div>
        <dl class="mt-4 space-y-1.5 text-[11px]">
          <div class="flex justify-between"><dt class="text-[#9D7F6A]">โต๊ะ</dt><dd class="font-medium text-[#2C1713]">${tableId||'—'}</dd></div>
          <div class="flex justify-between"><dt class="text-[#9D7F6A]">วัน-เวลา</dt><dd class="font-medium tabular-nums text-[#2C1713]">${now}</dd></div>
        </dl>
        <div class="mt-4 border-t border-dashed border-[#D9C4B0] pt-3">
          <div class="flex items-center justify-between text-[9px] font-medium uppercase tracking-[0.2em] text-[#B89A82]">
            <span>รายการ · Item</span><span>ยอด · Amount</span>
          </div>
        </div>

        <!-- Items grouped by status -->
        <div class="mt-3 space-y-5">${sectionsHtml}</div>

        <!-- Bill breakdown -->
        <div class="mt-5 border-t border-dashed border-[#D9C4B0] pt-3">
          <div class="flex items-center justify-between text-[12px]">
            <span class="text-[#9D7F6A]">ยอดอาหาร</span>
            <span class="font-medium text-[#5A4338] tabular-nums">${fmtMoney(subtotal)}</span>
          </div>
          <div class="mt-1.5 flex items-center justify-between text-[12px]">
            <span class="text-[#9D7F6A]">VAT 7%</span>
            <span class="font-medium text-[#5A4338] tabular-nums">${fmtMoney(vat)}</span>
          </div>
        </div>
        <div class="mt-3 border-t-2 border-double border-[#D9C4B0] pt-3">
          <div class="flex items-baseline justify-between">
            <span class="text-[13px] font-semibold text-[#2C1713]">รวมต้องชำระ</span>
            <span class="text-[24px] font-bold leading-none text-[#E12717] tabular-nums">${fmtMoney(total)}</span>
          </div>
        </div>

        <!-- Live update indicator -->
        <div class="mt-4 flex items-center justify-center gap-1.5">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          <span class="text-[10px] text-[#9D7F6A]">อัพเดทสถานะอัตโนมัติทุก 10 วินาที</span>
        </div>

        <div class="mt-4 flex items-center gap-2.5">
          <div class="h-px flex-1 border-t border-dashed border-[#D9C4B0]"></div>
          <span class="text-[8px] tracking-[0.3em] text-[#C8A48B]">THANK YOU</span>
          <div class="h-px flex-1 border-t border-dashed border-[#D9C4B0]"></div>
        </div>
        <p class="mt-2 text-center text-[10px] text-[#9D7F6A]">ขอบคุณที่อุดหนุนค่ะ</p>
      </div>
      <div class="block h-3 w-full" style="background-image:${TEAR_BG};background-size:14px 12px;background-repeat:repeat-x" aria-hidden="true"></div>
    </div>`);

  // Store total for billing sheet
  window._orderTotal = total;
  window._orderSubtotal = subtotal;
  window._orderVat = vat;
}

function renderBottomActions(){
  if(billingRequested){
    $('#billing-banner').removeClass('hidden');
    $('#bottom-actions').html(`
      <button type="button" disabled
        class="flex w-full items-center justify-center gap-2 rounded-[20px] bg-gradient-to-br from-[#E5C481] to-[#C9A45D] py-4 text-[14px] font-semibold text-white shadow-[0_12px_24px_rgba(201,164,93,0.32)]">
        🕐 รอพนักงานมาปิดบิล
      </button>`);
  } else {
    $('#billing-banner').addClass('hidden');
    const menuHref = tableId?`menu.php?table=${encodeURIComponent(tableId)}`:'index.php';
    const total = window._orderTotal||0;
    $('#bottom-actions').html(`
      <div class="flex gap-2">
        <a href="${menuHref}"
          class="flex flex-1 items-center justify-center gap-1.5 rounded-[20px] border border-[#E8D6C6] bg-white py-4 text-[13px] font-medium text-[#2C1713] shadow-[0_4px_12px_rgba(44,23,19,0.04)]">
          + สั่งเพิ่ม
        </a>
        <button type="button" onclick="openBillingSheet()"
          class="relative flex flex-[1.4] items-center justify-center gap-2 overflow-hidden rounded-[20px] py-4 text-[14px] font-semibold text-white shadow-[0_16px_32px_rgba(225,39,23,0.32)] btn-red">
          <span class="pointer-events-none absolute inset-x-0 top-0 h-1/2 bg-gradient-to-b from-white/16 to-transparent"></span>
          <span class="relative">ปิดบิล · ${fmtMoney(total)}</span>
          <svg class="relative" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </button>
      </div>`);
  }
}

function openBillingSheet(){
  const sub = window._orderSubtotal||0;
  const vat = window._orderVat||0;
  const total = window._orderTotal||0;

  $('#sheet-content').html(`
    <div class="mb-4 flex items-start justify-between gap-4">
      <div>
        <p class="text-[17px] font-semibold text-[#2C1713]">ยืนยันปิดบิล</p>
        <p class="mt-1 text-[11px] leading-5 text-[#9D7F6A]">หลังยืนยัน ระบบจะเรียกพนักงานมาที่โต๊ะเพื่อรับชำระเงิน</p>
      </div>
      <button onclick="closeSheet()" class="flex h-9 w-9 items-center justify-center rounded-full bg-[#F7EFE7] text-[#2C1713] shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <!-- Amount summary -->
    <div class="relative overflow-hidden rounded-[22px] bg-gradient-to-br from-[#FFF5F0] to-[#FFE9DE] p-4 ring-1 ring-[#F2D1BD]/50 mb-5">
      <div class="flex items-center justify-between text-[12px] text-[#7C5B47]">
        <span>ยอดอาหาร</span><span class="font-medium tabular-nums">${fmtMoney(sub)}</span>
      </div>
      <div class="mt-1.5 flex items-center justify-between text-[12px] text-[#7C5B47]">
        <span>VAT 7%</span><span class="font-medium tabular-nums">${fmtMoney(vat)}</span>
      </div>
      <div class="my-3 h-px bg-[#EFD9CC]"></div>
      <div class="flex items-end justify-between">
        <span class="text-[13px] font-medium text-[#2C1713]">รวมต้องชำระ</span>
        <span class="text-[22px] font-semibold leading-none text-[#E12717] tabular-nums">${fmtMoney(total)}</span>
      </div>
    </div>

    <!-- Payment method -->
    <p class="mb-3 px-1 text-[10px] font-medium uppercase tracking-[0.22em] text-[#A98671]">เลือกวิธีชำระ</p>
    <div class="grid grid-cols-2 gap-2.5 mb-5">
      <button type="button" data-pm="promptpay" onclick="selectPayment('promptpay')"
        class="pm-btn relative rounded-[20px] border border-[#E12717] bg-gradient-to-br from-white to-[#FFF5F0] p-4 text-left shadow-[0_8px_20px_rgba(225,39,23,0.12)]">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#FFE5DE] text-[#E12717]">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/></svg>
        </div>
        <p class="mt-3 text-[13px] font-semibold text-[#2C1713]">พร้อมเพย์</p>
        <p class="mt-0.5 text-[10px] leading-4 text-[#9D7F6A]">สแกนชำระจากมือถือ</p>
        <span class="absolute right-3 top-3 flex h-5 w-5 items-center justify-center rounded-full bg-[#E12717] text-white check-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </span>
      </button>
      <button type="button" data-pm="counter" onclick="selectPayment('counter')"
        class="pm-btn relative rounded-[20px] border border-[#E8D6C6] bg-white p-4 text-left">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#F7EFE7] text-[#9D7F6A]">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg>
        </div>
        <p class="mt-3 text-[13px] font-semibold text-[#2C1713]">จ่ายที่เคาน์เตอร์</p>
        <p class="mt-0.5 text-[10px] leading-4 text-[#9D7F6A]">เรียกพนักงานมารับชำระ</p>
        <span class="absolute right-3 top-3 hidden flex h-5 w-5 items-center justify-center rounded-full bg-[#E12717] text-white check-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </span>
      </button>
    </div>

    <!-- Confirm buttons -->
    <div class="flex gap-2">
      <button type="button" onclick="closeSheet()"
        class="flex-1 rounded-[18px] border border-[#E8D6C6] bg-white py-3.5 text-[13px] font-medium text-[#2C1713] transition-transform active:scale-[0.98]">
        ยังไม่ปิด
      </button>
      <button type="button" onclick="requestBilling()"
        class="relative flex flex-[1.5] items-center justify-center gap-2 overflow-hidden rounded-[18px] py-3.5 text-[13px] font-semibold text-white shadow-[0_12px_24px_rgba(225,39,23,0.28)] btn-red">
        <span class="pointer-events-none absolute inset-x-0 top-0 h-1/2 bg-gradient-to-b from-white/16 to-transparent"></span>
        <span class="relative">ยืนยันปิดบิล</span>
        <svg class="relative" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
      </button>
    </div>`);

  $('#overlay').removeClass('hidden');
  $('#sheet').removeClass('hidden');
  selectedPayment = 'promptpay';
}

function selectPayment(pm){
  selectedPayment = pm;
  $('.pm-btn').each(function(){
    const thisPm = $(this).data('pm');
    if(thisPm===pm){
      $(this).addClass('border-[#E12717] bg-gradient-to-br from-white to-[#FFF5F0] shadow-[0_8px_20px_rgba(225,39,23,0.12)]')
             .removeClass('border-[#E8D6C6] bg-white');
      $(this).find('.check-icon').removeClass('hidden');
    } else {
      $(this).removeClass('border-[#E12717] bg-gradient-to-br from-white to-[#FFF5F0] shadow-[0_8px_20px_rgba(225,39,23,0.12)]')
             .addClass('border-[#E8D6C6] bg-white');
      $(this).find('.check-icon').addClass('hidden');
    }
  });
}

function requestBilling(){
  const pmLabels = {promptpay:'พร้อมเพย์', counter:'จ่ายที่เคาน์เตอร์'};
  billingRequested = true;
  $('#payment-label').text(pmLabels[selectedPayment]||selectedPayment);
  closeSheet();
  renderBottomActions();
}

function cancelBilling(){
  billingRequested = false;
  renderBottomActions();
}

function closeSheet(){
  $('#overlay,#sheet').addClass('hidden');
}

// ─── Init ─────────────────────────────────────
initTableId();
loadOrders();
// Auto-poll every 10s
pollTimer = setInterval(loadOrders, POLL_MS);
</script>
</body>
</html>
