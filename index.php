<?php
// Landing Page — แซ่บกลางซอย
?><!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title>แซ่บกลางซอย</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>body{font-family:'Sarabun',sans-serif;padding-bottom:env(safe-area-inset-bottom);}.btn-red{background:linear-gradient(135deg,#FF5546,#F23A2B,#C41E0E);}</style>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet"/>
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-[#FFF9F5] px-5 py-16">

  <!-- Brand -->
  <div class="mb-12 text-center">
    <div class="mx-auto mb-5 flex h-[88px] w-[88px] items-center justify-center rounded-[30px] btn-red shadow-[0_20px_48px_rgba(225,39,23,0.38)]">
      <svg xmlns="http://www.w3.org/2000/svg" class="text-white" width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
    </div>
    <h1 class="text-[30px] font-bold tracking-tight text-[#2C1713]">แซ่บกลางซอย</h1>
    <p class="mt-1.5 text-[13px] text-[#A98671]">อีสาน · ซีฟู้ด · หมูกระทะ</p>
  </div>

  <!-- Cards -->
  <div class="w-full max-w-sm space-y-3">

    <!-- Customer card -->
    <div class="rounded-[24px] border border-[#F0E0D4] bg-white p-5 shadow-[0_8px_24px_rgba(44,23,19,0.07)]">
      <div class="mb-4 flex items-center gap-3">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#FFF3EC] text-[#E12717]">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>
        </div>
        <div>
          <p class="text-[15px] font-semibold text-[#2C1713]">ลูกค้า</p>
          <p class="text-[12px] text-[#9D7F6A]">สั่งอาหารผ่านมือถือ</p>
        </div>
      </div>
      <p class="mb-3 rounded-xl bg-[#FFF9F5] px-3.5 py-2.5 text-[12px] leading-5 text-[#9D7F6A]">
        📱 ปกติสแกน QR Code ที่โต๊ะจากแคชเชียร์<br/>
        ทดสอบ: กรอกเลขโต๊ะด้านล่างได้เลย
      </p>
      <div class="flex gap-2">
        <input id="tableInput" type="text" placeholder="เลขโต๊ะ เช่น A1, B3" maxlength="4"
          class="flex-1 rounded-xl border border-[#F0E0D4] bg-[#FFF9F5] px-4 py-2.5 text-[13px] text-[#2C1713] placeholder:text-[#C4A98A] outline-none transition focus:border-[#E12717]"/>
        <button id="goBtn" type="button"
          class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-xl btn-red text-white shadow-[0_8px_16px_rgba(225,39,23,0.28)] transition-transform active:scale-95">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </button>
      </div>
      <div class="mt-3 flex flex-wrap gap-1.5">
        <?php foreach(['A1','A2','A3','A4','B1','B2'] as $t): ?>
        <button type="button" onclick="goToTable('<?= $t ?>')"
          class="rounded-lg border border-[#F0E0D4] bg-[#FFF9F5] px-3 py-1 text-[11px] font-medium text-[#7C5B47] hover:border-[#E12717]/40 hover:bg-[#FFF3EC] hover:text-[#E12717]">
          <?= $t ?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Cashier card -->
    <a href="cashier.php" class="flex items-center gap-4 rounded-[24px] btn-red p-5 text-white shadow-[0_16px_36px_rgba(225,39,23,0.30)] transition-transform active:scale-[0.98] block">
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/20">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
      </div>
      <div class="flex-1"><p class="text-[15px] font-semibold">แคชเชียร์</p><p class="text-[12px] text-white/70">จัดการโต๊ะและบิล</p></div>
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white/60" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </a>

    <!-- Kitchen card -->
    <a href="kitchen.php" class="flex items-center gap-4 rounded-[24px] border border-[#1A1D2E]/15 bg-gradient-to-br from-[#1A1D2E] to-[#2D3250] p-5 text-white shadow-[0_8px_24px_rgba(26,29,46,0.25)] transition-transform active:scale-[0.98] block">
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 13.87A4 4 0 0 1 7.41 6a5.11 5.11 0 0 1 1.05-1.54 5 5 0 0 1 7.08 0A5.11 5.11 0 0 1 16.59 6 4 4 0 0 1 18 13.87V21H6Z"/><line x1="6" x2="18" y1="17" y2="17"/></svg>
      </div>
      <div class="flex-1"><p class="text-[15px] font-semibold">ห้องครัว</p><p class="text-[12px] text-white/60">รับและจัดการออเดอร์</p></div>
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white/40" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </a>

    <!-- Dashboard card -->
    <a href="dashboard.php" class="flex items-center gap-4 rounded-[24px] border border-[#D4E4CC] bg-gradient-to-br from-[#F0F7EC] to-[#E4F0DC] p-5 text-[#2D5A1B] shadow-[0_8px_24px_rgba(45,90,27,0.10)] transition-transform active:scale-[0.98] block">
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#2D5A1B]/10">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#2D5A1B]"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
      </div>
      <div class="flex-1"><p class="text-[15px] font-semibold">เจ้าของร้าน / Dashboard</p><p class="text-[12px] text-[#4A7A35]/80">รายงาน · สถิติ · ตั้งค่า</p></div>
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#4A7A35]/50" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </a>

    <!-- Accounting card -->
    <a href="accounting.php" class="flex items-center gap-4 rounded-[24px] border border-[#D4E4CC] bg-gradient-to-br from-[#F0F7EC] to-[#E4F0DC] p-5 text-[#2D5A1B] shadow-[0_8px_24px_rgba(45,90,27,0.10)] transition-transform active:scale-[0.98] block">
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#2D5A1B]/10">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#2D5A1B]"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/></svg>
      </div>
      <div class="flex-1"><p class="text-[15px] font-semibold">นักบัญชี</p><p class="text-[12px] text-[#4A7A35]/80">รายรับ · ประวัติบิล · รายงาน</p></div>
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[#4A7A35]/50" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </a>

  </div>

  <p class="mt-10 text-[11px] text-[#C4A98A]">แซ่บกลางซอย · ระบบสั่งอาหาร v1.0 PHP</p>

<script>
function goToTable(id) {
  var t = id || $('#tableInput').val().trim().toUpperCase();
  if (t) window.location.href = 'menu.php?table=' + encodeURIComponent(t);
}
$('#goBtn').on('click', function(){ goToTable(''); });
$('#tableInput').on('keydown', function(e){ if(e.key==='Enter') goToTable(''); });
</script>
</body>
</html>
