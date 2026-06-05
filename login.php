<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>เข้าสู่ระบบ · แซ่บกลางซอย</title>
<link rel="manifest" href="/manifest.json"/>
<meta name="theme-color" content="#E12717"/>
<link rel="apple-touch-icon" href="/logo.png"/>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<style>
body { font-family: 'Sarabun', sans-serif; }
.btn-red { background: linear-gradient(135deg,#FF5546,#F23A2B,#C41E0E); }
</style>
</head>
<body class="bg-[#FFF9F5] min-h-screen flex items-center justify-center p-4">

<?php
require_once 'auth.php';

// If already logged in, redirect to appropriate page
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'กรุณากรอก username และ password';
    } else {
        $user = verifyLogin($username, $password);
        if ($user) {
            setUserSession($user);
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username หรือ password ไม่ถูกต้อง';
        }
    }
}
?>

<div class="w-full max-w-sm">
  <div class="bg-white rounded-[28px] shadow-[0_20px_50px_rgba(44,23,19,0.12)] overflow-hidden">

    <!-- Header -->
    <div class="bg-gradient-to-br from-[#FFE5DE] to-[#FFF0EE] px-8 pt-8 pb-6 text-center">
      <div class="flex justify-center mb-2">
        <img src="logo.png" alt="แซ่บกลางซอย" class="w-[140px] h-[140px] object-contain drop-shadow-lg"/>
      </div>
      <p class="mt-1 text-[13px] text-[#9D7F6A]">ระบบจัดการร้านอาหาร</p>
    </div>

    <!-- Form -->
    <div class="px-8 py-10">

      <?php if ($error): ?>
      <div class="mb-5 rounded-[16px] bg-red-50 p-3.5 ring-1 ring-red-200">
        <p class="text-[12px] font-medium text-red-700">❌ <?php echo htmlspecialchars($error); ?></p>
      </div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-5">
          <label for="username" class="block text-[12px] font-semibold text-[#5A4338] mb-2">
            Username
          </label>
          <input
            id="username"
            type="text"
            name="username"
            value="<?php echo htmlspecialchars($username); ?>"
            placeholder="กรอก username"
            class="w-full rounded-[16px] border border-[#E8D6C6] bg-white px-4 py-3 text-[14px] text-[#2C1713] outline-none focus:border-[#E12717] transition-colors"
            required
            autofocus
          />
        </div>

        <div class="mb-6">
          <label for="password" class="block text-[12px] font-semibold text-[#5A4338] mb-2">
            Password
          </label>
          <input
            id="password"
            type="password"
            name="password"
            placeholder="กรอก password"
            class="w-full rounded-[16px] border border-[#E8D6C6] bg-white px-4 py-3 text-[14px] text-[#2C1713] outline-none focus:border-[#E12717] transition-colors"
            required
          />
        </div>

        <button
          type="submit"
          class="w-full rounded-[16px] py-3.5 text-[14px] font-bold text-white btn-red shadow-[0_12px_24px_rgba(225,39,23,0.28)] transition-transform active:scale-95"
        >
          เข้าสู่ระบบ
        </button>
      </form>

      <!-- Demo info -->
      <div class="mt-6 rounded-[16px] bg-[#FFF9F5] p-4 border border-[#F0E0D4]">
        <p class="text-[10px] font-semibold text-[#9D7F6A] mb-2">🔐 Demo Credentials</p>
        <div class="space-y-1 text-[11px] text-[#7C5B47]">
          <p><strong>Owner:</strong> username: owner / password: owner</p>
        </div>
      </div>

    </div>

  </div>

  <!-- Footer -->
  <p class="mt-6 text-center text-[11px] text-[#9D7F6A]">
    v1.0 · ระบบจัดการร้านอาหาร
  </p>

</div>

</body>
</html>
