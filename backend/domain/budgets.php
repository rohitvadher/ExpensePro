<?php


function getBudgetPeriodRange(string $period, ?string $referenceDate = null): array
{
    if (!in_array($period, ['weekly', 'monthly', 'yearly'], true)) {
        $period = 'monthly';
    }
    try {
        $date = new DateTimeImmutable($referenceDate ?: 'today');
    } catch (Throwable $e) {
        $date = new DateTimeImmutable('today');
    }

    if ($period === 'weekly') {
        $daysSinceMonday = (int) $date->format('N') - 1;
        $start = $date->modify('-' . $daysSinceMonday . ' days');
        $end = $start->modify('+6 days');
    } elseif ($period === 'yearly') {
        $start = $date->setDate((int) $date->format('Y'), 1, 1);
        $end = $date->setDate((int) $date->format('Y'), 12, 31);
    } else {
        $start = $date->modify('first day of this month');
        $end = $date->modify('last day of this month');
    }

    return [
        'start_date' => $start->format('Y-m-d'),
        'end_date' => $end->format('Y-m-d')
    ];
}
