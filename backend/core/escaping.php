<?php


function sanitize(string $input): string
{
    return strip_tags(trim($input));
}

function escapeHtml(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function escapeAttr(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
