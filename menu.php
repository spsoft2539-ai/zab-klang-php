<?php
// Customer menu page — menu.php?table=A1
$tableId = strtoupper(trim($_GET['table'] ?? ''));
if (!$tableId) { header('Location: index.php'); exit; }
?><!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>เมนู · แซ่บกลางซอย</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>
body{font-family:'Sarabun',sans-serif;background:#FFF9F5;}
.btn-red{background:linear-gradient(135deg,#FF5546,#F23A2B,#C41E0E);}
.cat-scroll::-webkit-scrollbar{display:none;}
.cat-scroll{scrollbar-width:none;}
/* iOS safe area */
.safe-bottom{padding-bottom:max(20px,env(safe-area-inset-bottom));}
.pb-safe{padding-bottom:calc(7rem + env(safe-area-inset-bottom));}
.tag-spicy{background:#FCEBEB;color:#A32D2D;}
.tag-hit{background:#EAF3DE;color:#3B6D11;}
.tag-pro{background:#FAEEDA;color:#854F0B;}
</style>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet"/>
</head>
<body class="mx-auto min-h-screen max-w-sm bg-[#FFF9F5] pb-28">

<!-- Header -->
<header id="menu-header" class="relative overflow-hidden bg-gradient-to-br from-[#FF5546] via-[#F23A2B] to-[#C41E0E] px-5 pt-12 pb-6 text-white">
  <div class="absolute inset-0 opacity-15" style="background:radial-gradient(circle at 80% 20%,#fff 0%,transparent 60%)"></div>
  <div class="relative">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-[11px] font-medium uppercase tracking-[0.22em] text-white/60">โต๊ะ · Table</p>
        <p class="text-[28px] font-bold leading-tight"><?= htmlspecialchars($tableId) ?></p>
      </div>
      <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
      </div>
    </div>
    <p id="restaurant-name" class="mt-2 text-[17px] font-semibold">แซ่บกลางซอย</p>
    <p id="restaurant-sub" class="mt-0.5 text-[12px] text-white/70">อีสาน · ซีฟู้ด · หมูกระทะ · เปิดถึง 22.00 น.</p>

    <!-- Search -->
    <div class="mt-4 flex items-center gap-2.5 rounded-2xl bg-white/15 px-4 py-2.5 backdrop-blur-sm">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 text-white/60" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
      <input id="search" type="text" placeholder="ค้นหาเมนู..." class="flex-1 bg-transparent text-[13px] text-white placeholder:text-white/50 outline-none"/>
    </div>
  </div>
</header>

<!-- Category nav -->
<nav id="cat-nav" class="cat-scroll flex gap-2 overflow-x-auto px-5 py-4"></nav>

<!-- Menu items -->
<section id="menu-list" class="space-y-3 px-5">
  <div class="py-10 text-center text-[13px] text-[#9D7F6A]">กำลังโหลดเมนู...</div>
</section>

<!-- Floating cart bar -->
<div class="fixed inset-x-0 bottom-0 z-40 pointer-events-none">
  <div class="mx-auto max-w-sm pointer-events-auto">
    <div class="h-5 bg-gradient-to-t from-[#FFF9F5] to-transparent"></div>
    <div class="bg-[#FFF9F5]/98 px-5 safe-bottom backdrop-blur-md" id="cart-bar-wrap">
      <!-- filled by JS -->
    </div>
  </div>
</div>

<script>
const TABLE_ID = <?= json_encode($tableId) ?>;
const CART_KEY = 'zab_cart';
let menuItems = [];
let cart = [];
let activeCategory = 'ทั้งหมด';
let searchQuery = '';

// ─── Cart helpers ───────────────────────────────
function readCart() {
  try {
    const raw = localStorage.getItem(CART_KEY);
    if (!raw) return [];
    const d = JSON.parse(raw);
    if (d.tableId !== TABLE_ID) return [];
    return d.items || [];
  } catch(e){ return []; }
}
function saveCart() {
  localStorage.setItem(CART_KEY, JSON.stringify({tableId: TABLE_ID, items: cart}));
}
function cartCount() { return cart.reduce((s,i)=>s+i.quantity,0); }
function cartSubtotal() { return cart.reduce((s,i)=>s+i.price*i.quantity,0); }
function cartTotal() { return cartSubtotal() + Math.round(cartSubtotal()*0.07); }

function addItem(item) {
  const ex = cart.find(c=>c.menuId===item.id);
  if (ex) { ex.quantity = Math.min(99, ex.quantity+1); }
  else { cart.push({menuId:item.id,name:item.name,price:item.price,quantity:1,image:item.image,category:item.category}); }
  saveCart();
  renderMenu();
  renderCartBar();
}

// ─── Render helpers ──────────────────────────────
function fmtMoney(n) { return '฿' + n.toLocaleString('th-TH'); }

function renderCatNav() {
  const cats = ['ทั้งหมด', ...new Set(menuItems.map(m=>m.category))];
  const counts = {};
  counts['ทั้งหมด'] = menuItems.length;
  menuItems.forEach(m=>{ counts[m.category]=(counts[m.category]||0)+1; });

  $('#cat-nav').html(cats.map(cat=>{
    const active = cat===activeCategory;
    const cnt = counts[cat]||0;
    return `<button type="button" data-cat="${cat}"
      class="flex h-9 shrink-0 items-center gap-1.5 rounded-full px-4 text-xs font-medium transition-colors
        ${active?'bg-[#E12717] text-white shadow-[0_8px_18px_rgba(225,39,23,0.22)]':'bg-white text-[#7C5B47] ring-1 ring-[#F0E0D4]'}">
      ${cat}
      <span class="min-w-[18px] rounded-full px-1.5 py-0.5 text-[10px] tabular-nums ${active?'bg-white/20 text-white':'bg-[#F7EFE7] text-[#A98671]'}">${cnt}</span>
    </button>`;
  }).join(''));
}

function renderMenu() {
  const q = searchQuery.toLowerCase();
  const filtered = menuItems.filter(item=>{
    const matchCat = activeCategory==='ทั้งหมด' || item.category===activeCategory;
    const matchQ = !q || (item.name+' '+item.description+' '+item.category).toLowerCase().includes(q);
    return matchCat && matchQ;
  });

  if (!filtered.length) {
    $('#menu-list').html(`<div class="rounded-[22px] border border-dashed border-[#E8D6C6] bg-white px-5 py-8 text-center">
      <p class="mt-3 text-[14px] font-semibold text-[#2C1713]">ไม่เจอเมนูที่ค้นหา</p>
      <p class="mt-1 text-[11px] text-[#9D7F6A]">ลองเปลี่ยนคำค้นหรือหมวดอาหาร</p></div>`);
    return;
  }

  $('#menu-list').html(filtered.map(item=>{
    const entry = cart.find(c=>c.menuId===item.id);
    const qty = entry ? entry.quantity : 0;
    const tagMap = {เผ็ด:'tag-spicy',ฮิต:'tag-hit',โปร:'tag-pro'};
    const tagHtml = item.tag ? `<span class="shrink-0 rounded-full px-2 py-1 text-[10px] font-medium ${tagMap[item.tag]||''}">${item.tag}</span>` : '';
    const btnInner = qty>0
      ? `<span class="px-1 text-[12px] font-semibold tabular-nums">x${qty}</span>`
      : `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>`;
    return `<article class="flex gap-3 rounded-[22px] border border-[#F0E0D4] bg-white p-3 shadow-[0_8px_20px_rgba(44,23,19,0.04)]">
      <div class="relative h-[82px] w-[82px] shrink-0 overflow-hidden rounded-[18px] bg-[#F7EFE7]">
        <img src="${item.image||'https://placehold.co/160x160/F7EFE7/9D7F6A?text=🍽'}" alt="${item.name}" class="h-full w-full object-cover"/>
      </div>
      <div class="min-w-0 flex-1">
        <div class="flex items-start justify-between gap-2">
          <div class="min-w-0">
            <h2 class="line-clamp-1 text-[14px] font-semibold text-[#2C1713]">${item.name}</h2>
            <p class="mt-1 line-clamp-2 text-[11px] leading-5 text-[#9D7F6A]">${item.description}</p>
          </div>
          ${tagHtml}
        </div>
        <div class="mt-3 flex items-center justify-between">
          <span class="text-[15px] font-semibold text-[#E12717] tabular-nums">${fmtMoney(item.price)}</span>
          <button type="button" data-id="${item.id}"
            class="add-btn relative flex h-9 min-w-9 items-center justify-center rounded-full px-2 text-white transition-transform active:scale-95"
            style="background:linear-gradient(135deg,#FF5546,#F23A2B,#D32316);box-shadow:0 8px 16px rgba(225,39,23,0.28)">
            ${btnInner}
          </button>
        </div>
      </div>
    </article>`;
  }).join(''));
}

function renderCartBar() {
  const cnt = cartCount();
  const total = cartTotal();
  if (!cnt) {
    $('#cart-bar-wrap').html(`<div class="flex items-center gap-3 rounded-[20px] border border-dashed border-[#E8D6C6] bg-white px-4 py-3.5">
      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#FFF3EC] text-[#E12717]">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" x2="21" y1="6" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </div>
      <p class="text-[12px] text-[#9D7F6A]">กดปุ่ม + ที่เมนูเพื่อเริ่มสั่งอาหาร</p>
    </div>`);
  } else {
    $('#cart-bar-wrap').html(`<a href="cart.php"
      class="flex items-center gap-3 rounded-[20px] px-4 py-3.5 text-white shadow-[0_14px_28px_rgba(225,39,23,0.3)] active:scale-[0.98]"
      style="background:linear-gradient(135deg,#FF5546,#F23A2B,#D32316)">
      <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/20">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" x2="21" y1="6" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-white text-[10px] font-bold text-[#E12717] tabular-nums">${cnt}</span>
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-[13px] font-semibold">ดูรายการสั่งอาหาร</p>
        <p class="mt-0.5 text-[11px] font-medium text-white/70 tabular-nums">${cnt} รายการ · ${fmtMoney(total)}</p>
      </div>
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 text-white/60" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </a>`);
  }
}

// ─── Events ──────────────────────────────────────
$(document).on('click', '.add-btn', function(){
  const id = $(this).data('id');
  const item = menuItems.find(m=>m.id===id);
  if (item) addItem(item);
});
$(document).on('click', '[data-cat]', function(){
  activeCategory = $(this).data('cat');
  renderCatNav();
  renderMenu();
});
$('#search').on('input', function(){
  searchQuery = $(this).val();
  renderMenu();
});

// ─── Load data ───────────────────────────────────
cart = readCart();
Promise.all([
  $.getJSON('api/menu.php'),
  $.getJSON('api/settings.php'),
]).then(function([items, settings]){
  menuItems = items;
  if (settings.restaurantName) $('#restaurant-name').text(settings.restaurantName);
  if (settings.cuisine) {
    const close = settings.closeTime ? ' · เปิดถึง '+settings.closeTime+' น.' : '';
    $('#restaurant-sub').text(settings.cuisine + close);
  }
  renderCatNav();
  renderMenu();
  renderCartBar();
}).fail(function(){
  $('#menu-list').html('<div class="py-10 text-center text-red-500">โหลดเมนูไม่สำเร็จ กรุณาลองใหม่</div>');
});
</script>
</body>
</html>
