<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>บัญชี · แซ่บกลางซอย</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>
body{font-family:'Sarabun',sans-serif;background:#F7F3EF;}
.btn-red{background:linear-gradient(135deg,#FF5546,#F23A2B,#C41E0E);}
.safe-bottom{padding-bottom:max(16px,env(safe-area-inset-bottom));}
</style>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet"/>
</head>
<body>

<!-- Navbar -->
<nav class="sticky top-0 z-40 border-b border-black/8 bg-white/80 backdrop-blur-md px-6 py-4 flex items-center justify-between">
  <div class="flex items-center gap-3">
    <a href="index.php" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#F7F3EF] text-[#4A3728] hover:bg-[#EFE8E0]">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
    </a>
    <h1 class="text-[17px] font-bold text-[#2C1713]">นักบัญชี</h1>
  </div>
  <button id="refreshBtn" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#F7F3EF] text-[#4A3728]">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
  </button>
</nav>

<!-- Safe area CSS -->
<div class="max-w-4xl mx-auto"><div id="summary-area" class="px-4 pt-4">
  <div class="py-8 text-center text-[#9D7F6A]">กำลังโหลด...</div>
</div>

<!-- Bill list -->
<div class="px-4 pb-8 max-w-4xl mx-auto">
  <div class="flex items-center justify-between mb-3 mt-5">
    <h3 class="text-[15px] font-semibold text-[#2C1713]">ประวัติบิล</h3>
    <div class="flex gap-2">
      <button data-filter="today" class="bill-filter h-8 rounded-full px-3 text-[11px] font-medium btn-red text-white">วันนี้</button>
      <button data-filter="all" class="bill-filter h-8 rounded-full px-3 text-[11px] font-medium bg-white text-[#7C5B47] ring-1 ring-[#F0E0D4]">ทั้งหมด</button>
    </div>
  </div>
  <div id="bill-list" class="space-y-3"></div>
</div>

<script>
let bills = [];
let summary = {};
let activeFilter = 'today';

function fmtMoney(n){ return '฿'+Number(n).toLocaleString('th-TH'); }

function loadSummary(){
  return $.getJSON('api/accounting_summary.php').then(function(s){
    summary = s;
    $('#summary-area').html(`
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        ${[
          {label:'รายได้วันนี้',val:fmtMoney(s.todayRevenue||0),sub:`${s.todayBills||0} บิล`,color:'text-[#E12717]'},
          {label:'รายได้สัปดาห์',val:fmtMoney(s.weekRevenue||0),sub:`${s.weekBills||0} บิล`,color:'text-[#2D5A1B]'},
          {label:'รายได้ทั้งหมด',val:fmtMoney(s.allRevenue||0),sub:`${s.allBills||0} บิล`,color:'text-[#1A1D2E]'},
          {label:'ค่าเฉลี่ย/บิล',val:fmtMoney(s.avgBill||0),sub:'เฉลี่ย',color:'text-[#7C5B47]'},
        ].map(c=>`<div class="rounded-[20px] bg-white p-4 ring-1 ring-[#F0E0D4]">
          <p class="text-[11px] text-[#9D7F6A]">${c.label}</p>
          <p class="mt-1 text-[20px] font-bold ${c.color} tabular-nums">${c.val}</p>
          <p class="text-[11px] text-[#9D7F6A]">${c.sub}</p>
        </div>`).join('')}
      </div>`);
  });
}

function loadBills(){
  const params = activeFilter==='today' ? '?today=1' : '';
  return $.getJSON('api/bills.php'+params).then(function(data){
    bills = data;
    renderBills();
  });
}

function renderBills(){
  if(!bills.length){
    $('#bill-list').html('<div class="py-8 text-center text-[#9D7F6A]">ยังไม่มีบิล</div>');
    return;
  }
  $('#bill-list').html(bills.map(b=>{
    const pm = {cash:'เงินสด',transfer:'โอน/QR'}[b.paymentMethod]||'—';
    const itemsHtml = b.items.map(i=>`<div class="flex justify-between text-[11px] text-[#7C5B47]">
      <span>${i.name} ×${i.quantity}</span>
      <span class="tabular-nums">${fmtMoney(i.price*i.quantity)}</span>
    </div>`).join('');
    return `<div class="rounded-[20px] bg-white ring-1 ring-[#F0E0D4] overflow-hidden">
      <div class="flex items-center justify-between px-5 py-3.5 border-b border-[#F7F0E8]">
        <div>
          <p class="text-[13px] font-semibold text-[#2C1713]">${b.id}</p>
          <p class="text-[11px] text-[#9D7F6A]">โต๊ะ ${b.tableId} · ${b.closedAt}${b.guests?' · '+b.guests+' คน':''}</p>
        </div>
        <div class="text-right">
          <p class="text-[16px] font-bold text-[#E12717] tabular-nums">${fmtMoney(b.total)}</p>
          <p class="text-[10px] text-[#9D7F6A]">${pm}</p>
        </div>
      </div>
      <div class="px-5 py-3 space-y-1">
        ${itemsHtml}
        <div class="border-t border-dashed border-[#F0E0D4] mt-2 pt-2">
          <div class="flex justify-between text-[11px] text-[#9D7F6A]"><span>ยอดอาหาร</span><span class="tabular-nums">${fmtMoney(b.subtotal)}</span></div>
          <div class="flex justify-between text-[11px] text-[#9D7F6A]"><span>VAT ${b.vatRate}%</span><span class="tabular-nums">${fmtMoney(b.vat)}</span></div>
          ${b.serviceCharge>0?`<div class="flex justify-between text-[11px] text-[#9D7F6A]"><span>Service ${b.serviceCharge}%</span><span class="tabular-nums">${fmtMoney(b.serviceAmt)}</span></div>`:''}
          ${b.cashReceived?`<div class="flex justify-between text-[11px] text-[#9D7F6A]"><span>รับเงิน</span><span class="tabular-nums">${fmtMoney(b.cashReceived)}</span></div>`:''}
          ${b.change!=null?`<div class="flex justify-between text-[11px] text-emerald-600"><span>เงินทอน</span><span class="tabular-nums">${fmtMoney(b.change)}</span></div>`:''}
        </div>
      </div>
    </div>`;
  }).join(''));
}

$(document).on('click','.bill-filter',function(){
  activeFilter=$(this).data('filter');
  $('.bill-filter').removeClass('btn-red text-white').addClass('bg-white text-[#7C5B47] ring-1 ring-[#F0E0D4]');
  $(this).addClass('btn-red text-white').removeClass('bg-white text-[#7C5B47] ring-1 ring-[#F0E0D4]');
  loadBills();
});

$('#refreshBtn').on('click',function(){ loadSummary(); loadBills(); });

loadSummary();
loadBills();
</script>
</body>
</html>
