<?php

function moneyRound(float $value): float
{
    return round($value, 2);
}

function calcTotals(float $income, float $expense): array
{
    $income = moneyRound($income);
    $expense = moneyRound($expense);
    return [
        'income' => $income,
        'expense' => $expense,
        'balance' => moneyRound($income - $expense),
    ];
}

function calcPercentage(float $part, float $total): float
{
    if ($total <= 0) {
        return 0.0;
    }
    return round(($part / $total) * 100, 1);
}

function calcSavingsRate(float $balance, float $income): float
{
    if ($income <= 0) {
        return 0.0;
    }
    return round(($balance / $income) * 100, 1);
}

function calcAvgDaily(float $expense, string $dateFrom, string $dateTo): float
{
    $days = 1;
    if (validateDate($dateFrom) && validateDate($dateTo) && $dateTo >= $dateFrom) {
        $days = max(1, (int) ((strtotime($dateTo) - strtotime($dateFrom)) / 86400) + 1);
    }
    return moneyRound($expense / $days);
}

function calcBudgetStatus(float $spent, float $limit): array
{
    $spent = moneyRound(max(0, $spent));
    $limit = moneyRound(max(0, $limit));
    return [
        'spent' => $spent,
        'remaining' => moneyRound(max(0, $limit - $spent)),
        'percentage' => calcPercentage($spent, $limit),
        'is_over' => $spent > $limit,
    ];
}

function indianGroupedInt(string $digits): string
{
    $len = strlen($digits);
    if ($len <= 3) {
        return $digits;
    }
    $tail = substr($digits, -3);
    $head = substr($digits, 0, $len - 3);
    $out = '';
    while (strlen($head) > 2) {
        $out = ',' . substr($head, -2) . $out;
        $head = substr($head, 0, -2);
    }
    return $head . $out . ',' . $tail;
}

function formatINR(float $amount): string
{
    $rounded = number_format($amount, 2, '.', '');
    $negative = str_starts_with($rounded, '-');
    if ($negative) {
        $rounded = substr($rounded, 1);
    }
    [$int, $dec] = explode('.', $rounded) + [1 => '00'];
    $grouped = indianGroupedInt(ltrim($int, '0') === '' ? '0' : ltrim($int, '0'));
    return ($negative ? '-₹' : '₹') . $grouped . '.' . $dec;
}
