<?php

require_once __DIR__ . '/../includes/api.php';

$userId = requireApiAuth();
requireApiMethod(['GET']);

$_SESSION['last_activity'] = time();

$type = $_GET['type'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$pdo = getConnection();
$where = ['t.user_id = ?'];
$params = [$userId];

if ($type === 'income' || $type === 'expense') {
    $where[] = 't.type = ?';
    $params[] = $type;
}

if (validateDate($dateFrom)) {
    $where[] = 't.date >= ?';
    $params[] = $dateFrom;
}
if (validateDate($dateTo)) {
    $where[] = 't.date <= ?';
    $params[] = $dateTo;
}

$whereClause = implode(' AND ', $where);

if (validateDate($dateFrom) && validateDate($dateTo) && $dateFrom > $dateTo) {
    sendError('Invalid date range.', ['date_from' => 'Start date must be before end date.'], 422);
}

$stmt = $pdo->prepare("SELECT t.date, t.type, c.name AS category, t.amount, t.description FROM transactions t JOIN categories c ON t.category_id = c.id AND c.user_id = t.user_id WHERE $whereClause ORDER BY t.date DESC, t.id DESC");
$stmt->execute($params);
$transactions = $stmt->fetchAll();

$format = $_GET['format'] ?? 'csv';

if ($format === 'pdf' || $format === 'print') {
    if (!headers_sent()) {
        header_remove('Content-Type');
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="expensepro_report_' . date('Y-m-d') . '.html"');
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>ExpensePro — Transactions Report</title>
        <style>
            body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; margin: 30px; color: #1e293b; }
            .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #6366f1; padding-bottom: 15px; margin-bottom: 20px; }
            .title { font-size: 24px; font-weight: bold; color: #4338ca; }
            .meta { font-size: 13px; color: #64748b; }
            table { width: 100%; border-collapse: collapse; font-size: 13px; }
            th { background-color: #f1f5f9; color: #475569; font-weight: 600; text-align: left; padding: 10px 12px; border-bottom: 1px solid #cbd5e1; }
            td { padding: 9px 12px; border-bottom: 1px solid #e2e8f0; }
            tr:nth-child(even) td { background-color: #f8fafc; }
            .income { color: #059669; font-weight: 600; }
            .expense { color: #dc2626; font-weight: 600; }
            .amount { text-align: right; }
            .print-bar { margin-bottom: 20px; text-align: right; }
            .btn { background: #6366f1; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; }
            @media print { .print-bar { display: none; } }
        </style>
    </head>
    <body>
        <div class="print-bar">
            <button class="btn" type="button" id="ep-print-btn">Print / Save as PDF</button>
        </div>
        <div class="header">
            <div><div class="title">ExpensePro</div><div class="meta">Transaction Report</div></div>
            <div class="meta" style="text-align: right;"><div>Date Generated: <?= date('d M Y, h:i A') ?></div><div>Total Records: <?= count($transactions) ?></div></div>
        </div>
        <table>
            <thead><tr><th>Date</th><th>Type</th><th>Category</th><th>Description</th><th class="amount">Amount</th></tr></thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                <tr><td colspan="5" style="text-align:center; padding: 20px; color: #94a3b8;">No transactions found matching your criteria.</td></tr>
                <?php else: ?>
                <?php foreach ($transactions as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['date']) ?></td>
                    <td style="text-transform: capitalize;"><?= htmlspecialchars($t['type']) ?></td>
                    <td><?= htmlspecialchars($t['category']) ?></td>
                    <td><?= htmlspecialchars($t['description'] ?: '—') ?></td>
                    <td class="amount <?= $t['type'] === 'income' ? 'income' : 'expense' ?>"><?= ($t['type'] === 'income' ? '+' : '-') . number_format((float) $t['amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <script>
        (function () {
            var btn = document.getElementById('ep-print-btn');
            if (btn) {
                btn.addEventListener('click', function () { window.print(); });
            }
            window.addEventListener('load', function(){ setTimeout(function(){ window.print(); }, 500); });
        })();
        </script>
    </body>
    </html>
    <?php
    exit;
}

$filename = 'expensepro_export_' . date('Y-m-d') . '.csv';
if (!headers_sent()) {
    header_remove('Content-Type');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
}
echo "\xEF\xBB\xBF";
$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Type', 'Category', 'Amount', 'Description']);
foreach ($transactions as $row) {
    fputcsv($output, [$row['date'], ucfirst($row['type']), $row['category'], number_format((float) $row['amount'], 2, '.', ''), $row['description']]);
}
fclose($output);
exit;
