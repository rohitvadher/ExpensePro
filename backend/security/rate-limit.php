<?php


function checkRateLimit(PDO $pdo, string $key, int $max, int $windowSecs): bool
{
    try {
        
        if (random_int(1, 100) === 1) {
            try {
                $pdo->exec('DELETE FROM rate_limits WHERE attempted_at < (NOW() - INTERVAL 24 HOUR)');
            } catch (Throwable $ignored) {
            }
        }
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM rate_limits WHERE rate_key = ? AND attempted_at >= (NOW() - INTERVAL ? SECOND)"
        );
        $stmt->execute([$key, $windowSecs]);
        return (int) $stmt->fetchColumn() < $max;
    } catch (Throwable $e) {
        epLog('Rate limit check failed: ' . $e->getMessage());
        return false;
    }
}

function recordRateHit(PDO $pdo, string $key, string $ip): void
{
    try {
        $stmt = $pdo->prepare("INSERT INTO rate_limits (rate_key, ip) VALUES (?, ?)");
        $stmt->execute([$key, $ip]);
    } catch (Throwable $e) {
        epLog('Rate limit record failed: ' . $e->getMessage());
    }
}
