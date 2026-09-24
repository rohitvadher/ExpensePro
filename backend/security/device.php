<?php


function getDeviceCategory(string $ua): string
{
    $low = strtolower(trim($ua));
    if ($low === '') {
        return 'Unknown device';
    }
    if (strpos($low, 'iphone') !== false) {
        return 'iPhone';
    }
    if (strpos($low, 'ipad') !== false) {
        return 'iPad';
    }
    if (strpos($low, 'android') !== false) {
        if (strpos($low, 'mobile') !== false) {
            return 'Android phone';
        }
        return 'Android tablet';
    }
    if (strpos($low, 'tablet') !== false || strpos($low, 'playbook') !== false
        || strpos($low, 'silk') !== false || strpos($low, 'kindle') !== false) {
        return 'Tablet';
    }
    if (strpos($low, 'windows phone') !== false) {
        return 'Windows phone';
    }
    if (strpos($low, 'windows') !== false) {
        return 'Windows PC';
    }
    if (strpos($low, 'macintosh') !== false || strpos($low, 'mac os') !== false) {
        if (strpos($low, 'mobile') !== false) {
            return 'iPad';
        }
        return 'Mac';
    }
    if (strpos($low, 'cros') !== false) {
        return 'Chromebook';
    }
    if (strpos($low, 'linux') !== false) {
        return 'Linux PC';
    }
    if (strpos($low, 'mobi') !== false) {
        return 'Mobile device';
    }
    return 'Desktop';
}

function getBrowserName(string $ua): string
{
    $low = strtolower(trim($ua));
    if ($low === '') {
        return 'Unknown browser';
    }
    if (strpos($low, 'edg/') !== false || strpos($low, 'edga/') !== false
        || strpos($low, 'edgios/') !== false || strpos($low, 'edge/') !== false) {
        return 'Edge';
    }
    if (strpos($low, 'opr/') !== false || strpos($low, 'opios/') !== false
        || strpos($low, 'opera') !== false) {
        return 'Opera';
    }
    if (strpos($low, 'vivaldi') !== false) {
        return 'Vivaldi';
    }
    if (strpos($low, 'brave') !== false) {
        return 'Brave';
    }
    if (strpos($low, 'samsungbrowser') !== false) {
        return 'Samsung Internet';
    }
    if (strpos($low, 'ucbrowser') !== false || strpos($low, 'uc browser') !== false) {
        return 'UC Browser';
    }
    if (strpos($low, 'yabrowser') !== false) {
        return 'Yandex Browser';
    }
    if (strpos($low, 'duckduckgo') !== false) {
        return 'DuckDuckGo';
    }
    if (strpos($low, 'fxios') !== false || strpos($low, 'firefox') !== false
        || strpos($low, 'focus') !== false) {
        return 'Firefox';
    }
    if (strpos($low, 'crios') !== false || strpos($low, 'chrome') !== false) {
        return 'Chrome';
    }
    if (strpos($low, 'safari') !== false) {
        return 'Safari';
    }
    return 'Unknown browser';
}
