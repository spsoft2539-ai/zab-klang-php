<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>ตะกร้า · แซ่บกลางซอย</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>
body{font-family:'Sarabun',sans-serif;background:#FFF9F5;}
/* iOS safe area — รอยบากและแถบท้าย */
.safe-bottom{padding-bottom:max(20px, env(safe-area-inset-bottom));}
.safe-bottom-pad{padding-bottom:calc(5rem + env(safe-area-inset-bottom));}
</style>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet"/>
</head>
<body class="mx-auto flex min-h-screen max-w-sm flex-col bg-[#FFF9F5]">

<!-- Page: empty / submit-success / main-cart  — all rendered by JS -->
<div id="page-root"></div>

<script>
const CART_KEY = 'zab_cart';
const TEAR_BG = `url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 14 12'><polygon points='0,0 14,0 14,4 7,12 0,4' fill='%23FCF7EF'/></svg>")`;

let cart = {tableId:'', items:[]};

function readCart(){
  try{ const d=JSON.parse(localStorage.getItem(CART_KEY)||'{}'); return {tableId:d.tableId||'',items:d.items||[]}; }
  catch(e){ return {tableId:'',items:[]}; }
}
function saveCart(){ localStorage.setItem(CART_KEY, JSON.stringify(cart)); }
function clearCart(){ localStorage.removeItem(CART_KEY); }
function fmtMoney(n){ return '฿'+Number(n).toLocaleString('th-TH'); }
function itemCount(){ return cart.items.reduce((s,i)=>s+i.quantity,0); }
function subtotal(){ return cart.items.reduce((s,i)=>s+i.price*i.quantity,0); }

function increase(menuId){
  cart.items = cart.items.map(i=>i.menuId===menuId?{...i,quantity:Math.min(99,i.quantity+1)}:i);
  saveCart(); renderCart();
}
function decrease(menuId){
  cart.items = cart.items.map(i=>i.menuId===menuId?{...i,quantity:i.quantity-1}:i).filter(i=>i.quantity>0);
  saveCart(); renderCart();
}

function renderSuccess(orderId){
  const t = cart.tableId;
  $('#page-root').html(`
    <main class="mx-auto flex min-h-screen max-w-sm flex-col items-center justify-center bg-[#FFF9F5] px-6 text-center">
      <div class="flex h-20 w-20 items-center justify-center rounded-3xl text-white" style="background:linear-gradient(135deg,#FF5546,#F23A2B,#D32316);box-shadow:0 16px 32px rgba(225,39,23,0.28)">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <h1 class="mt-5 text-[22px] font-bold text-[#2C1713]">ส่งออเดอร์แล้ว!</h1>
      <p class="mt-2 text-[13px] leading-6 text-[#9D7F6A]">
        ออเดอร์โต๊ะ <span class="font-semibold text-[#2C1713]">${t}</span> ถูกส่งไปยังครัวแล้ว
      </p>
      <div class="mt-3 rounded-2xl bg-white px-5 py-3 ring-1 ring-[#F0E0D4]">
        <p class="text-[10px] text-[#9D7F6A] uppercase tracking-widest">เลขออเดอร์</p>
        <p class="mt-0.5 font-mono text-[13px] font-semibold text-[#2C1713]">${orderId}</p>
      </div>
      <p class="mt-5 text-[12px] text-[#9D7F6A]">รอครู่น้อย ๆ อาหารกำลังถูกปรุง 🍳</p>
      <a href="menu.php?table=${encodeURIComponent(t)}"
        class="mt-8 inline-flex items-center gap-1.5 rounded-full bg-[#2C1713] px-6 py-3 text-[13px] font-medium text-white shadow-[0_12px_28px_rgba(44,23,19,0.18)]">
        ✨ สั่งเพิ่ม
      </a>
    </main>`);
}

function renderEmpty(){
  const href = cart.tableId ? `menu.php?table=${encodeURIComponent(cart.tableId)}` : 'index.php';
  $('#page-root').html(`
    <main class="mx-auto flex min-h-screen max-w-sm flex-col items-center justify-center bg-[#FFF9F5] px-5 py-16 text-center">
      <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white shadow-[0_12px_30px_rgba(44,23,19,0.08)] ring-1 ring-black/5">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#C8A48B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" x2="21" y1="6" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </div>
      <p class="mt-5 text-[15px] font-medium text-[#2C1713]">ตะกร้ายังว่างอยู่</p>
      <p class="mt-1 max-w-[220px] text-[12px] leading-5 text-[#9D7F6A]">กลับไปเลือกเมนูเด็ดของร้าน</p>
      <a href="${href}" class="mt-6 inline-flex items-center gap-1.5 rounded-full bg-[#2C1713] px-5 py-3 text-[13px] font-medium text-white shadow-[0_12px_28px_rgba(44,23,19,0.18)]">
        ✨ กลับไปดูเมนู
      </a>
    </main>`);
}

function renderCart(){
  const sub = subtotal();
  const cnt = itemCount();
  const backHref = cart.tableId ? `menu.php?table=${encodeURIComponent(cart.tableId)}` : 'index.php';
  const now = new Date().toLocaleString('th-TH',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit',hour12:false,timeZone:'Asia/Bangkok'});

  const itemsHtml = cart.items.map(item=>`
    <li data-id="${item.menuId}">
      <div class="flex items-start justify-between gap-3">
        <p class="text-[13px] font-medium leading-snug text-[#2C1713]">${item.name}</p>
        <span class="shrink-0 text-[13px] font-semibold tabular-nums text-[#2C1713]">${fmtMoney(item.price*item.quantity)}</span>
      </div>
      <div class="mt-2 flex items-center justify-between">
        <span class="text-[11px] tabular-nums text-[#9D7F6A]">${fmtMoney(item.price)} × ${item.quantity}</span>
        <div class="flex items-center gap-1 rounded-full bg-white px-1 py-1 ring-1 ring-[#E5D2BC]">
          <button type="button" data-action="dec" data-id="${item.menuId}"
            class="flex h-6 w-6 items-center justify-center rounded-full text-[#5A4338] active:bg-[#FBF4ED]">
            ${item.quantity===1
              ? '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>'
              : '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>'}
          </button>
          <span class="min-w-[16px] text-center text-[11px] font-semibold tabular-nums text-[#2C1713]">${item.quantity}</span>
          <button type="button" data-action="inc" data-id="${item.menuId}"
            class="flex h-6 w-6 items-center justify-center rounded-full bg-[#E12717] text-white shadow-[0_2px_5px_rgba(225,39,23,0.32)]">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
          </button>
        </div>
      </div>
    </li>`).join('');

  $('#page-root').html(`
    <main class="mx-auto flex min-h-screen max-w-sm flex-col bg-[#FFF9F5]">
      <!-- Header -->
      <div class="sticky top-0 z-30 border-b border-[#F0E2D4]/60 bg-[#FFF9F5]/85 px-5 py-4 backdrop-blur-md">
        <div class="flex items-center gap-3">
          <a href="${backHref}" class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white text-[#2C1713] shadow-[0_4px_12px_rgba(44,23,19,0.06)] ring-1 ring-black/5 transition-transform active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
          </a>
          <div class="flex-1">
            ${cart.tableId?`<p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[#B89A82]">โต๊ะ ${cart.tableId}</p>`:''}
            <h1 class="text-[17px] font-semibold text-[#2C1713]">ตะกร้าของคุณ</h1>
          </div>
          <div class="flex h-10 items-center justify-center rounded-2xl bg-white px-3 text-[12px] font-medium text-[#2C1713] shadow-[0_4px_12px_rgba(44,23,19,0.06)] ring-1 ring-black/5">${cnt} ชิ้น</div>
        </div>
      </div>

      <!-- Receipt -->
      <section class="px-5 pt-5">
        <div style="filter:drop-shadow(0 10px 24px rgba(44,23,19,0.07))">
          <div class="rounded-t-[4px] bg-[#FCF7EF] px-6 pb-5 pt-7">
            <div class="flex flex-col items-center">
              <img src="logo.png" alt="แซ่บกลางซอย" class="h-24 w-24 object-contain" onerror="this.style.display='none'"/>
            </div>
            <div class="mt-5 flex items-center gap-2.5">
              <div class="h-px flex-1 border-t border-dashed border-[#D9C4B0]"></div>
              <span class="text-[8px] text-[#C8A48B]">◆ ◆ ◆</span>
              <div class="h-px flex-1 border-t border-dashed border-[#D9C4B0]"></div>
            </div>
            <dl class="mt-4 space-y-1.5 text-[11px]">
              <div class="flex justify-between"><dt class="text-[#9D7F6A]">โต๊ะ</dt><dd class="font-medium text-[#2C1713]">${cart.tableId||'—'}</dd></div>
              <div class="flex justify-between"><dt class="text-[#9D7F6A]">วัน-เวลา</dt><dd class="font-medium tabular-nums text-[#2C1713]">${now}</dd></div>
            </dl>
            <div class="mt-4 border-t border-dashed border-[#D9C4B0] pt-3">
              <div class="flex items-center justify-between text-[9px] font-medium uppercase tracking-[0.2em] text-[#B89A82]">
                <span>รายการ · Item</span><span>ยอด · Amount</span>
              </div>
            </div>
            <ul class="mt-4 space-y-5">${itemsHtml}</ul>
            <a href="${backHref}" class="mt-5 flex items-center justify-center gap-1.5 rounded-xl border border-dashed border-[#C8A48B] bg-[#FCF7EF] py-2.5 text-[11px] font-medium text-[#7C5B47]">
              + เพิ่มเมนูอื่น
            </a>
            <div class="mt-5 border-t border-dashed border-[#D9C4B0] pt-3">
              <div class="flex items-center justify-between text-[12px]">
                <span class="text-[#9D7F6A]">จำนวน</span>
                <span class="font-medium tabular-nums text-[#5A4338]">${cnt} ชิ้น</span>
              </div>
              <div class="mt-1.5 flex items-center justify-between text-[12px]">
                <span class="text-[#9D7F6A]">ยอดอาหาร</span>
                <span class="font-medium tabular-nums text-[#5A4338]">${fmtMoney(sub)}</span>
              </div>
            </div>
            <div class="mt-3 border-t-2 border-double border-[#D9C4B0] pt-3">
              <div class="flex items-baseline justify-between">
                <span class="text-[13px] font-semibold text-[#2C1713]">รวมรอบนี้</span>
                <span class="text-[24px] font-bold leading-none tabular-nums text-[#2C1713]">${fmtMoney(sub)}</span>
              </div>
            </div>
            <div class="mt-5 flex items-center gap-2.5">
              <div class="h-px flex-1 border-t border-dashed border-[#D9C4B0]"></div>
              <span class="text-[8px] tracking-[0.3em] text-[#C8A48B]">THANK YOU</span>
              <div class="h-px flex-1 border-t border-dashed border-[#D9C4B0]"></div>
            </div>
            <p class="mt-2 text-center text-[10px] text-[#9D7F6A]">ขอบคุณที่อุดหนุนค่ะ</p>
          </div>
          <div class="block h-3 w-full" style="background-image:${TEAR_BG};background-size:14px 12px;background-repeat:repeat-x" aria-hidden="true"></div>
        </div>
      </section>

      <div class="h-36"></div>

      <!-- Bottom actions -->
      <div class="fixed inset-x-0 bottom-0 z-40">
        <div class="mx-auto max-w-sm">
          <div class="pointer-events-none h-6 bg-gradient-to-t from-[#FFF9F5] to-transparent"></div>
          <div class="bg-[#FFF9F5] px-5 pt-2 safe-bottom">
            <button id="submitBtn" type="button"
              class="relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-[20px] px-4 py-4 text-[14px] font-semibold text-white transition-transform active:scale-[0.98]"
              style="background:linear-gradient(135deg,#FF5546,#F23A2B,#D32316);box-shadow:0 16px 32px rgba(225,39,23,0.32)">
              <span class="pointer-events-none absolute inset-x-0 top-0 h-1/2 bg-gradient-to-b from-white/16 to-transparent"></span>
              <span class="relative">ยืนยันสั่ง · ${fmtMoney(sub)}</span>
              <svg class="relative" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </button>
          </div>
        </div>
      </div>
    </main>`);

  // Bind events
  $(document).off('click','[data-action]').on('click','[data-action]',function(){
    const id=$(this).data('id'), action=$(this).data('action');
    if(action==='inc') increase(id);
    else decrease(id);
  });
  $('#submitBtn').off('click').on('click', submitOrder);
}

function submitOrder(){
  if(!cart.tableId || !cart.items.length) return;
  $('#submitBtn').prop('disabled',true).html('<span class="relative">กำลังส่งออเดอร์...</span>');
  $.ajax({
    url:'api/orders.php', method:'POST', contentType:'application/json',
    data: JSON.stringify({
      tableId: cart.tableId,
      items: cart.items.map(i=>({menuId:i.menuId,name:i.name,price:i.price,quantity:i.quantity}))
    }),
    success: function(order){
      clearCart();
      // Redirect to orders tracking page
      window.location.href = 'orders.php?table=' + encodeURIComponent(cart.tableId);
    },
    error: function(){
      alert('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
      $('#submitBtn').prop('disabled',false);
      renderCart();
    }
  });
}

// ─── Init ────────────────────────────────────────
cart = readCart();
if(!cart.items.length) renderEmpty();
else renderCart();
</script>
</body>
</html>
