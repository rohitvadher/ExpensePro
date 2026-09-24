<?php

require_once __DIR__ . '/../includes/api.php';

$userId = requireApiAuth();
requireApiMethod(['GET']);

$_SESSION['last_activity'] = time();

$pdo = getConnection();
$data = [];

$monthStart = date('Y-m-01');
$monthEnd = date('Y-m-t');
$today = date('Y-m-d');

$summaryStmt = $pdo->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income,
        COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense,
        COUNT(*) AS transaction_count
    FROM transactions
    WHERE user_id = ? AND date >= ? AND date <= ?
");
$summaryStmt->execute([$userId, $monthStart, $monthEnd]);
$summary = $summaryStmt->fetch();

$totals = calcTotals((float) $summary['total_income'], (float) $summary['total_expense']);
$data['summary'] = [
    'total_income' => $totals['income'],
    'total_expense' => $totals['expense'],
    'balance' => $totals['balance'],
    'transaction_count' => (int) $summary['transaction_count']
];

$recentStmt = $pdo->prepare("
    SELECT t.id, t.type, t.amount, t.description, t.date,
           c.name AS category_name, c.icon AS category_icon, c.color AS category_color
    FROM transactions t
    JOIN categories c ON t.category_id = c.id AND c.user_id = t.user_id
    WHERE t.user_id = ? AND t.date >= ? AND t.date <= ?
    ORDER BY t.date DESC, t.id DESC
    LIMIT 5
");
$recentStmt->execute([$userId, $monthStart, $monthEnd]);
$data['recent_transactions'] = $recentStmt->fetchAll();

$trendStmt = $pdo->prepare("
    SELECT DATE_FORMAT(date, '%Y-%m') AS month,
           SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS income,
           SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expense
    FROM transactions
    WHERE user_id = ? AND date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(date, '%Y-%m')
    ORDER BY month ASC
");
$trendStmt->execute([$userId]);
$trendRows = $trendStmt->fetchAll();

$trendLabels = [];
$trendBalance = [];
$trendIncome = [];
$trendExpense = [];
foreach ($trendRows as $row) {
    $timestamp = strtotime($row['month'] . '-01');
    $trendLabels[] = date('M Y', $timestamp);
    $income = moneyRound((float) $row['income']);
    $expense = moneyRound((float) $row['expense']);
    $trendIncome[] = $income;
    $trendExpense[] = $expense;
    $trendBalance[] = moneyRound($income - $expense);
}
$data['balance_trend'] = ['labels' => $trendLabels, 'balance' => $trendBalance, 'income' => $trendIncome, 'expense' => $trendExpense];

$breakdownStmt = $pdo->prepare("
    SELECT c.name, c.color, SUM(t.amount) AS total
    FROM transactions t
    JOIN categories c ON t.category_id = c.id AND c.user_id = t.user_id
    WHERE t.user_id = ? AND t.type = 'expense' AND t.date >= ? AND t.date <= ?
    GROUP BY c.id, c.name, c.color
    ORDER BY total DESC
");
$breakdownStmt->execute([$userId, $monthStart, $monthEnd]);
$breakdown = $breakdownStmt->fetchAll();

$totalExpense = $data['summary']['total_expense'];
$data['category_breakdown'] = array_map(function ($item) use ($totalExpense) {
    return [
        'name' => $item['name'],
        'color' => safeCategoryColor($item['color']),
        'total' => moneyRound((float) $item['total']),
        'percentage' => calcPercentage((float) $item['total'], $totalExpense)
    ];
}, $breakdown);

$userStmt = $pdo->prepare("SELECT monthly_budget FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$userRow = $userStmt->fetch();
$monthlyBudget = $userRow && $userRow['monthly_budget'] ? (float) $userRow['monthly_budget'] : null;

$budgetStatus = null;
if ($monthlyBudget !== null && $monthlyBudget > 0) {
    $status = calcBudgetStatus((float) $totalExpense, $monthlyBudget);
    $budgetStatus = [
        'monthly_budget' => $monthlyBudget,
        'spent' => $status['spent'],
        'remaining' => $status['remaining'],
        'percentage' => $status['percentage'],
        'is_over_budget' => $status['is_over']
    ];
}
$data['budget_status'] = $budgetStatus;

sendJson($data, 'Dashboard data retrieved successfully.');
