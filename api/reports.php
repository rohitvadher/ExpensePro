<?php

require_once __DIR__ . '/../includes/api.php';

$userId = requireApiAuth();
requireApiMethod(['GET']);

$_SESSION['last_activity'] = time();

$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$section = $_GET['section'] ?? 'all';

$allowedSections = ['all', 'categories', 'trend', 'category_comparison', 'transactions'];
if (!in_array($section, $allowedSections, true)) {
    $section = 'all';
}

$pdo = getConnection();
$where = ['t.user_id = ?'];
$params = [$userId];

if (validateDate($dateFrom)) {
    $where[] = 't.date >= ?';
    $params[] = $dateFrom;
} else {
    $where[] = 't.date >= ?';
    $params[] = date('Y-01-01');
    $dateFrom = date('Y-01-01');
}

if (validateDate($dateTo)) {
    $where[] = 't.date <= ?';
    $params[] = $dateTo;
} else {
    $where[] = 't.date <= ?';
    $params[] = date('Y-m-d');
    $dateTo = date('Y-m-d');
}

$whereClause = implode(' AND ', $where);

if (validateDate($dateFrom) && validateDate($dateTo) && $dateFrom > $dateTo) {
    sendError('Invalid date range.', ['date_from' => 'Start date must be before end date.'], 422);
}

$stmt = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN t.type = 'income' THEN t.amount END), 0) AS total_income, COALESCE(SUM(CASE WHEN t.type = 'expense' THEN t.amount END), 0) AS total_expense FROM transactions t WHERE $whereClause");
$stmt->execute($params);
$summary = $stmt->fetch();

$transactionCount = 0;
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM transactions t WHERE $whereClause");
$stmtCount->execute($params);
$transactionCount = $stmtCount->fetchColumn();

$totals = calcTotals((float) $summary['total_income'], (float) $summary['total_expense']);

$result = [
    'summary' => [
        'total_income' => $totals['income'],
        'total_expense' => $totals['expense'],
        'balance' => $totals['balance'],
        'savings_rate' => calcSavingsRate($totals['balance'], $totals['income']),
        'avg_daily_expense' => calcAvgDaily($totals['expense'], $dateFrom, $dateTo),
        'transaction_count' => (int) $transactionCount,
        'date_from' => $dateFrom, 'date_to' => $dateTo
    ]
];

if ($section === 'all' || $section === 'categories') {
    $catStmt = $pdo->prepare("SELECT c.name, c.color, c.icon, SUM(t.amount) AS total, COUNT(*) AS tx_count FROM transactions t JOIN categories c ON t.category_id = c.id AND c.user_id = t.user_id WHERE $whereClause AND t.type = 'expense' GROUP BY c.id, c.name, c.color, c.icon ORDER BY total DESC");
    $catStmt->execute($params);
    $categories = $catStmt->fetchAll();
    $totalExpense = (float) $summary['total_expense'];
    $result['categories'] = array_map(function ($cat) use ($totalExpense) {
        return [
            'name' => $cat['name'], 'color' => safeCategoryColor($cat['color']), 'icon' => safeCategoryIcon($cat['icon']),
            'total' => moneyRound((float) $cat['total']), 'tx_count' => (int) $cat['tx_count'],
            'percentage' => calcPercentage((float) $cat['total'], $totalExpense)
        ];
    }, $categories);
}

if ($section === 'all' || $section === 'trend') {
    $trendStmt = $pdo->prepare("SELECT DATE_FORMAT(t.date, '%Y-%m') AS month_key, SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END) AS income, SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END) AS expense FROM transactions t WHERE $whereClause GROUP BY month_key ORDER BY month_key ASC");
    $trendStmt->execute($params);
    $trendRows = $trendStmt->fetchAll();
    $labels = []; $incomeData = []; $expenseData = []; $balanceData = [];
    foreach ($trendRows as $row) {
        $labels[] = date('M Y', strtotime($row['month_key'] . '-01'));
        $inc = moneyRound((float) $row['income']); $exp = moneyRound((float) $row['expense']);
        $incomeData[] = $inc; $expenseData[] = $exp;

        $balanceData[] = moneyRound($inc - $exp);
    }
    $result['trend'] = ['labels' => $labels, 'income' => $incomeData, 'expense' => $expenseData, 'balance' => $balanceData];
}

if ($section === 'all' || $section === 'category_comparison') {
    $cmpStmt = $pdo->prepare("SELECT DATE_FORMAT(t.date, '%Y-%m') AS month_key, c.name AS category_name, c.color AS category_color, SUM(t.amount) AS total FROM transactions t JOIN categories c ON t.category_id = c.id AND c.user_id = t.user_id WHERE $whereClause AND t.type = 'expense' GROUP BY month_key, c.id, c.name, c.color ORDER BY month_key ASC, total DESC");
    $cmpStmt->execute($params);
    $cmpRows = $cmpStmt->fetchAll();
    $months = []; $categoryTotals = []; $categoryColors = [];
    foreach ($cmpRows as $r) {
        $m = $r['month_key'];
        if (!in_array($m, $months, true)) $months[] = $m;
        $cName = $r['category_name'];
        if (!isset($categoryTotals[$cName])) {
            $categoryTotals[$cName] = []; $categoryColors[$cName] = safeCategoryColor($r['category_color']);
        }
        $categoryTotals[$cName][$m] = (float) $r['total'];
    }
    $monthLabels = array_map(function ($m) { return date('M Y', strtotime($m . '-01')); }, $months);
    $series = [];
    foreach ($categoryTotals as $catName => $dataByMonth) {
        $data = [];
        foreach ($months as $m) $data[] = $dataByMonth[$m] ?? 0.0;
        $series[] = ['name' => $catName, 'color' => $categoryColors[$catName], 'data' => $data];
    }
    $result['category_comparison'] = ['months' => $monthLabels, 'series' => $series];
}

if ($section === 'transactions') {
    $txStmt = $pdo->prepare("SELECT t.date, t.type, c.name AS category, c.color, t.amount, t.description FROM transactions t JOIN categories c ON t.category_id = c.id AND c.user_id = t.user_id WHERE $whereClause ORDER BY t.date DESC, t.id DESC LIMIT 200");
    $txStmt->execute($params);
    $result['transactions'] = $txStmt->fetchAll();
}

sendJson($result, 'Report data loaded.');
