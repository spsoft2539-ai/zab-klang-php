<?php
// GET /api/accounting_summary.php?from=MS&to=MS
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_out(['error' => 'Method not allowed'], 405);

$now         = time() * 1000;
$startOfDay  = strtotime('today midnight') * 1000;
$startOfWeek = strtotime('monday this week midnight') * 1000;

// Custom range (optional)
$from = isset($_GET['from']) ? (int)$_GET['from'] : 0;
$to   = isset($_GET['to'])   ? (int)$_GET['to']   : $now;

// ── Today ──────────────────────────────────────────
$r1 = db()->prepare('SELECT COALESCE(SUM(total),0) rev, COUNT(*) cnt FROM bills WHERE closed_at_ms >= ?');
$r1->bind_param('i', $startOfDay); $r1->execute();
$today = $r1->get_result()->fetch_assoc();

// ── This week ──────────────────────────────────────
$r2 = db()->prepare('SELECT COALESCE(SUM(total),0) rev, COUNT(*) cnt FROM bills WHERE closed_at_ms >= ?');
$r2->bind_param('i', $startOfWeek); $r2->execute();
$week = $r2->get_result()->fetch_assoc();

// ── All time ───────────────────────────────────────
$all = db()->query('SELECT COALESCE(SUM(total),0) rev, COUNT(*) cnt FROM bills')->fetch_assoc();
$allBills = (int)$all['cnt'];
$avgBill  = $allBills > 0 ? round((float)$all['rev'] / $allBills) : 0;

// ── Custom range summary ────────────────────────────
$rs = db()->prepare('SELECT COALESCE(SUM(total),0) rev, COUNT(*) cnt,
    COALESCE(SUM(CASE WHEN payment_method="cash" THEN total ELSE 0 END),0) cash_rev,
    COALESCE(SUM(CASE WHEN payment_method="transfer" THEN total ELSE 0 END),0) transfer_rev,
    COUNT(CASE WHEN payment_method="cash" THEN 1 END) cash_cnt,
    COUNT(CASE WHEN payment_method="transfer" THEN 1 END) transfer_cnt
    FROM bills WHERE closed_at_ms BETWEEN ? AND ?');
$rs->bind_param('ii', $from, $to); $rs->execute();
$range = $rs->get_result()->fetch_assoc();

// ── 7-day daily revenue ─────────────────────────────
$daily = [];
for ($i = 6; $i >= 0; $i--) {
    $dayStart = (strtotime("-{$i} days midnight")) * 1000;
    $dayEnd   = $dayStart + 86400000 - 1;
    $label    = date('d/m', $dayStart / 1000);
    $stmt     = db()->prepare('SELECT COALESCE(SUM(total),0) rev, COUNT(*) cnt FROM bills WHERE closed_at_ms BETWEEN ? AND ?');
    $stmt->bind_param('ii', $dayStart, $dayEnd); $stmt->execute();
    $row      = $stmt->get_result()->fetch_assoc();
    $daily[]  = ['label' => $label, 'revenue' => (float)$row['rev'], 'bills' => (int)$row['cnt']];
}

// ── Top 10 selling items (by revenue) ─────────────────
$topStmt = db()->prepare(
    'SELECT name, SUM(quantity) total_qty, SUM(price*quantity) total_rev
     FROM bill_items
     WHERE bill_id IN (SELECT id FROM bills WHERE closed_at_ms BETWEEN ? AND ?)
     GROUP BY name ORDER BY total_rev DESC LIMIT 10'
);
$topStmt->bind_param('ii', $from, $to); $topStmt->execute();
$topItems = $topStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Monthly revenue (last 6 months) ────────────────────
$monthly = [];
for ($i = 5; $i >= 0; $i--) {
    $mStart = strtotime("first day of -{$i} months midnight") * 1000;
    $mEnd   = strtotime("last day of -{$i} months 23:59:59") * 1000;
    $label  = date('M y', $mStart / 1000);
    $ms     = db()->prepare('SELECT COALESCE(SUM(total),0) rev, COUNT(*) cnt FROM bills WHERE closed_at_ms BETWEEN ? AND ?');
    $ms->bind_param('ii', $mStart, $mEnd); $ms->execute();
    $mr     = $ms->get_result()->fetch_assoc();
    $monthly[] = ['label' => $label, 'revenue' => (float)$mr['rev'], 'bills' => (int)$mr['cnt']];
}

// ── Expenses summary ───────────────────────────
$fromDate = $from > 0 ? date('Y-m-d', $from/1000) : '2000-01-01';
$toDate   = date('Y-m-d', $to/1000);

$expStmt = db()->prepare(
    'SELECT category, COALESCE(SUM(amount),0) cat_amt FROM expenses WHERE date BETWEEN ? AND ? GROUP BY category ORDER BY cat_amt DESC'
);
$expStmt->bind_param('ss', $fromDate, $toDate);
$expStmt->execute();
$expRows = $expStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$totalExpense = array_sum(array_column($expRows, 'cat_amt'));

$allExpStmt = db()->query('SELECT COALESCE(SUM(amount),0) total FROM expenses');
$allExpense = (float)$allExpStmt->fetch_assoc()['total'];

json_out([
    'todayRevenue'  => (float)$today['rev'],
    'todayBills'    => (int)$today['cnt'],
    'weekRevenue'   => (float)$week['rev'],
    'weekBills'     => (int)$week['cnt'],
    'allRevenue'    => (float)$all['rev'],
    'allBills'      => $allBills,
    'avgBill'       => $avgBill,
    'range'         => [
        'revenue'      => (float)$range['rev'],
        'bills'        => (int)$range['cnt'],
        'cashRev'      => (float)$range['cash_rev'],
        'transferRev'  => (float)$range['transfer_rev'],
        'cashCnt'      => (int)$range['cash_cnt'],
        'transferCnt'  => (int)$range['transfer_cnt'],
    ],
    'daily'         => $daily,
    'monthly'       => $monthly,
    'topItems'      => array_map(fn($r) => [
        'name'     => $r['name'],
        'qty'      => (int)$r['total_qty'],
        'revenue'  => (float)$r['total_rev'],
    ], $topItems),
    'expense'       => [
        'total'      => (float)$totalExpense,
        'allTime'    => (float)$allExpense,
        'byCategory' => array_map(fn($r) => [
            'category' => $r['category'],
            'amount'   => (float)$r['cat_amt'],
        ], $expRows),
        'profit'     => (float)$range['rev'] - (float)$totalExpense,
        'allProfit'  => (float)$all['rev'] - (float)$allExpense,
    ],
]);
