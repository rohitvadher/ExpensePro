<?php


function formatDate(string $date): string
{
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }
    return date('j M Y', $timestamp);
}

function formatDateTime(string $datetime): string
{
    $timestamp = strtotime($datetime);
    if ($timestamp === false) {
        return $datetime;
    }
    return date('j M Y \a\t g:i A', $timestamp);
}

function userIcon(int $size = 8): string
{
    $classes = [
        8 => 'w-8 h-8',
        10 => 'w-10 h-10',
        24 => 'w-24 h-24',
    ];
    $sizeClass = $classes[$size] ?? 'w-8 h-8';

    return '<img src="' . escapeAttr(BASE_URL . 'assets/icons/SVG/user.svg') . '" alt="User profile icon" ' .
        'class="' . $sizeClass . ' rounded-full bg-indigo-100 object-cover flex-shrink-0">';
}
