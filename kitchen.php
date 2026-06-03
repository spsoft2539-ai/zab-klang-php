<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>ห้องครัว · แซ่บกลางซอย</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>
body{font-family:'Sarabun',sans-serif;background:#0F0F12;color:#fff;}
.btn-red{background:linear-gradient(135deg,#FF5546,#F23A2B,#C41E0E);}
body{padding-bottom:env(safe-area-inset-bottom);}
</style>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet"/>
</head>
<body class="min-h-screen">

<!-- Navbar -->
<nav class="sticky top-0 z-40 border-b border-white/10 bg-[#0F0F12]/90 backdrop-blur-md px-6 py-4 flex items-center justify-between">
  <div class="flex items-center gap-3">
    <div class="flex h-9 w-9 items-center justify-center rounded-xl btn-red">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/><line x1="6" x2="18" y1="17" y2="17"/></svg>
    </div>
    <div>
      <h1 class="text-[17px] font-bold">ห้องครัว</h1>
      <p id="kitchen-sub" class="text-[11px] text-white/50">กำลังโหลด...</p>
    </div>
  </div>
  <div class="flex items-center gap-2">
    <div id="live-dot" class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></div>
    <span class="text-[11px] text-white/50">Live</span>
    <a href="index.php" class="ml-3 text-[12px] text-white/50 hover:text-white">← กลับ</a>
  </div>
</nav>

<!-- Tabs -->
<div class="sticky top-[61px] z-30 bg-[#0F0F12]/90 backdrop-blur-md px-4 pt-3 pb-2 border-b border-white/8">
  <div class="flex gap-2">
    <button data-tab="incoming" class="kitchen-tab flex h-9 shrink-0 items-center gap-1.5 rounded-full px-4 text-[12px] font-medium bg-white/15 text-white">
      รายการใหม่ <span id="cnt-incoming" class="rounded-full bg-[#E12717] px-2 text-[10px] font-bold">0</span>
    </button>
    <button data-tab="cooking" class="kitchen-tab flex h-9 shrink-0 items-center gap-1.5 rounded-full px-4 text-[12px] font-medium text-white/50">
      กำลังทำ <span id="cnt-cooking" class="rounded-full bg-white/20 px-2 text-[10px]">0</span>
    </button>
  </div>
</div>

<!-- Content -->
<main class="p-4" id="kitchen-content">
  <div class="py-12 text-center text-white/40">กำลังโหลดออเดอร์...</div>
</main>

<script>
let orders = [];
let accepted = {}; // orderId -> bool (accepted = cooking)
let activeTab = 'incoming';
let lastSince = 0;
const POLL_MS = 8000;

function fmtMoney(n){ return '฿'+Number(n).toLocaleString('th-TH'); }

function loadOrders(){
  $.getJSON('api/orders.php', function(data){
    orders = data;
    const newIds = data.filter(o=>!o.printed).map(o=>o.id);
    // cleanup accepted map
    Object.keys(accepted).forEach(k=>{ if(!newIds.includes(k)) delete accepted[k]; });
    renderKitchen();
  });
}

function renderKitchen(){
  const unprintedOrders = orders.filter(o=>!o.printed);
  const incoming = unprintedOrders.filter(o=>!accepted[o.id]);
  const cooking  = unprintedOrders.filter(o=>accepted[o.id]);

  $('#cnt-incoming').text(incoming.length);
  $('#cnt-cooking').text(cooking.length);
  $('#kitchen-sub').text(`${unprintedOrders.length} ออเดอร์รอทำ · อัพเดทอัตโนมัติ`);

  const list = activeTab==='incoming' ? incoming : cooking;

  if(!list.length){
    $('#kitchen-content').html(`<div class="py-12 text-center text-white/40">
      ${activeTab==='incoming'?'✅ ไม่มีออเดอร์ใหม่':'🍳 ยังไม่มีออเดอร์ที่กำลังทำ'}</div>`);
    return;
  }

  $('#kitchen-content').html(`<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">${list.map(o=>{
    const tableOrders = orders.filter(x=>x.tableId===o.tableId&&!x.printed);
    const isAccepted = !!accepted[o.id];
    const btnHtml = isAccepted
      ? `<button onclick="markDone('${o.id}')" class="w-full rounded-xl py-2.5 text-[12px] font-semibold text-[#0F0F12]" style="background:#4ade80">✅ ทำเสร็จแล้ว</button>`
      : `<button onclick="acceptOrder('${o.id}')" class="w-full rounded-xl py-2.5 text-[12px] font-semibold text-white btn-red">รับออเดอร์</button>`;
    return `<div class="rounded-[22px] bg-white/8 ring-1 ring-white/10 overflow-hidden">
      <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
        <div>
          <p class="text-[14px] font-bold text-white">${o.id}</p>
          <p class="text-[11px] text-white/50">โต๊ะ ${o.tableId} · ${o.orderedAt}</p>
        </div>
        <span class="rounded-full px-2 py-1 text-[10px] font-medium ${isAccepted?'bg-emerald-400/20 text-emerald-400':'bg-[#E12717]/20 text-[#FF7A6E]'}">
          ${isAccepted?'กำลังทำ':'ใหม่'}
        </span>
      </div>
      <div class="px-4 py-3 space-y-2">
        ${o.items.map(i=>`<div class="flex items-start justify-between gap-2">
          <div>
            <p class="text-[13px] font-medium text-white">${i.name}</p>
            ${i.note?`<p class="text-[11px] text-white/40">${i.note}</p>`:''}
          </div>
          <span class="shrink-0 rounded-full bg-white/10 px-2 py-0.5 text-[11px] font-bold text-white tabular-nums">×${i.quantity}</span>
        </div>`).join('')}
      </div>
      <div class="px-4 pb-4">${btnHtml}</div>
    </div>`;
  }).join('')}</div>`);
}

function acceptOrder(id){ accepted[id]=true; renderKitchen(); }

function markDone(id){
  $.ajax({url:'api/order_printed.php?id='+encodeURIComponent(id),method:'PATCH',
    success:function(){ loadOrders(); },
    error:function(){ alert('เกิดข้อผิดพลาด'); }
  });
}

$('#kitchen-content').parent().on('click','.kitchen-tab',function(){ }); // prevent propagation
$(document).on('click','.kitchen-tab',function(){
  activeTab=$(this).data('tab');
  $('.kitchen-tab').removeClass('bg-white/15 text-white').addClass('text-white/50');
  $(this).addClass('bg-white/15 text-white').removeClass('text-white/50');
  renderKitchen();
});

loadOrders();
setInterval(loadOrders, POLL_MS);
</script>
</body>
</html>
