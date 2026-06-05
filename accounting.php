<?php require_once 'auth.php'; requireRole(['owner','manager']); $user = getCurrentUser(); ?>
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
.tab-active{border-bottom-color:#E12717!important;color:#E12717!important;}
.bar-col{display:flex;flex-direction:column;align-items:center;gap:3px;flex:1;min-width:0;}
.bar-fill{width:100%;border-radius:6px 6px 0 0;background:linear-gradient(to top,#E12717,#FF6B5B);transition:height .5s;}
@media print{
  nav,.no-print{display:none!important;}
  body{background:#fff!important;}
  .print-card{break-inside:avoid;}
}
</style>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet"/>
</head>
<body>

<!-- Navbar -->
<nav class="sticky top-0 z-40 border-b border-black/8 bg-white/80 backdrop-blur-md px-5 py-3.5 flex items-center justify-between">
  <div class="flex items-center gap-3">
    <a href="index.php" class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#F7F3EF] text-[#4A3728] hover:bg-[#EFE8E0]">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
    </a>
    <h1 class="text-[17px] font-bold text-[#2C1713]">บัญชี</h1>
  </div>
  <div class="flex items-center gap-2">
    <span class="hidden sm:inline text-[11px] text-[#C4A98A]"><?= htmlspecialchars($user['name']) ?></span>
    <button onclick="printReport()" class="flex h-9 items-center gap-1.5 rounded-xl border border-[#E8D6C6] bg-white px-3 text-[11px] font-medium text-[#7C5B47] hover:bg-[#FFF3EC] no-print">
      🖨 พิมพ์
    </button>
    <button onclick="exportCsv()" class="flex h-9 items-center gap-1.5 rounded-xl border border-[#E8D6C6] bg-white px-3 text-[11px] font-medium text-[#7C5B47] hover:bg-[#FFF3EC] no-print">
      ⬇ CSV
    </button>
    <a href="logout.php" onclick="return confirm('ออกจากระบบ?')"
      class="flex h-9 items-center gap-1 rounded-xl border border-[#F0E0D4] bg-[#F7F3EF] px-3 text-[11px] font-medium text-[#7C5B47] hover:bg-red-50 hover:text-red-600 no-print">
      🚪
    </a>
  </div>
</nav>

<!-- Tabs -->
<div class="border-b border-[#F0E0D4] bg-white px-2 overflow-x-auto no-print">
  <div class="flex gap-0 min-w-max">
    <?php foreach([
      ['id'=>'overview','label'=>'ภาพรวม'],
      ['id'=>'chart',   'label'=>'กราฟรายได้'],
      ['id'=>'top',     'label'=>'เมนูขายดี'],
      ['id'=>'history', 'label'=>'ประวัติบิล'],
    ] as $i=>$tab): ?>
    <button data-tab="<?= $tab['id'] ?>"
      class="acc-tab border-b-2 border-transparent px-5 py-3.5 text-[13px] font-medium text-[#9D7F6A] whitespace-nowrap <?= $i===0?'tab-active':'' ?>">
      <?= $tab['label'] ?>
    </button>
    <?php endforeach; ?>
  </div>
</div>

<!-- Content -->
<div id="tab-content" class="max-w-4xl mx-auto p-4">
  <div class="py-10 text-center text-[#9D7F6A]">กำลังโหลด...</div>
</div>

<script>
let currentTab = 'overview';
let summary = {}, bills = [], allBills = [];

function fmtMoney(n){ return '฿'+Number(n||0).toLocaleString('th-TH'); }
function fmtDate(ms){ return new Date(parseInt(ms)).toLocaleDateString('th-TH',{day:'2-digit',month:'short',year:'numeric',timeZone:'Asia/Bangkok'}); }

/* ── Date range state ── */
let rangeFrom = 0, rangeTo = Date.now();

function loadAll(){
  return Promise.all([
    $.getJSON('api/accounting_summary.php?from='+rangeFrom+'&to='+rangeTo),
    $.getJSON('api/bills.php'),
  ]).then(([s, b]) => {
    summary = s;
    allBills = b;
    bills = filterBillsByRange(b);
    renderTab();
  });
}

function filterBillsByRange(b){
  if(!rangeFrom) return b;
  return b.filter(x => x.closedAtMs >= rangeFrom && x.closedAtMs <= rangeTo);
}

function renderTab(){
  const fns = { overview:renderOverview, chart:renderChart, top:renderTop, history:renderHistory };
  if(fns[currentTab]) fns[currentTab]();
}

/* ═══════════════════════════════════════
   TAB: ภาพรวม
═══════════════════════════════════════ */
function renderOverview(){
  const s = summary;
  const r = s.range || {};
  const cashPct = r.revenue>0 ? Math.round(r.cashRev/r.revenue*100) : 50;
  $('#tab-content').html(`
    <!-- Date range picker -->
    <div class="mb-5 rounded-[20px] bg-white p-4 ring-1 ring-[#F0E0D4] no-print">
      <p class="text-[12px] font-semibold text-[#9D7F6A] mb-2">📅 เลือกช่วงวันที่</p>
      <div class="flex flex-wrap gap-2 items-center">
        <input type="date" id="date-from" class="rounded-xl border border-[#F0E0D4] px-3 py-2 text-[12px] outline-none focus:border-[#E12717]"/>
        <span class="text-[#9D7F6A] text-[12px]">ถึง</span>
        <input type="date" id="date-to" class="rounded-xl border border-[#F0E0D4] px-3 py-2 text-[12px] outline-none focus:border-[#E12717]"/>
        <button onclick="applyRange()" class="rounded-xl btn-red px-4 py-2 text-[12px] font-bold text-white">ค้นหา</button>
        <button onclick="clearRange()" class="rounded-xl border border-[#E8D6C6] bg-white px-4 py-2 text-[12px] text-[#7C5B47]">ล้าง</button>
      </div>
      ${rangeFrom ? `<p class="mt-2 text-[11px] text-[#E12717]">📌 กรองช่วง: ${fmtDate(rangeFrom)} – ${fmtDate(rangeTo)}</p>` : ''}
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-5 print-card">
      ${[
        {label:'รายได้วันนี้',   val:fmtMoney(s.todayRevenue), sub:s.todayBills+' บิล',  color:'text-[#E12717]'},
        {label:'รายได้สัปดาห์', val:fmtMoney(s.weekRevenue),  sub:s.weekBills+' บิล',   color:'text-[#2D5A1B]'},
        {label:'รายได้ทั้งหมด', val:fmtMoney(s.allRevenue),   sub:s.allBills+' บิล',    color:'text-[#1A1D2E]'},
        {label:'ค่าเฉลี่ย/บิล', val:fmtMoney(s.avgBill),      sub:'เฉลี่ย',             color:'text-[#7C5B47]'},
      ].map(c=>`<div class="rounded-[20px] bg-white p-4 ring-1 ring-[#F0E0D4]">
        <p class="text-[11px] text-[#9D7F6A]">${c.label}</p>
        <p class="mt-1 text-[20px] font-bold ${c.color} tabular-nums">${c.val}</p>
        <p class="text-[11px] text-[#9D7F6A]">${c.sub}</p>
      </div>`).join('')}
    </div>

    <!-- Cash vs Transfer -->
    <div class="rounded-[20px] bg-white p-5 ring-1 ring-[#F0E0D4] mb-5 print-card">
      <p class="text-[13px] font-semibold text-[#2C1713] mb-3">💳 วิธีชำระเงิน ${rangeFrom?'(ช่วงที่เลือก)':'(ทั้งหมด)'}</p>
      <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="rounded-[16px] bg-emerald-50 p-3.5 ring-1 ring-emerald-100">
          <p class="text-[11px] text-emerald-700 font-medium">💵 เงินสด</p>
          <p class="text-[20px] font-bold text-emerald-700 tabular-nums">${fmtMoney(r.cashRev||0)}</p>
          <p class="text-[11px] text-emerald-600">${r.cashCnt||0} บิล · ${cashPct}%</p>
        </div>
        <div class="rounded-[16px] bg-blue-50 p-3.5 ring-1 ring-blue-100">
          <p class="text-[11px] text-blue-700 font-medium">🏦 โอน/QR</p>
          <p class="text-[20px] font-bold text-blue-700 tabular-nums">${fmtMoney(r.transferRev||0)}</p>
          <p class="text-[11px] text-blue-600">${r.transferCnt||0} บิล · ${100-cashPct}%</p>
        </div>
      </div>
      <div class="flex h-3 overflow-hidden rounded-full bg-[#F0E0D4]">
        <div class="bg-emerald-400 rounded-l-full transition-all" style="width:${cashPct}%"></div>
        <div class="flex-1 bg-blue-400 rounded-r-full"></div>
      </div>
    </div>

    <!-- Profit / Loss -->
    <div class="rounded-[20px] bg-white p-5 ring-1 ring-[#F0E0D4] mb-5 print-card">
      <p class="text-[13px] font-semibold text-[#2C1713] mb-3">📊 กำไร-ขาดทุน ${rangeFrom?'(ช่วงที่เลือก)':'(ทั้งหมด)'}</p>
      <div class="grid grid-cols-3 gap-3">
        <div class="rounded-[16px] bg-[#FFF9F5] p-3.5 ring-1 ring-[#F0E0D4]">
          <p class="text-[10px] text-[#9D7F6A]">รายรับ</p>
          <p class="text-[18px] font-bold text-[#2D5A1B] tabular-nums">${fmtMoney(r.revenue||s.allRevenue)}</p>
        </div>
        <div class="rounded-[16px] bg-red-50 p-3.5 ring-1 ring-red-100">
          <p class="text-[10px] text-red-500">รายจ่าย</p>
          <p class="text-[18px] font-bold text-red-600 tabular-nums">${fmtMoney((s.expense||{}).total||(s.expense||{}).allTime||0)}</p>
        </div>
        <div class="rounded-[16px] p-3.5 ring-1 ${((s.expense||{}).profit||(s.expense||{}).allProfit||0)>=0?'bg-emerald-50 ring-emerald-100':'bg-red-50 ring-red-100'}">
          <p class="text-[10px] ${((s.expense||{}).profit||(s.expense||{}).allProfit||0)>=0?'text-emerald-600':'text-red-500'}">กำไร</p>
          <p class="text-[18px] font-bold tabular-nums ${((s.expense||{}).profit||(s.expense||{}).allProfit||0)>=0?'text-emerald-700':'text-red-600'}">
            ${fmtMoney((s.expense||{}).profit||(s.expense||{}).allProfit||0)}
          </p>
        </div>
      </div>
      ${(s.expense||{}).byCategory&&(s.expense||{}).byCategory.length?`
        <div class="mt-3 space-y-1.5 border-t border-[#F0E0D4] pt-3">
          <p class="text-[11px] font-semibold text-[#9D7F6A] mb-1.5">แยกรายจ่ายตามหมวด</p>
          ${(s.expense.byCategory||[]).map(c=>`
            <div class="flex justify-between text-[12px]">
              <span class="text-[#5A4338]">${c.category}</span>
              <span class="font-semibold tabular-nums text-[#2C1713]">${fmtMoney(c.amount)}</span>
            </div>`).join('')}
        </div>` : ''}
    </div>

    <!-- Monthly 6 months -->
    <div class="rounded-[20px] bg-white p-5 ring-1 ring-[#F0E0D4] print-card">
      <p class="text-[13px] font-semibold text-[#2C1713] mb-4">📆 รายได้ 6 เดือนย้อนหลัง</p>
      <div class="space-y-2">
        ${(s.monthly||[]).map(m=>{
          const maxM = Math.max(...(s.monthly||[]).map(x=>x.revenue),1);
          const pct = m.revenue>0 ? Math.max(4, m.revenue/maxM*100) : 0;
          return `<div class="flex items-center gap-3">
            <span class="text-[11px] text-[#9D7F6A] w-14 shrink-0">${m.label}</span>
            <div class="flex-1 h-6 bg-[#F7F3EF] rounded-full overflow-hidden">
              <div class="h-full rounded-full bg-gradient-to-r from-[#E12717] to-[#FF6B5B] transition-all" style="width:${pct}%"></div>
            </div>
            <span class="text-[12px] font-bold text-[#2C1713] tabular-nums w-24 text-right shrink-0">${fmtMoney(m.revenue)}</span>
            <span class="text-[10px] text-[#9D7F6A] w-10 shrink-0">${m.bills} บิล</span>
          </div>`;
        }).join('')}
      </div>
    </div>`);
}

function applyRange(){
  const f=$('#date-from').val(), t=$('#date-to').val();
  if(!f||!t) return alert('กรุณาเลือกวันที่ทั้งสอง');
  rangeFrom = new Date(f+'T00:00:00').getTime();
  rangeTo   = new Date(t+'T23:59:59').getTime();
  loadAll();
}
function clearRange(){
  rangeFrom=0; rangeTo=Date.now();
  $('#date-from,#date-to').val('');
  loadAll();
}

/* ═══════════════════════════════════════
   TAB: กราฟรายได้
═══════════════════════════════════════ */
function renderChart(){
  const days   = summary.daily   || [];
  const maxVal = Math.max(...days.map(d=>d.revenue), 1);

  const barHtml = days.map(d=>{
    const pct   = d.revenue>0 ? Math.max(5, d.revenue/maxVal*100) : 2;
    const label = d.revenue>0 ? '฿'+(d.revenue/1000).toFixed(1)+'k' : '';
    return `<div class="bar-col">
      <span class="text-[9px] text-[#9D7F6A] tabular-nums h-4 leading-4">${label}</span>
      <div class="relative flex-1 w-full flex items-end">
        <div class="bar-fill w-full" style="height:${pct}%;${d.revenue===0?'opacity:0.2':''}"></div>
      </div>
      <span class="text-[9px] text-[#9D7F6A] whitespace-nowrap">${d.label}</span>
      <span class="text-[8px] text-[#C4A98A]">${d.bills} บิล</span>
    </div>`;
  }).join('');

  $('#tab-content').html(`
    <div class="rounded-[20px] bg-white p-5 ring-1 ring-[#F0E0D4] mb-5">
      <p class="text-[14px] font-semibold text-[#2C1713] mb-1">📊 รายได้ 7 วันที่ผ่านมา</p>
      <p class="text-[11px] text-[#9D7F6A] mb-4">รายได้รวม: <strong class="text-[#E12717]">${fmtMoney(days.reduce((s,d)=>s+d.revenue,0))}</strong></p>
      <div class="flex items-end gap-1.5 h-44">${barHtml}</div>
    </div>

    <div class="rounded-[20px] bg-white p-5 ring-1 ring-[#F0E0D4]">
      <p class="text-[14px] font-semibold text-[#2C1713] mb-4">📆 6 เดือนย้อนหลัง</p>
      <div class="space-y-2.5">
        ${(summary.monthly||[]).map(m=>{
          const maxM = Math.max(...(summary.monthly||[]).map(x=>x.revenue),1);
          const pct = m.revenue>0 ? Math.max(4, m.revenue/maxM*100) : 0;
          return `<div class="flex items-center gap-3">
            <span class="text-[12px] text-[#9D7F6A] w-14 shrink-0">${m.label}</span>
            <div class="flex-1 h-7 bg-[#F7F3EF] rounded-full overflow-hidden">
              <div class="h-full rounded-full btn-red transition-all flex items-center px-2" style="width:${pct}%">
                ${pct>15?`<span class="text-[9px] text-white font-bold tabular-nums whitespace-nowrap">${fmtMoney(m.revenue)}</span>`:''}
              </div>
            </div>
            ${pct<=15?`<span class="text-[11px] font-bold text-[#2C1713] tabular-nums">${fmtMoney(m.revenue)}</span>`:'<span></span>'}
            <span class="text-[10px] text-[#9D7F6A] shrink-0">${m.bills} บิล</span>
          </div>`;
        }).join('')}
      </div>
    </div>`);
}

/* ═══════════════════════════════════════
   TAB: เมนูขายดี
═══════════════════════════════════════ */
function renderTop(){
  const items = summary.topItems || [];
  const maxRev = Math.max(...items.map(i=>i.revenue), 1);

  $('#tab-content').html(`
    <div class="flex items-center justify-between mb-4 no-print">
      <div>
        <h2 class="text-[18px] font-bold text-[#2C1713]">🏆 เมนูขายดี Top 10</h2>
        <p class="text-[11px] text-[#9D7F6A]">${rangeFrom?`ช่วง ${fmtDate(rangeFrom)} – ${fmtDate(rangeTo)}`:'ทั้งหมด'}</p>
      </div>
    </div>
    ${!items.length
      ? `<div class="rounded-[20px] border border-dashed border-[#E8D6C6] bg-white py-12 text-center">
          <p class="text-[28px] mb-2">🍽</p>
          <p class="text-[13px] text-[#9D7F6A]">ยังไม่มีข้อมูล</p>
        </div>`
      : `<div class="space-y-2">
          ${items.map((item,idx)=>{
            const pct = Math.max(8, item.revenue/maxRev*100);
            const medals = ['🥇','🥈','🥉'];
            const badge = medals[idx] || `<span class="text-[12px] font-bold text-[#9D7F6A]">${idx+1}</span>`;
            return `<div class="rounded-[18px] bg-white p-4 ring-1 ring-[#F0E0D4]">
              <div class="flex items-center gap-3 mb-2">
                <span class="text-[20px] w-7 text-center shrink-0">${badge}</span>
                <div class="flex-1 min-w-0">
                  <p class="text-[13px] font-semibold text-[#2C1713] truncate">${item.name}</p>
                  <p class="text-[11px] text-[#9D7F6A]">ขาย ${item.qty} ชิ้น</p>
                </div>
                <span class="text-[15px] font-bold text-[#E12717] tabular-nums shrink-0">${fmtMoney(item.revenue)}</span>
              </div>
              <div class="h-2 bg-[#F7F3EF] rounded-full overflow-hidden">
                <div class="h-full rounded-full btn-red" style="width:${pct}%"></div>
              </div>
            </div>`;
          }).join('')}
        </div>`
    }`);
}

/* ═══════════════════════════════════════
   TAB: ประวัติบิล
═══════════════════════════════════════ */
let billFilter = 'today';
function renderHistory(){
  const filtered = billFilter==='today'
    ? allBills.filter(b=>b.closedAtMs >= new Date().setHours(0,0,0,0))
    : allBills;

  $('#tab-content').html(`
    <div class="flex items-center justify-between mb-4 no-print">
      <div>
        <h2 class="text-[18px] font-bold text-[#2C1713]">ประวัติบิล</h2>
        <p class="text-[11px] text-[#9D7F6A]">${filtered.length} บิล</p>
      </div>
      <div class="flex gap-2">
        <button data-bf="today" onclick="setBillFilter('today')"
          class="bf-btn h-8 rounded-full px-3 text-[11px] font-medium ${billFilter==='today'?'btn-red text-white':'bg-white text-[#7C5B47] ring-1 ring-[#F0E0D4]'}">วันนี้</button>
        <button data-bf="all" onclick="setBillFilter('all')"
          class="bf-btn h-8 rounded-full px-3 text-[11px] font-medium ${billFilter==='all'?'btn-red text-white':'bg-white text-[#7C5B47] ring-1 ring-[#F0E0D4]'}">ทั้งหมด</button>
      </div>
    </div>
    ${!filtered.length
      ? `<div class="rounded-[20px] border border-dashed border-[#E8D6C6] bg-white py-12 text-center">
          <p class="text-[28px] mb-2">🧾</p>
          <p class="text-[13px] text-[#9D7F6A]">ยังไม่มีบิล</p>
        </div>`
      : `<div class="space-y-3">${filtered.map(b => renderBillCard(b)).join('')}</div>`
    }`);
}

function setBillFilter(f){ billFilter=f; renderHistory(); }

function renderBillCard(b){
  const pm = b.paymentMethod==='transfer' ? '🏦 โอน/QR' : '💵 เงินสด';
  const pmColor = b.paymentMethod==='transfer' ? 'bg-blue-500' : 'bg-emerald-500';
  const itemsHtml = b.items.map(i=>`
    <div class="flex justify-between text-[11px] text-[#7C5B47]">
      <span>${i.name}${i.note?` <span class="text-[#9D7F6A]">(${i.note})</span>`:''} ×${i.quantity}</span>
      <span class="tabular-nums">${fmtMoney(i.price*i.quantity)}</span>
    </div>`).join('');
  return `<div class="overflow-hidden rounded-[18px] bg-white ring-1 ring-[#F0E0D4]">
    <button type="button" onclick="toggleBillAcc('${b.id}')"
      class="flex w-full items-center gap-3 px-4 py-3.5 text-left hover:bg-[#FFF9F5]">
      <div class="flex h-9 w-9 items-center justify-center rounded-xl text-white ${pmColor} text-[15px] shrink-0">
        ${b.paymentMethod==='transfer'?'🏦':'💵'}
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-[13px] font-semibold text-[#2C1713]">โต๊ะ ${b.tableId}
          <span class="ml-1 text-[10px] font-normal text-[#9D7F6A]">${b.id}</span></p>
        <p class="text-[11px] text-[#9D7F6A]">${fmtDate(b.closedAtMs)} · ${b.closedAt} น.${b.guests?' · '+b.guests+' คน':''}</p>
      </div>
      <div class="text-right shrink-0">
        <p class="text-[15px] font-bold text-[#2C1713] tabular-nums">${fmtMoney(b.total)}</p>
        <p class="text-[10px] text-[#9D7F6A]">${pm}</p>
      </div>
      <span class="acc-chv-${b.id} text-[#9D7F6A] text-[11px] ml-1">▼</span>
    </button>
    <div class="acc-det-${b.id} hidden border-t border-[#F0E0D4] px-4 pb-3 pt-2.5">
      <div class="space-y-1 mb-2">${itemsHtml}</div>
      <div class="border-t border-dashed border-[#F0E0D4] pt-2 space-y-1 text-[11px]">
        <div class="flex justify-between text-[#9D7F6A]"><span>ยอดอาหาร</span><span>${fmtMoney(b.subtotal)}</span></div>
        ${b.vat>0?`<div class="flex justify-between text-[#9D7F6A]"><span>VAT ${b.vatRate}%</span><span>${fmtMoney(b.vat)}</span></div>`:''}
        <div class="flex justify-between font-semibold text-[#2C1713]"><span>รวม</span><span>${fmtMoney(b.total)}</span></div>
        ${b.cashReceived?`<div class="flex justify-between text-[#9D7F6A]"><span>รับเงิน</span><span>${fmtMoney(b.cashReceived)}</span></div>`:''}
        ${b.change>0?`<div class="flex justify-between text-emerald-600 font-medium"><span>เงินทอน</span><span>${fmtMoney(b.change)}</span></div>`:''}
      </div>
    </div>
  </div>`;
}

function toggleBillAcc(id){
  const det=$('.acc-det-'+id), chv=$('.acc-chv-'+id);
  det.toggleClass('hidden');
  chv.text(det.hasClass('hidden')?'▼':'▲');
}

/* ═══════════════════════════════════════
   Export CSV
═══════════════════════════════════════ */
function exportCsv(){
  const src = rangeFrom ? allBills.filter(b=>b.closedAtMs>=rangeFrom&&b.closedAtMs<=rangeTo) : allBills;
  const rows = [['บิล','โต๊ะ','วันที่','เวลา','วิธีชำระ','ยอดอาหาร','VAT','รวม','รับเงิน','เงินทอน','ลูกค้า']];
  src.forEach(b=>rows.push([
    b.id, b.tableId, fmtDate(b.closedAtMs), b.closedAt,
    b.paymentMethod==='transfer'?'โอนเงิน':'เงินสด',
    b.subtotal, b.vat, b.total,
    b.cashReceived||'', b.change||'', b.guests||''
  ]));
  const csv = '﻿' + rows.map(r=>r.map(c=>`"${String(c).replace(/"/g,'""')}"`).join(',')).join('\n');
  const a = document.createElement('a');
  a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
  a.download = 'saep_klang_soi_' + new Date().toISOString().slice(0,10) + '.csv';
  a.click();
}

/* ═══════════════════════════════════════
   Print Report
═══════════════════════════════════════ */
function printReport(){
  currentTab = 'overview';
  renderTab();
  setTimeout(()=>window.print(), 300);
}

/* ═══════════════════════════════════════
   Tab switching
═══════════════════════════════════════ */
$(document).on('click','.acc-tab',function(){
  currentTab = $(this).data('tab');
  $('.acc-tab').removeClass('tab-active');
  $(this).addClass('tab-active');
  renderTab();
});

loadAll();
</script>
</body>
</html>
