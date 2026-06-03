<?php
// GET /api/accounting_summary.php
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_out(['error' => 'Method not allowed'], 405);

$startOfDay  = strtotime('today midnight') * 1000;
$startOfWeek = strtotime('monday this week midnight') * 1000;

// Today
$r1 = db()->prepare('SELECT COALESCE(SUM(total),0) as rev, COUNT(*) as cnt FROM bills WHERE closed_at_ms >= ?');
$r1->bind_param('i', $startOfDay);
$r1->execute();
$today = $r1->get_result()->fetch_assoc();

// This week
$r2 = db()->prepare('SELECT COALESCE(SUM(total),0) as rev, COUNT(*) as cnt FROM bills WHERE closed_at_ms >= ?');
$r2->bind_param('i', $startOfWeek);
$r2->execute();
$week = $r2->get_result()->fetch_assoc();

// All time
$all = db()->query('SELECT COALESCE(SUM(total),0) as rev, COUNT(*) as cnt FROM bills')->fetch_assoc();

$allBills = (int)$all['cnt'];
$avgBill  = $allBills > 0 ? round((float)$all['rev'] / $allBills) : 0;

json_out([
    'todayRevenue' => (float)$today['rev'],
    'todayBills'   => (int)$today['cnt'],
    'weekRevenue'  => (float)$week['rev'],
    'weekBills'    => (int)$week['cnt'],
    'allRevenue'   => (float)$all['rev'],
    'allBills'     => $allBills,
    'avgBill'      => $avgBill,
]);
