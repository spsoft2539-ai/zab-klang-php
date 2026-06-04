<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>Dashboard · แซ่บกลางซอย</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>
body{font-family:'Sarabun',sans-serif;background:#F7F3EF;}
.btn-red{background:linear-gradient(135deg,#FF5546,#F23A2B,#C41E0E);}
.tab-active{border-bottom-color:#E12717!important;color:#E12717!important;}
body{padding-bottom:env(safe-area-inset-bottom);}
.bar-col{display:flex;flex-direction:column;align-items:center;gap:4px;flex:1;}
.bar-fill{width:100%;border-radius:6px 6px 0 0;background:linear-gradient(to top,#E12717,#FF6B5B);transition:height .5s;}
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
    <h1 class="text-[17px] font-bold text-[#2C1713]">Dashboard</h1>
  </div>
  <a href="accounting.php" class="text-[12px] text-[#9D7F6A] hover:text-[#E12717]">บัญชี →</a>
</nav>

<!-- Tabs -->
<div class="border-b border-[#F0E0D4] bg-white px-2 overflow-x-auto">
  <div class="flex gap-0 min-w-max">
    <?php
    $tabs = [
      ['id'=>'overview',  'label'=>'ภาพรวม'],
      ['id'=>'revenue',   'label'=>'รายรับ'],
      ['id'=>'history',   'label'=>'ประวัติบิล'],
      ['id'=>'menu',      'label'=>'เมนู'],
      ['id'=>'tables',    'label'=>'โต๊ะ'],
      ['id'=>'pos',       'label'=>'POS'],
      ['id'=>'settings',  'label'=>'ตั้งค่า'],
    ];
    foreach($tabs as $i => $tab):
    ?>
    <button data-tab="<?= $tab['id'] ?>"
      class="dash-tab border-b-2 border-transparent px-5 py-3.5 text-[13px] font-medium text-[#9D7F6A] whitespace-nowrap <?= $i===0?'tab-active':'' ?>">
      <?= $tab['label'] ?>
    </button>
    <?php endforeach; ?>
  </div>
</div>

<!-- Content -->
<div id="tab-content" class="p-4 max-w-5xl mx-auto">
  <div class="py-10 text-center text-[#9D7F6A]">กำลังโหลด...</div>
</div>

<script>
let currentTab = 'overview';
let summary = {}, menuItems = [], tablesList = [], settings = {}, categories = [], bills = [];

function fmtMoney(n){ return '฿'+Number(n).toLocaleString('th-TH'); }
function fmtDate(ms){
  return new Date(parseInt(ms)).toLocaleDateString('th-TH',{day:'2-digit',month:'short',year:'numeric',timeZone:'Asia/Bangkok'});
}

function loadAll(){
  return Promise.all([
    $.getJSON('api/accounting_summary.php'),
    $.getJSON('api/menu.php'),
    $.getJSON('api/tables.php'),
    $.getJSON('api/settings.php'),
    $.getJSON('api/categories.php'),
    $.getJSON('api/bills.php'),
  ]).then(function([s,m,t,st,c,b]){
    summary=s; menuItems=m; tablesList=t; settings=st; categories=c;
    bills=b.slice().reverse(); // newest first
    renderTab();
  });
}

function renderTab(){
  const fns = {
    overview: renderOverview, revenue: renderRevenue, history: renderHistory,
    menu: renderMenuTab, tables: renderTablesTab, pos: renderPos, settings: renderSettings
  };
  if(fns[currentTab]) fns[currentTab]();
}

/* ══════════════════════════════════════════════
   TAB: ภาพรวม
══════════════════════════════════════════════ */
function renderOverview(){
  const busy = tablesList.filter(t=>t.status!=='available').length;
  $('#tab-content').html(`
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-5">
      ${[
        {label:'รายได้วันนี้',    val:fmtMoney(summary.todayRevenue||0), sub:`${summary.todayBills||0} บิล`,  color:'text-[#E12717]'},
        {label:'รายได้สัปดาห์',  val:fmtMoney(summary.weekRevenue||0),  sub:`${summary.weekBills||0} บิล`,  color:'text-[#2D5A1B]'},
        {label:'รายได้ทั้งหมด',  val:fmtMoney(summary.allRevenue||0),   sub:`${summary.allBills||0} บิล`,  color:'text-[#1A1D2E]'},
        {label:'ค่าเฉลี่ย/บิล',  val:fmtMoney(summary.avgBill||0),      sub:'เฉลี่ย',                       color:'text-[#7C5B47]'},
      ].map(s=>`<div class="rounded-[20px] bg-white p-4 ring-1 ring-[#F0E0D4]">
        <p class="text-[11px] text-[#9D7F6A]">${s.label}</p>
        <p class="mt-1 text-[20px] font-bold ${s.color} tabular-nums">${s.val}</p>
        <p class="text-[11px] text-[#9D7F6A]">${s.sub}</p>
      </div>`).join('')}
    </div>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-5">
      ${[
        {label:'โต๊ะทั้งหมด',   val:tablesList.length,                                    icon:'🪑'},
        {label:'โต๊ะว่าง',      val:tablesList.filter(t=>t.status==='available').length,  icon:'✅'},
        {label:'โต๊ะกำลังใช้',  val:busy,                                                  icon:'🔥'},
        {label:'เมนูทั้งหมด',   val:menuItems.length,                                      icon:'🍽'},
      ].map(s=>`<div class="rounded-[20px] bg-white p-4 ring-1 ring-[#F0E0D4]">
        <p class="text-[24px]">${s.icon}</p>
        <p class="mt-1 text-[22px] font-bold text-[#2C1713] tabular-nums">${s.val}</p>
        <p class="text-[11px] text-[#9D7F6A]">${s.label}</p>
      </div>`).join('')}
    </div>
    <!-- Table status quick view -->
    <div class="rounded-[20px] bg-white p-5 ring-1 ring-[#F0E0D4]">
      <h3 class="text-[14px] font-semibold text-[#2C1713] mb-3">สถานะโต๊ะปัจจุบัน</h3>
      <div class="grid grid-cols-4 gap-2 sm:grid-cols-6 lg:grid-cols-8">
        ${tablesList.map(t=>{
          const dotColor={available:'bg-emerald-400',active:'bg-amber-400',preparing:'bg-orange-400',billing:'bg-violet-400'}[t.status]||'bg-gray-300';
          return `<div class="flex flex-col items-center gap-1 rounded-xl bg-[#FFF9F5] p-2 ring-1 ring-[#F0E0D4]">
            <span class="h-2 w-2 rounded-full ${dotColor}"></span>
            <span class="text-[12px] font-bold text-[#2C1713]">${t.id}</span>
          </div>`;
        }).join('')}
      </div>
    </div>`);
}

/* ══════════════════════════════════════════════
   TAB: รายรับ
══════════════════════════════════════════════ */
function renderRevenue(){
  const cashBills  = bills.filter(b=>b.paymentMethod!=='transfer');
  const transBills = bills.filter(b=>b.paymentMethod==='transfer');
  const cashTotal  = cashBills.reduce((s,b)=>s+b.total,0);
  const transTotal = transBills.reduce((s,b)=>s+b.total,0);
  const allTotal   = bills.reduce((s,b)=>s+b.total,0);

  // 7-day bar chart data
  const dayMap = new Map();
  for(let i=6;i>=0;i--){
    const d=new Date(); d.setDate(d.getDate()-i);
    const key=d.toLocaleDateString('th-TH',{day:'numeric',month:'short',timeZone:'Asia/Bangkok'});
    dayMap.set(key,{label:key,value:0});
  }
  bills.forEach(b=>{
    const key=new Date(b.closedAtMs).toLocaleDateString('th-TH',{day:'numeric',month:'short',timeZone:'Asia/Bangkok'});
    if(dayMap.has(key)) dayMap.get(key).value+=b.total;
  });
  const days=Array.from(dayMap.values());
  const maxVal=Math.max(...days.map(d=>d.value),1);

  const barHtml = days.map(d=>{
    const pct=d.value>0?Math.max(6,(d.value/maxVal)*100):3;
    const label=d.value>0?`฿${(d.value/1000).toFixed(1)}k`:'';
    return `<div class="bar-col">
      <span class="text-[9px] text-[#9D7F6A] tabular-nums h-4">${label}</span>
      <div class="relative flex-1 w-full flex items-end">
        <div class="bar-fill w-full" style="height:${pct}%;${d.value===0?'opacity:0.2':''}"></div>
      </div>
      <span class="text-[9px] text-[#9D7F6A] whitespace-nowrap">${d.label}</span>
    </div>`;
  }).join('');

  const cashPct = allTotal>0?Math.round(cashTotal/allTotal*100):50;

  $('#tab-content').html(`
    <!-- Export button -->
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-[18px] font-bold text-[#2C1713]">รายรับ / จ่าย</h2>
        <p class="text-[12px] text-[#9D7F6A]">ภาพรวมการเงินร้าน</p>
      </div>
      <button onclick="exportCsv()" class="flex items-center gap-1.5 rounded-xl border border-[#E8D6C6] bg-white px-3 py-2 text-[12px] font-medium text-[#7C5B47] hover:bg-[#FFF9F5]">
        ⬇ Export CSV
      </button>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-3 gap-3 mb-5">
      <div class="rounded-[20px] p-4 text-white btn-red shadow-[0_8px_24px_rgba(225,39,23,0.3)] col-span-1">
        <p class="text-[10px] font-medium uppercase tracking-wide text-white/70 mb-2">รายรับทั้งหมด</p>
        <p class="text-[20px] font-bold tabular-nums">${fmtMoney(allTotal)}</p>
        <p class="text-[10px] text-white/60 mt-0.5">${bills.length} บิล</p>
      </div>
      <div class="rounded-[20px] bg-white p-4 ring-1 ring-[#F0E0D4]">
        <div class="text-emerald-500 mb-2">💵</div>
        <p class="text-[10px] text-[#9D7F6A]">เงินสด</p>
        <p class="text-[18px] font-bold text-[#2C1713] tabular-nums">${fmtMoney(cashTotal)}</p>
        <p class="text-[10px] text-[#9D7F6A]">${cashBills.length} บิล</p>
      </div>
      <div class="rounded-[20px] bg-white p-4 ring-1 ring-[#F0E0D4]">
        <div class="text-blue-500 mb-2">🏦</div>
        <p class="text-[10px] text-[#9D7F6A]">โอนเงิน</p>
        <p class="text-[18px] font-bold text-[#2C1713] tabular-nums">${fmtMoney(transTotal)}</p>
        <p class="text-[10px] text-[#9D7F6A]">${transBills.length} บิล</p>
      </div>
    </div>

    <!-- 7-day bar chart -->
    <div class="rounded-[20px] bg-white p-5 ring-1 ring-[#F0E0D4] mb-5">
      <p class="text-[13px] font-semibold text-[#2C1713] mb-4">รายรับ 7 วันที่ผ่านมา</p>
      <div class="flex items-end gap-2 h-36">${barHtml}</div>
    </div>

    <!-- Payment breakdown -->
    <div class="rounded-[20px] bg-white p-5 ring-1 ring-[#F0E0D4] mb-5">
      <p class="text-[13px] font-semibold text-[#2C1713] mb-3">สัดส่วนวิธีชำระ</p>
      <div class="mb-3 flex h-3 overflow-hidden rounded-full bg-[#F0E0D4]">
        <div class="bg-emerald-400 transition-all rounded-l-full" style="width:${cashPct}%"></div>
        <div class="flex-1 bg-blue-400 rounded-r-full"></div>
      </div>
      <div class="flex justify-between text-[12px]">
        <div class="flex items-center gap-1.5">
          <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
          <span class="text-[#9D7F6A]">เงินสด</span>
          <span class="font-semibold text-[#2C1713]">${allTotal>0?cashPct+'%':'—'}</span>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="font-semibold text-[#2C1713]">${allTotal>0?(100-cashPct)+'%':'—'}</span>
          <span class="text-[#9D7F6A]">โอนเงิน</span>
          <span class="h-2.5 w-2.5 rounded-full bg-blue-400"></span>
        </div>
      </div>
    </div>

    <!-- Expenses coming soon -->
    <div class="rounded-[20px] border border-dashed border-[#E8D6C6] bg-white p-5 text-center">
      <p class="text-[13px] font-semibold text-[#2C1713]">บันทึกรายจ่าย</p>
      <p class="mt-1 text-[11px] text-[#9D7F6A]">ฟีเจอร์บันทึกค่าใช้จ่ายร้าน (วัตถุดิบ, ค่าจ้าง ฯลฯ) กำลังพัฒนา</p>
      <span class="mt-2 inline-block rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-600">Coming soon</span>
    </div>`);
}

function exportCsv(){
  const rows=[['บิล','โต๊ะ','วันที่','เวลา','ชำระด้วย','ยอดก่อน VAT','VAT','รวม','รับเงิน','เงินทอน']];
  bills.forEach(b=>rows.push([
    b.id, b.tableId, fmtDate(b.closedAtMs), b.closedAt,
    b.paymentMethod==='transfer'?'โอนเงิน':'เงินสด',
    b.subtotal, b.vat, b.total,
    b.cashReceived||'', b.change||''
  ]));
  const csv='﻿'+rows.map(r=>r.join(',')).join('\n');
  const a=document.createElement('a');
  a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(csv);
  a.download='revenue_'+new Date().toISOString().slice(0,10)+'.csv';
  a.click();
}

/* ══════════════════════════════════════════════
   TAB: ประวัติบิล
══════════════════════════════════════════════ */
function renderHistory(){
  $('#tab-content').html(`
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-[18px] font-bold text-[#2C1713]">ประวัติการทำรายการ</h2>
        <p class="text-[12px] text-[#9D7F6A]">${bills.length} บิลทั้งหมด</p>
      </div>
      <button onclick="exportCsv()" class="flex items-center gap-1.5 rounded-xl border border-[#E8D6C6] bg-white px-3 py-2 text-[12px] font-medium text-[#7C5B47] hover:bg-[#FFF9F5]">
        ⬇ Export CSV
      </button>
    </div>
    ${!bills.length
      ? `<div class="rounded-[20px] border border-dashed border-[#E8D6C6] bg-white py-12 text-center">
          <p class="text-[28px] mb-2">🧾</p>
          <p class="text-[13px] font-semibold text-[#2C1713]">ยังไม่มีประวัติ</p>
          <p class="mt-1 text-[11px] text-[#9D7F6A]">บิลจะบันทึกเมื่อแคชเชียร์ปิดโต๊ะ</p>
        </div>`
      : `<div class="space-y-2" id="bill-rows">${bills.map(b=>renderBillRow(b)).join('')}</div>`}
  `);
}

function renderBillRow(b){
  const pmIcon  = b.paymentMethod==='transfer'?'🏦':'💵';
  const pmColor = b.paymentMethod==='transfer'?'bg-blue-500':'bg-emerald-500';
  const itemsHtml = b.items.map(i=>`
    <div class="flex items-center justify-between text-[12px]">
      <span class="text-[#2C1713]">${i.name}${i.note?` <span class="text-[#9D7F6A]">(${i.note})</span>`:''}</span>
      <span class="tabular-nums text-[#7C5B47]">×${i.quantity} = ${fmtMoney(i.price*i.quantity)}</span>
    </div>`).join('');
  const changeHtml = b.change>0?`<div class="flex justify-between text-emerald-600 font-medium"><span>เงินทอน</span><span>${fmtMoney(b.change)}</span></div>`:'';
  const cashHtml = b.cashReceived?`<div class="flex justify-between text-[#7C5B47]"><span>รับเงิน</span><span>${fmtMoney(b.cashReceived)}</span></div>`:'';
  return `<div class="overflow-hidden rounded-[18px] bg-white ring-1 ring-[#F0E0D4]">
    <button type="button" onclick="toggleBill('${b.id}')"
      class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-[#FFF9F5] transition-colors">
      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-white ${pmColor} text-[16px]">${pmIcon}</div>
      <div class="flex-1 min-w-0">
        <p class="text-[13px] font-semibold text-[#2C1713]">
          โต๊ะ ${b.tableId}
          <span class="ml-2 text-[11px] font-normal text-[#9D7F6A]">${b.id}</span>
        </p>
        <p class="text-[11px] text-[#9D7F6A]">${fmtDate(b.closedAtMs)} · ${b.closedAt} น.${b.guests?' · '+b.guests+' คน':''}</p>
      </div>
      <div class="text-right shrink-0">
        <p class="text-[15px] font-bold tabular-nums text-[#2C1713]">${fmtMoney(b.total)}</p>
        ${b.change>0?`<p class="text-[10px] text-[#9D7F6A]">ทอน ${fmtMoney(b.change)}</p>`:''}
      </div>
      <span class="bill-chevron-${b.id} text-[#9D7F6A] text-[12px]">▼</span>
    </button>
    <div class="bill-detail-${b.id} hidden border-t border-[#F0E0D4] px-4 pb-3 pt-3">
      <div class="mb-2 space-y-1">${itemsHtml}</div>
      <div class="space-y-1 border-t border-dashed border-[#F0E0D4] pt-2 text-[12px]">
        <div class="flex justify-between text-[#9D7F6A]"><span>ยอดอาหาร</span><span>${fmtMoney(b.subtotal)}</span></div>
        ${b.vat>0?`<div class="flex justify-between text-[#9D7F6A]"><span>VAT ${b.vatRate}%</span><span>${fmtMoney(b.vat)}</span></div>`:''}
        <div class="flex justify-between font-semibold text-[#2C1713]"><span>รวมทั้งสิ้น</span><span>${fmtMoney(b.total)}</span></div>
        ${cashHtml}${changeHtml}
      </div>
    </div>
  </div>`;
}

function toggleBill(id){
  const det = $('.bill-detail-'+id);
  const chv = $('.bill-chevron-'+id);
  det.toggleClass('hidden');
  chv.text(det.hasClass('hidden')?'▼':'▲');
}

/* ══════════════════════════════════════════════
   TAB: เมนู
══════════════════════════════════════════════ */
let menuActiveCategory = 'ทั้งหมด';

function renderMenuTab(){
  const cats = ['ทั้งหมด', ...new Set(menuItems.map(m=>m.category))];
  const filtered = menuActiveCategory==='ทั้งหมด' ? menuItems : menuItems.filter(m=>m.category===menuActiveCategory);

  const catTabsHtml = cats.map(c=>`
    <button data-mcat="${c}" type="button"
      class="menu-cat-tab shrink-0 rounded-full px-3.5 py-1.5 text-[12px] font-medium transition-colors
        ${c===menuActiveCategory?'btn-red text-white shadow-[0_4px_10px_rgba(225,39,23,0.25)]':'bg-white text-[#7C5B47] ring-1 ring-[#F0E0D4]'}">
      ${c}
      ${c!=='ทั้งหมด'?`<span class="ml-1 tabular-nums ${c===menuActiveCategory?'text-white/70':'text-[#9D7F6A]'}">${menuItems.filter(m=>m.category===c).length}</span>`:''}
    </button>`).join('');

  const gridHtml = !filtered.length
    ? `<div class="col-span-full rounded-[20px] border border-dashed border-[#E8D6C6] bg-white py-10 text-center text-[#9D7F6A]">ไม่มีเมนู</div>`
    : filtered.map(item=>{
        const tagMap={'เผ็ด':'bg-red-50 text-red-600','ฮิต':'bg-green-50 text-green-700','โปร':'bg-amber-50 text-amber-700'};
        const tagHtml=item.tag?`<span class="shrink-0 rounded-full px-1.5 py-0.5 text-[9px] font-bold ${tagMap[item.tag]||''}">${item.tag}</span>`:'';
        // encode item data for edit button
        const itemJson = encodeURIComponent(JSON.stringify(item));
        return `<div class="flex gap-3 rounded-[18px] bg-white p-3 ring-1 ring-[#F0E0D4]">
          <img src="${item.image||'https://placehold.co/64x64/F7EFE7/9D7F6A?text=🍽'}" class="h-16 w-16 rounded-xl object-cover shrink-0"/>
          <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-1">
              <p class="line-clamp-1 text-[13px] font-semibold text-[#2C1713]">${item.name}</p>
              ${tagHtml}
            </div>
            <p class="mt-0.5 line-clamp-1 text-[11px] text-[#9D7F6A]">${item.description||''}</p>
            <div class="mt-2 flex items-center justify-between">
              <span class="rounded-lg bg-[#F7EFE7] px-2 py-0.5 text-[10px] font-medium text-[#7C5B47]">${item.category}</span>
              <span class="text-[14px] font-bold text-[#E12717] tabular-nums">฿${item.price.toLocaleString()}</span>
            </div>
            <!-- ✏️ Edit + 🗑 Delete + ⚙️ Options buttons -->
            <div class="mt-2.5 flex gap-1.5">
              <button onclick="showEditMenu(decodeURIComponent('${itemJson}'))"
                class="flex flex-1 items-center justify-center gap-1 rounded-lg border border-[#E8D6C6] bg-[#F7F3EF] py-1.5 text-[11px] font-semibold text-[#5A4338] hover:bg-[#EFE8E0]">
                ✏️ แก้ไข
              </button>
              <button onclick="deleteMenuItem('${item.id}','${item.name.replace(/'/g,"\\'")}')"
                class="flex flex-1 items-center justify-center gap-1 rounded-lg border border-red-100 bg-red-50 py-1.5 text-[11px] font-semibold text-red-600 hover:bg-red-100">
                🗑 ลบ
              </button>
            </div>
            <button onclick="toggleOptions('${item.id}')"
              class="mt-1.5 w-full flex items-center justify-center gap-1 rounded-lg border border-[#E8D6C6] bg-white py-1.5 text-[11px] font-semibold text-[#7C5B47] hover:bg-[#FFF9F5]">
              ⚙️ ตัวเลือก (เผ็ด/โปรตีน/เพิ่มเติม)
            </button>
            <div id="opts-${item.id}" class="hidden mt-2 rounded-xl bg-[#FFF9F5] p-3 ring-1 ring-[#F0E0D4]"></div>
          </div>
        </div>`;
      }).join('');

  $('#tab-content').html(`
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-[18px] font-bold text-[#2C1713]">จัดการเมนู</h2>
        <p class="text-[12px] text-[#9D7F6A]">${menuItems.length} รายการ · ${cats.length-1} หมวดหมู่</p>
      </div>
      <button onclick="showAddMenu()" class="flex h-9 items-center gap-1.5 rounded-xl px-4 text-[12px] font-semibold text-white btn-red">+ เพิ่มเมนู</button>
    </div>
    <div id="add-menu-area"></div>
    <!-- Category filter -->
    <div class="flex gap-2 overflow-x-auto pb-2 mb-4" style="scrollbar-width:none">${catTabsHtml}</div>
    <!-- Grid -->
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">${gridHtml}</div>
  `);
}

$(document).on('click','.menu-cat-tab',function(){
  menuActiveCategory=$(this).data('mcat');
  renderMenuTab();
});

function showAddMenu(){
  const catOptions = categories.map(c=>`<option value="${c}">${c}</option>`).join('');
  $('#add-menu-area').html(`
    <div class="mb-4 rounded-[20px] bg-white p-5 ring-1 ring-[#F0E0D4]">
      <h4 class="text-[15px] font-semibold text-[#2C1713] mb-4">เพิ่มเมนูใหม่</h4>
      <div class="space-y-3">
        <input id="m-name" placeholder="ชื่อเมนู *" class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/>
        <textarea id="m-desc" placeholder="คำอธิบาย" rows="2" class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none resize-none"></textarea>
        <div class="flex gap-2">
          <input id="m-price" type="number" placeholder="ราคา *" class="flex-1 rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/>
          <select id="m-tag" class="flex-1 rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none">
            <option value="">ไม่มี tag</option>
            <option value="เผ็ด">🌶 เผ็ด</option><option value="ฮิต">⭐ ฮิต</option><option value="โปร">🎉 โปร</option>
          </select>
        </div>
        <select id="m-cat" class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none">${catOptions}</select>
        <input id="m-img" placeholder="URL รูปภาพ" class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none"/>
        <div class="flex gap-2">
          <button onclick="$('#add-menu-area').html('')" class="flex-1 rounded-xl border border-[#E8D6C6] bg-white py-2.5 text-[13px] font-medium text-[#2C1713]">ยกเลิก</button>
          <button onclick="saveNewMenu()" class="flex-[1.5] rounded-xl py-2.5 text-[13px] font-semibold text-white btn-red">บันทึก</button>
        </div>
      </div>
    </div>`);
}

function saveNewMenu(){
  const body={name:$('#m-name').val().trim(),description:$('#m-desc').val().trim(),
    price:parseFloat($('#m-price').val()),category:$('#m-cat').val(),
    tag:$('#m-tag').val()||null,image:$('#m-img').val().trim()};
  if(!body.name||!body.price) return alert('กรุณากรอกชื่อและราคา');
  $.ajax({url:'api/menu.php',method:'POST',contentType:'application/json',data:JSON.stringify(body),
    success:function(){ loadAll(); },error:function(){ alert('บันทึกไม่สำเร็จ'); }
  });
}

/* ══ แก้ไขเมนู ══ */
function showEditMenu(itemJson){
  let item; try{ item=JSON.parse(itemJson); }catch(e){ return; }
  const catOptions=categories.map(c=>`<option value="${c}"${c===item.category?' selected':''}>${c}</option>`).join('');
  const tagOptions=[['','ไม่มี tag'],['เผ็ด','🌶 เผ็ด'],['ฮิต','⭐ ฮิต'],['โปร','🎉 โปร']]
    .map(([v,l])=>`<option value="${v}"${(item.tag||'')=== v?' selected':''}>${l}</option>`).join('');
  const escapedName = item.name.replace(/\\/g,'\\\\').replace(/'/g,"\\'");

  $('#edit-menu-area').remove();
  const form=`<div id="edit-menu-area" class="mb-5 rounded-[20px] bg-white p-5 ring-2 ring-[#E12717]/25 shadow-[0_4px_20px_rgba(225,39,23,0.08)]">
    <div class="flex items-center justify-between mb-4">
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wide text-[#E12717]">กำลังแก้ไข</p>
        <h4 class="text-[15px] font-bold text-[#2C1713]">${item.name}</h4>
      </div>
      <button onclick="$('#edit-menu-area').remove()" class="h-8 w-8 flex items-center justify-center rounded-full bg-[#F7EFE7] text-[#5A4338]">✕</button>
    </div>
    <div class="space-y-3">
      <input id="e-name" value="${item.name.replace(/"/g,'&quot;')}" placeholder="ชื่อเมนู *"
        class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/>
      <textarea id="e-desc" rows="2" placeholder="คำอธิบาย"
        class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none resize-none">${item.description||''}</textarea>
      <div class="flex gap-2">
        <input id="e-price" type="number" value="${item.price}" placeholder="ราคา *"
          class="flex-1 rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/>
        <select id="e-tag" class="flex-1 rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none">
          ${tagOptions}
        </select>
      </div>
      <select id="e-cat" class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none">${catOptions}</select>
      <div class="flex gap-3 items-center">
        <img id="e-img-thumb" src="${item.image||'https://placehold.co/56x56/F7EFE7/9D7F6A?text=🍽'}"
          class="h-14 w-14 rounded-xl object-cover ring-1 ring-[#F0E0D4] shrink-0"/>
        <input id="e-img" value="${item.image||''}" placeholder="URL รูปภาพ"
          class="flex-1 rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/>
      </div>
      <div class="flex gap-2 pt-1">
        <button onclick="$('#edit-menu-area').remove()"
          class="flex-1 rounded-xl border border-[#E8D6C6] bg-[#F7F3EF] py-2.5 text-[13px] font-medium text-[#2C1713]">ยกเลิก</button>
        <button onclick="saveEditMenu('${item.id}')"
          class="flex-[1.5] rounded-xl py-2.5 text-[13px] font-bold text-white btn-red shadow-[0_8px_16px_rgba(225,39,23,0.25)]">💾 บันทึกการแก้ไข</button>
      </div>
    </div>
  </div>`;

  if($('#add-menu-area').length) $('#add-menu-area').after(form);
  else $('#tab-content').prepend(form);

  $('#e-img').on('input',function(){
    const v=$(this).val().trim();
    $('#e-img-thumb').attr('src',v||'https://placehold.co/56x56/F7EFE7/9D7F6A?text=🍽');
  });
  $('html,body').animate({scrollTop:($('#edit-menu-area').offset().top-80)},300);
}

function saveEditMenu(id){
  const body={name:$('#e-name').val().trim(),description:$('#e-desc').val().trim(),
    price:parseFloat($('#e-price').val()),category:$('#e-cat').val(),
    tag:$('#e-tag').val()||null,image:$('#e-img').val().trim()};
  if(!body.name||!body.price) return alert('กรุณากรอกชื่อและราคา');
  $.ajax({url:'api/menu_item.php?id='+encodeURIComponent(id),method:'PATCH',
    contentType:'application/json',data:JSON.stringify(body),
    success:function(){ $('#edit-menu-area').remove(); loadAll(); },
    error:function(){ alert('บันทึกไม่สำเร็จ'); }
  });
}

/* ══ ลบเมนู ══ */
function deleteMenuItem(id, name){
  if(!confirm('⚠️ ลบเมนู "'+name+'" ออก?\n\nไม่สามารถกู้คืนได้!')) return;
  $.ajax({url:'api/menu_item.php?id='+encodeURIComponent(id),method:'DELETE',
    success:function(){ loadAll(); },
    error:function(){ alert('ลบไม่สำเร็จ'); }
  });
}

/* ══════════════════════════════════════════════
   TAB: โต๊ะ
══════════════════════════════════════════════ */
function renderTablesTab(){
  const zones=[...new Set(tablesList.map(t=>t.zone))].sort();
  const counts={
    available:tablesList.filter(t=>t.status==='available').length,
    active:tablesList.filter(t=>t.status==='active').length,
    preparing:tablesList.filter(t=>t.status==='preparing').length,
    billing:tablesList.filter(t=>t.status==='billing').length,
  };
  const STATUS={available:{label:'ว่าง',pill:'bg-emerald-100 text-emerald-700'},active:{label:'ใช้บริการ',pill:'bg-slate-100 text-slate-600'},preparing:{label:'รออาหาร',pill:'bg-blue-100 text-blue-700'},billing:{label:'รอชำระ',pill:'bg-red-100 text-red-600'}};

  const pillsHtml = Object.entries(counts).map(([k,v])=>`
    <div class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[12px] font-semibold ring-1 ${
      k==='available'?'bg-emerald-50 text-emerald-700 ring-emerald-200':
      k==='active'?'bg-slate-100 text-slate-600 ring-slate-200':
      k==='preparing'?'bg-blue-50 text-blue-700 ring-blue-200':
      'bg-red-50 text-red-600 ring-red-200'}">
      <span class="tabular-nums font-bold">${v}</span>
      <span class="font-medium">${STATUS[k].label}</span>
    </div>`).join('');

  const zonesHtml = zones.map(zone=>`
    <div class="mb-5">
      <p class="mb-2 text-[11px] font-bold uppercase tracking-widest text-[#9D7F6A]">โซน ${zone}</p>
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
        ${tablesList.filter(t=>t.zone===zone).map(t=>{
          const s=STATUS[t.status]||STATUS.available;
          const canDelete = t.status==='available';
          return `<div class="rounded-[18px] bg-white p-4 ring-1 ring-[#F0E0D4]">
            <div class="mb-2 flex items-center justify-between">
              <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#F7EFE7]">
                <span class="text-[13px] font-bold text-[#2C1713]">${t.id}</span>
              </div>
              <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold ${s.pill}">${s.label}</span>
            </div>
            <div class="space-y-1 text-[11px] text-[#9D7F6A]">
              <div class="flex justify-between"><span>โซน</span><span class="font-semibold text-[#2C1713]">${t.zone}</span></div>
              <div class="flex justify-between"><span>ที่นั่ง</span><span class="font-semibold text-[#2C1713]">${t.seats} คน</span></div>
              ${t.guests&&t.status!=='available'?`<div class="flex justify-between"><span>ลูกค้า</span><span class="font-semibold text-[#2C1713]">${t.guests} คน</span></div>`:''}
              ${t.openedAt&&t.status!=='available'?`<div class="flex justify-between"><span>เปิดเมื่อ</span><span class="font-semibold text-[#2C1713] tabular-nums">${t.openedAt}</span></div>`:''}
            </div>
            <div class="mt-3 flex gap-1.5">
              <button onclick="showEditTable('${t.id}','${t.zone}',${t.seats})"
                class="flex flex-1 items-center justify-center gap-1 rounded-lg border border-[#E8D6C6] bg-[#F7F3EF] py-1.5 text-[11px] font-semibold text-[#5A4338] hover:bg-[#EFE8E0]">
                ✏️ แก้ไข
              </button>
              <button onclick="deleteTable('${t.id}','${t.status}')"
                ${canDelete?'':'disabled title="โต๊ะกำลังใช้งาน"'}
                class="flex flex-1 items-center justify-center gap-1 rounded-lg border border-red-100 bg-red-50 py-1.5 text-[11px] font-semibold text-red-600 hover:bg-red-100 disabled:opacity-40 disabled:cursor-not-allowed">
                🗑 ลบ
              </button>
            </div>
          </div>`;
        }).join('')}
      </div>
    </div>`).join('');

  $('#tab-content').html(`
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-[18px] font-bold text-[#2C1713]">จัดการโต๊ะ</h2>
        <p class="text-[12px] text-[#9D7F6A]">ดูสถานะโต๊ะทั้งหมด ${tablesList.length} โต๊ะ</p>
      </div>
      <button onclick="showAddTable()" class="flex h-9 items-center gap-1.5 rounded-xl px-4 text-[12px] font-semibold text-white btn-red">+ เพิ่มโต๊ะ</button>
    </div>
    <div id="add-table-area"></div>
    <!-- Status pills -->
    <div class="flex flex-wrap gap-2 mb-5">${pillsHtml}</div>
    <!-- Zones -->
    ${zonesHtml}
    `);
}

function showAddTable(){
  $('#add-table-area').html(`
    <div class="mb-4 rounded-[20px] bg-white p-5 ring-1 ring-[#F0E0D4]">
      <h4 class="text-[15px] font-semibold text-[#2C1713] mb-3">เพิ่มโต๊ะใหม่</h4>
      <div class="flex gap-2 mb-3">
        <input id="t-id" placeholder="รหัสโต๊ะ เช่น E1" maxlength="5" class="flex-1 rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/>
        <select id="t-zone" class="flex-1 rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none">
          <option>A</option><option>B</option><option>C</option><option>D</option><option>E</option>
        </select>
        <input id="t-seats" type="number" min="1" max="20" value="4" class="w-20 rounded-xl border border-[#F0E0D4] px-3 py-2.5 text-[13px] outline-none"/>
      </div>
      <div class="flex gap-2">
        <button onclick="$('#add-table-area').html('')" class="flex-1 rounded-xl border border-[#E8D6C6] bg-white py-2.5 text-[13px] font-medium text-[#2C1713]">ยกเลิก</button>
        <button onclick="saveNewTable()" class="flex-[1.5] rounded-xl py-2.5 text-[13px] font-semibold text-white btn-red">บันทึก</button>
      </div>
    </div>`);
}

function saveNewTable(){
  const id=$('#t-id').val().trim().toUpperCase(), zone=$('#t-zone').val(), seats=parseInt($('#t-seats').val())||2;
  if(!id) return alert('กรุณากรอกรหัสโต๊ะ');
  $.ajax({url:'api/table.php?id='+encodeURIComponent(id),method:'POST',contentType:'application/json',
    data:JSON.stringify({id,zone,seats}),
    success:function(){ loadAll(); },error:function(r){ alert(r.responseJSON?.error||'เพิ่มโต๊ะไม่สำเร็จ'); }
  });
}

/* ══ แก้ไขโต๊ะ ══ */
function showEditTable(id, zone, seats){
  $('#edit-table-area').remove();
  const zoneOptions=['A','B','C','D','E'].map(z=>`<option value="${z}"${z===zone?' selected':''}>${z}</option>`).join('');
  const form=`<div id="edit-table-area" class="mb-5 rounded-[20px] bg-white p-5 ring-2 ring-[#E12717]/25 shadow-[0_4px_20px_rgba(225,39,23,0.08)]">
    <div class="flex items-center justify-between mb-4">
      <div>
        <p class="text-[10px] font-semibold uppercase tracking-wide text-[#E12717]">กำลังแก้ไข</p>
        <h4 class="text-[15px] font-bold text-[#2C1713]">โต๊ะ ${id}</h4>
      </div>
      <button onclick="$('#edit-table-area').remove()" class="h-8 w-8 flex items-center justify-center rounded-full bg-[#F7EFE7] text-[#5A4338]">✕</button>
    </div>
    <div class="flex gap-2 mb-4">
      <div class="flex-1">
        <label class="text-[11px] text-[#9D7F6A] mb-1 block">โซน</label>
        <select id="et-zone" class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]">
          ${zoneOptions}
        </select>
      </div>
      <div class="w-28">
        <label class="text-[11px] text-[#9D7F6A] mb-1 block">จำนวนที่นั่ง</label>
        <input id="et-seats" type="number" min="1" max="20" value="${seats}"
          class="w-full rounded-xl border border-[#F0E0D4] px-3 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/>
      </div>
    </div>
    <div class="flex gap-2">
      <button onclick="$('#edit-table-area').remove()"
        class="flex-1 rounded-xl border border-[#E8D6C6] bg-[#F7F3EF] py-2.5 text-[13px] font-medium text-[#2C1713]">ยกเลิก</button>
      <button onclick="saveEditTable('${id}')"
        class="flex-[1.5] rounded-xl py-2.5 text-[13px] font-bold text-white btn-red shadow-[0_8px_16px_rgba(225,39,23,0.25)]">💾 บันทึกการแก้ไข</button>
    </div>
  </div>`;

  if($('#add-table-area').length) $('#add-table-area').after(form);
  else $('#tab-content').prepend(form);
  $('html,body').animate({scrollTop:($('#edit-table-area').offset().top-80)},200);
}

function saveEditTable(id){
  const zone=$('#et-zone').val(), seats=parseInt($('#et-seats').val())||1;
  $.ajax({url:'api/table.php?id='+encodeURIComponent(id),method:'PUT',contentType:'application/json',
    data:JSON.stringify({zone,seats}),
    success:function(){ $('#edit-table-area').remove(); loadAll(); },
    error:function(){ alert('บันทึกไม่สำเร็จ'); }
  });
}

/* ══ ลบโต๊ะ ══ */
function deleteTable(id, status){
  if(status!=='available') return alert('โต๊ะ '+id+' กำลังใช้งานอยู่\nไม่สามารถลบได้จนกว่าจะปิดบิล');
  if(!confirm('⚠️ ลบโต๊ะ "'+id+'" ออก?\n\nไม่สามารถกู้คืนได้!')) return;
  $.ajax({url:'api/table.php?id='+encodeURIComponent(id),method:'DELETE',
    success:function(){ loadAll(); },
    error:function(r){ alert(r.responseJSON?.error||'ลบไม่สำเร็จ'); }
  });
}

/* ══════════════════════════════════════════════
   TAB: POS
══════════════════════════════════════════════ */
function renderPos(){
  $('#tab-content').html(`
    <div class="space-y-5 max-w-2xl">
      <div>
        <h2 class="text-[18px] font-bold text-[#2C1713]">จัดการเครื่อง POS</h2>
        <p class="mt-0.5 text-[12px] text-[#9D7F6A]">อุปกรณ์ที่เชื่อมต่อกับระบบ</p>
      </div>
      <!-- Active machine -->
      <div class="flex items-center gap-4 rounded-[20px] bg-white p-4 ring-1 ring-[#F0E0D4]">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#FFF0EE] text-[32px]">🖥</div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2">
            <p class="text-[14px] font-bold text-[#2C1713]">เครื่อง 01</p>
            <span class="flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-600 ring-1 ring-emerald-200">
              ✅ ออนไลน์
            </span>
          </div>
          <p class="mt-0.5 text-[12px] text-[#9D7F6A]">เคาน์เตอร์หน้าร้าน</p>
          <p class="mt-1 text-[11px] text-[#9D7F6A]">📶 localhost · ใช้งานล่าสุด: เมื่อสักครู่</p>
        </div>
        <a href="cashier.php" class="flex items-center gap-1 rounded-xl border border-[#E8D6C6] bg-[#F7F3EF] px-3 py-1.5 text-[12px] font-medium text-[#2C1713] hover:bg-[#EFE8E0]">
          เปิด POS ↗
        </a>
      </div>
      <!-- Coming soon -->
      <div class="rounded-[20px] border border-dashed border-[#E8D6C6] bg-white p-5">
        <p class="text-[13px] font-semibold text-[#2C1713] mb-3">ฟีเจอร์ที่กำลังพัฒนา</p>
        <div class="space-y-2">
          ${['เพิ่มเครื่อง POS หลายเครื่องพร้อมกัน','กำหนดสิทธิ์แต่ละเครื่อง (ดูได้ / แก้ไขได้ / จัดการบิลได้)','ดูออเดอร์แยกตามเครื่อง','ตั้งชื่อและ PIN สำหรับแต่ละเครื่อง']
          .map(f=>`<div class="flex items-center gap-2 text-[12px] text-[#9D7F6A]">
            <span class="h-1.5 w-1.5 rounded-full bg-[#C4A98A]"></span>${f}</div>`).join('')}
        </div>
        <span class="mt-3 inline-block rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-600">Coming soon</span>
      </div>
    </div>`);
}

/* ══════════════════════════════════════════════
   TAB: ตั้งค่า
══════════════════════════════════════════════ */
function renderSettings(){
  $('#tab-content').html(`
    <div class="rounded-[20px] bg-white p-5 ring-1 ring-[#F0E0D4] max-w-lg">
      <h3 class="text-[15px] font-semibold text-[#2C1713] mb-5">ตั้งค่าร้านอาหาร</h3>
      <div class="space-y-4">
        <div><label class="text-[12px] text-[#9D7F6A] mb-1 block">ชื่อร้าน</label>
          <input id="s-name" value="${settings.restaurantName||''}" class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/></div>
        <div><label class="text-[12px] text-[#9D7F6A] mb-1 block">ประเภทอาหาร</label>
          <input id="s-cuisine" value="${settings.cuisine||''}" class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/></div>
        <div class="flex gap-2">
          <div class="flex-1"><label class="text-[12px] text-[#9D7F6A] mb-1 block">เวลาเปิด</label>
            <input id="s-open" value="${settings.openTime||'11:00'}" class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/></div>
          <div class="flex-1"><label class="text-[12px] text-[#9D7F6A] mb-1 block">เวลาปิด</label>
            <input id="s-close" value="${settings.closeTime||'22:00'}" class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/></div>
        </div>
        <div class="flex gap-2">
          <div class="flex-1"><label class="text-[12px] text-[#9D7F6A] mb-1 block">VAT (%)</label>
            <input id="s-vat" type="number" min="0" max="30" value="${settings.vatRate||7}" class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/></div>
          <div class="flex-1"><label class="text-[12px] text-[#9D7F6A] mb-1 block">Service Charge (%)</label>
            <input id="s-svc" type="number" min="0" max="30" value="${settings.serviceCharge||0}" class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/></div>
        </div>
        <div class="rounded-xl bg-[#FFF9F5] p-4 ring-1 ring-[#F0E0D4]">
          <label class="text-[12px] font-semibold text-[#9D7F6A] mb-2 block">📱 รูป QR Code พร้อมเพย์</label>
          <div class="flex gap-3 items-start">
            <div id="s-qr-preview-wrap" class="${settings.promptPayQr?'':'hidden'} shrink-0">
              <img id="s-qr-preview" src="${settings.promptPayQr||''}" alt="QR Preview"
                class="h-20 w-20 rounded-xl object-contain bg-white ring-1 ring-[#F0E0D4]"
                onerror="this.closest('div').classList.add('hidden')"/>
            </div>
            <div class="flex-1">
              <input id="s-promptpay" value="${settings.promptPayQr||''}" placeholder="วาง URL ของรูป QR พร้อมเพย์ที่นี่"
                class="w-full rounded-xl border border-[#F0E0D4] px-4 py-2.5 text-[13px] outline-none focus:border-[#E12717]"/>
              <p class="mt-1.5 text-[11px] text-[#C4A98A]">นำรูป QR จากแอปธนาคารอัปโหลดไปยัง Google Drive / Imgur แล้ววาง URL ที่นี่</p>
            </div>
          </div>
        </div>
        <button onclick="saveSettings()" class="w-full rounded-xl py-3 text-[13px] font-semibold text-white btn-red">บันทึกการตั้งค่า</button>
      </div>
    </div>`);
}

function saveSettings(){
  const body={restaurantName:$('#s-name').val().trim(),cuisine:$('#s-cuisine').val().trim(),
    openTime:$('#s-open').val().trim(),closeTime:$('#s-close').val().trim(),
    vatRate:parseFloat($('#s-vat').val())||7,serviceCharge:parseFloat($('#s-svc').val())||0,
    promptPayQr:$('#s-promptpay').val().trim()};
  $.ajax({url:'api/settings.php',method:'PATCH',contentType:'application/json',data:JSON.stringify(body),
    success:function(){ alert('บันทึกแล้ว ✅'); loadAll(); },error:function(){ alert('บันทึกไม่สำเร็จ'); }
  });
}

/* ══════════════════════════════════════════════
   Tab switching
══════════════════════════════════════════════ */
$(document).on('click','.dash-tab',function(){
  currentTab=$(this).data('tab');
  $('.dash-tab').removeClass('tab-active');
  $(this).addClass('tab-active');
  renderTab();
});

/* ══════════════════════════════════════════════
   ตัวเลือกเมนู (Options / Variants)
══════════════════════════════════════════════ */
function toggleOptions(menuId){
  const box=$('#opts-'+menuId);
  if(!box.hasClass('hidden')){ box.addClass('hidden'); return; }
  box.removeClass('hidden').html('<p class="text-[11px] text-[#9D7F6A]">กำลังโหลด...</p>');
  $.getJSON('api/menu_options.php?menu_id='+encodeURIComponent(menuId)).then(function(groups){
    renderOptionsBox(menuId, groups);
  });
}

function renderOptionsBox(menuId, groups){
  const box=$('#opts-'+menuId);
  const groupsHtml = groups.length ? groups.map(g=>`
    <div class="mb-3">
      <div class="flex items-center justify-between mb-1.5">
        <span class="text-[11px] font-bold text-[#5A4338]">${escDash(g.group)}${g.required?' <span class="text-red-500">*</span>':''}</span>
      </div>
      <div class="flex flex-wrap gap-1.5">
        ${g.items.map(o=>`
          <span class="inline-flex items-center gap-1 rounded-full bg-white px-2.5 py-1 text-[10px] font-medium text-[#5A4338] ring-1 ring-[#E8D6C6]">
            ${escDash(o.name)}${o.price>0?' ฿'+o.price:''}
            <button type="button" onclick="deleteOption('${o.id}','${menuId}')"
              class="ml-0.5 text-[#C8A48B] hover:text-red-500">✕</button>
          </span>`).join('')}
      </div>
    </div>`).join('')
    : '<p class="text-[11px] text-[#C4A98A] mb-3">ยังไม่มีตัวเลือก</p>';

  box.html(`
    ${groupsHtml}
    <div class="border-t border-[#F0E0D4] pt-3 mt-1">
      <p class="text-[11px] font-bold text-[#9D7F6A] mb-2">+ เพิ่มตัวเลือกใหม่</p>
      <div class="flex flex-wrap gap-1.5 mb-1.5">
        <input id="on-group-${menuId}" placeholder="กลุ่ม เช่น โปรตีน, ระดับเผ็ด"
          class="rounded-lg border border-[#F0E0D4] px-2.5 py-1.5 text-[11px] outline-none focus:border-[#E12717]" style="flex:1;min-width:120px"/>
        <input id="on-name-${menuId}" placeholder="ชื่อ เช่น หมู, เผ็ดน้อย"
          class="rounded-lg border border-[#F0E0D4] px-2.5 py-1.5 text-[11px] outline-none focus:border-[#E12717]" style="flex:1;min-width:100px"/>
        <input id="on-price-${menuId}" type="number" min="0" placeholder="ราคา (0=ฟรี)"
          class="rounded-lg border border-[#F0E0D4] px-2.5 py-1.5 text-[11px] outline-none w-20"/>
      </div>
      <div class="flex items-center gap-2 mb-2">
        <label class="flex items-center gap-1 text-[11px] text-[#7C5B47] cursor-pointer">
          <input type="checkbox" id="on-req-${menuId}" class="rounded"/> บังคับเลือก
        </label>
      </div>
      <button onclick="saveOption('${menuId}')"
        class="rounded-lg btn-red px-3 py-1.5 text-[11px] font-semibold text-white">บันทึก</button>
    </div>
  `);
}

function escDash(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function saveOption(menuId){
  const group=$('#on-group-'+menuId).val().trim();
  const name=$('#on-name-'+menuId).val().trim();
  const price=parseFloat($('#on-price-'+menuId).val())||0;
  const required=$('#on-req-'+menuId).is(':checked')?1:0;
  if(!group||!name) return alert('กรุณากรอกกลุ่มและชื่อตัวเลือก');
  $.ajax({url:'api/menu_options.php',method:'POST',contentType:'application/json',
    data:JSON.stringify({menuId,groupName:group,optName:name,price,required}),
    success:function(){ $.getJSON('api/menu_options.php?menu_id='+encodeURIComponent(menuId)).then(g=>renderOptionsBox(menuId,g)); },
    error:function(){ alert('บันทึกไม่สำเร็จ'); }
  });
}

function deleteOption(optId, menuId){
  if(!confirm('ลบตัวเลือกนี้?')) return;
  $.ajax({url:'api/menu_options.php?id='+encodeURIComponent(optId),method:'DELETE',
    success:function(){ $.getJSON('api/menu_options.php?menu_id='+encodeURIComponent(menuId)).then(g=>renderOptionsBox(menuId,g)); },
    error:function(){ alert('ลบไม่สำเร็จ'); }
  });
}

/* ─── QR image live preview in settings ─── */
$(document).on('input','#s-promptpay',function(){
  const url=$(this).val().trim();
  const wrap=$('#s-qr-preview-wrap'), img=$('#s-qr-preview');
  if(url){ img.attr('src',url); wrap.removeClass('hidden'); }
  else { wrap.addClass('hidden'); }
});

loadAll();
</script>
</body>
</html>
